<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        $categories = \App\Models\Category::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $attributes = Attribute::with('values')->get();

        return view('pages.products.create', compact('categories', 'brands', 'attributes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name']);

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        }

        if ($data['product_type'] === 'downloadable' && $request->hasFile('download_file')) {
            $data['download_file'] = $request->file('download_file')->store('products/downloads', 'public');
        }

        DB::beginTransaction();
        try {
            $product = Product::create($data);

            // gallery
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $img) {
                    $path = $img->store('products/gallery', 'public');
                    $product->gallery()->create(['image_path' => $path]);
                }
            }

            // ✅ save attribute selections + variants for variable products
            if ($data['product_type'] === 'variable') {
                $this->syncProductAttributes($request, $product);
                $this->syncVariants($request, $product);
            } else {
                // if not variable, make sure pivot cleared (safety)
                DB::table('product_attribute_values')->where('product_id', $product->id)->delete();
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product created successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();

            // cleanup uploaded featured/download if created but failed later
            if (!empty($data['featured_image'])) Storage::disk('public')->delete($data['featured_image']);
            if (!empty($data['download_file'])) Storage::disk('public')->delete($data['download_file']);

            throw $e;
        }
    }

    public function show(Product $product)
    {
        $product->load([
            'gallery',
            'variants',
            'attributeValues.attribute',   // needs relation in AttributeValue model (below)
            'category',
            'brand',
        ]);

        return view('pages.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load(['gallery', 'variants', 'attributeValues']);
        $categories = \App\Models\Category::orderBy('name')->get();
        $brands = \App\Models\Brand::orderBy('name')->get();
        $attributes = Attribute::with('values')->get();

        return view('pages.products.edit', compact('product', 'categories', 'brands', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request, $product->id);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?: $data['name'], $product->id);

        $oldFeatured = $product->featured_image;
        $oldDownload = $product->download_file;

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = $request->file('featured_image')->store('products/featured', 'public');
        }

        if ($data['product_type'] === 'downloadable' && $request->hasFile('download_file')) {
            $data['download_file'] = $request->file('download_file')->store('products/downloads', 'public');
        }

        DB::beginTransaction();
        try {
            $product->update($data);

            // delete old featured only AFTER update success
            if ($request->hasFile('featured_image') && $oldFeatured) {
                Storage::disk('public')->delete($oldFeatured);
            }

            // delete old download only AFTER update success
            if ($data['product_type'] === 'downloadable' && $request->hasFile('download_file') && $oldDownload) {
                Storage::disk('public')->delete($oldDownload);
            }

            // gallery add new
            if ($request->hasFile('gallery_images')) {
                foreach ($request->file('gallery_images') as $img) {
                    $path = $img->store('products/gallery', 'public');
                    $product->gallery()->create(['image_path' => $path]);
                }
            }

            // ✅ resync pivot + variants
            $product->variants()->delete();

            if ($data['product_type'] === 'variable') {
                $this->syncProductAttributes($request, $product);
                $this->syncVariants($request, $product);
            } else {
                DB::table('product_attribute_values')->where('product_id', $product->id)->delete();
            }

            DB::commit();
            return redirect()->route('products.edit', $product)->with('success', 'Product updated successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();

            // if new files uploaded in this request, delete them because update failed
            if ($request->hasFile('featured_image') && !empty($data['featured_image'])) {
                Storage::disk('public')->delete($data['featured_image']);
            }
            if ($request->hasFile('download_file') && !empty($data['download_file'])) {
                Storage::disk('public')->delete($data['download_file']);
            }

            throw $e;
        }
    }

    public function destroy(Product $product)
    {
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

            // Only for simple/downloadable
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],

            'sku' => ['nullable', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($ignoreId)],
            'barcode' => ['nullable', 'string', 'max:120'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],

            'category_id' => ['nullable', 'integer'],
            'brand_id' => ['nullable', 'integer'],

            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'download_file' => ['nullable', 'file', 'mimes:pdf,zip', 'max:10240'],

            // ✅ attribute selections (required if variable)
            'variable_attributes' => ['nullable', 'array'],
            'variable_attributes.*.attribute_id' => [
                Rule::requiredIf(fn() => $request->input('product_type') === 'variable'),
                'integer',
                'exists:attributes,id'
            ],
            'variable_attributes.*.value_ids' => [
                Rule::requiredIf(fn() => $request->input('product_type') === 'variable'),
                'array'
            ],
            'variable_attributes.*.value_ids.*' => ['integer', 'exists:attribute_values,id'],

            // ✅ variants table (required if variable)
            'variants' => ['nullable', 'array'],
            'variants.*.attributes_json' => [
                Rule::requiredIf(fn() => $request->input('product_type') === 'variable'),
                'string'
            ],
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

    /**
     * ✅ Store product-level selected attributes/values into pivot table:
     * product_attribute_values(product_id, attribute_id, attribute_value_id)
     */
    private function syncProductAttributes(Request $request, Product $product): void
    {
        $rows = $request->input('variable_attributes', []);

        DB::table('product_attribute_values')->where('product_id', $product->id)->delete();

        $insert = [];

        foreach ($rows as $r) {
            $attributeId = (int)($r['attribute_id'] ?? 0);
            $valueIds = $r['value_ids'] ?? [];

            if (!$attributeId || !is_array($valueIds) || empty($valueIds)) continue;

            foreach ($valueIds as $valueId) {
                $insert[] = [
                    'product_id' => $product->id,
                    'attribute_id' => $attributeId,
                    'attribute_value_id' => (int)$valueId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($insert)) {
            DB::table('product_attribute_values')->insert($insert);
        }
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

            if ($request->hasFile("variants.$idx.image")) {
                $variantData['image_path'] = $request->file("variants.$idx.image")
                    ->store('products/variants', 'public');
            }

            $product->variants()->create($variantData);
        }
    }

    // Optional legacy endpoint (you can delete if unused)
    public function attributeOptions(Request $request)
    {
        $attributeId = (int) $request->get('attribute_id');

        $options = AttributeValue::query()
            ->where('attribute_id', $attributeId)
            ->orderBy('value')
            ->get(['id', 'value']);

        return response()->json($options);
    }

    // ✅ AJAX endpoint used by your JS: /attribute-values?attribute_id=ID
    public function attributeValues(Request $request)
    {
        $attributeId = (int) $request->query('attribute_id');
        if (!$attributeId) return response()->json([]);

        $values = AttributeValue::where('attribute_id', $attributeId)
            ->orderBy('id')
            ->get(['id', 'value']);

        return response()->json(
            $values->map(fn($v) => ['id' => $v->id, 'label' => $v->value])->values()
        );
    }
}
