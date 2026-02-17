<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductGallery;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $products = Product::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.products.index', compact('products', 'q'));
    }

    public function create()
    {
        // load your categories/brands if you have models
        $categories = \App\Models\Category::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();

        return view('pages.products.create', compact('categories', 'brands'));
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);

        // featured image
        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        }

        // downloadable
        if ($data['product_type'] === 'downloadable' && $request->hasFile('download_file')) {
            $data['download_file'] = $request->file('download_file')->store('products/downloads', 'public');
        }

        $product = Product::create($data);

        // gallery
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $path = $img->store('products/gallery', 'public');
                $product->gallery()->create(['image_path' => $path]);
            }
        }

        // variants (variable product)
        if ($data['product_type'] === 'variable') {
            $this->syncVariants($request, $product);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function show(Product $product)
    {
        $product->load(['gallery', 'variants']);
        return view('pages.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['gallery', 'variants']);
        $categories = \App\Models\Category::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();

        return view('pages.products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product->id);

        // slug (unique)
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $product->id);

        // featured image replace
        if ($request->hasFile('featured_image')) {
            if ($product->featured_image) Storage::disk('public')->delete($product->featured_image);
            $data['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        }

        // downloadable replace
        if ($data['product_type'] === 'downloadable' && $request->hasFile('download_file')) {
            if ($product->download_file) Storage::disk('public')->delete($product->download_file);
            $data['download_file'] = $request->file('download_file')->store('products/downloads', 'public');
        }

        // if product type changed away from downloadable, remove file
        if ($data['product_type'] !== 'downloadable') {
            // keep file if you want; otherwise delete:
            // if ($product->download_file) Storage::disk('public')->delete($product->download_file);
            // $data['download_file'] = null;
        }

        $product->update($data);

        // gallery add new
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $path = $img->store('products/gallery', 'public');
                $product->gallery()->create(['image_path' => $path]);
            }
        }

        // variants resync
        $product->variants()->delete();
        if ($data['product_type'] === 'variable') {
            $this->syncVariants($request, $product);
        }

        return redirect()->route('products.edit', $product)->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        // delete files
        if ($product->featured_image) Storage::disk('public')->delete($product->featured_image);
        if ($product->download_file) Storage::disk('public')->delete($product->download_file);

        foreach ($product->gallery as $g) Storage::disk('public')->delete($g->image_path);
        foreach ($product->variants as $v) if ($v->image_path) Storage::disk('public')->delete($v->image_path);

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('products', 'slug')->ignore($ignoreId)],
            'product_type' => ['required', Rule::in(['simple', 'variable', 'downloadable'])],

            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1500'],

            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],

            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($ignoreId)],
            'barcode' => ['nullable', 'string', 'max:120'],

            'stock' => ['nullable', 'integer', 'min:0'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],

            'category_id' => ['nullable', 'integer'],
            'brand_id' => ['nullable', 'integer'],

            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'download_file' => ['nullable', 'file', 'mimes:pdf,zip', 'max:10240'],

            // variants arrays (only used for variable)
            'variants' => ['nullable', 'array'],
            'variants.*.attributes_json' => ['nullable', 'string'],
            'variants.*.regular_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.sale_price' => ['nullable', 'numeric', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.sku' => ['nullable', 'string', 'max:120'],
            'variants.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($base);
        $slug = $baseSlug;
        $i = 1;

        while (
            Product::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $i++;
        }

        return $slug;
    }

    private function syncVariants(Request $request, Product $product): void
    {
        $variants = $request->input('variants', []);
        foreach ($variants as $idx => $v) {
            if (empty($v['attributes_json'])) continue;

            $attrs = json_decode($v['attributes_json'], true);
            if (!is_array($attrs) || empty($attrs)) continue;

            $variantData = [
                'attributes' => $attrs,
                'regular_price' => $v['regular_price'] ?? null,
                'sale_price' => $v['sale_price'] ?? null,
                'stock' => $v['stock'] ?? null,
                'sku' => $v['sku'] ?? null,
                'image_path' => null,
            ];

            // variant image file input name: variants[0][image]
            if ($request->hasFile("variants.$idx.image")) {
                $variantData['image_path'] = $request->file("variants.$idx.image")->store('products/variants', 'public');
            }

            $product->variants()->create($variantData);
        }
    }
}
