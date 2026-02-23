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

    public function update(Request $request, \App\Models\Product $product)
{
    $data = $request->validate([
        'name' => ['required','string','max:255'],
        'sku' => ['nullable','string','max:255'],
        'slug' => ['nullable','string','max:255'],
        'product_type' => ['required','in:simple,variable'],
        'regular_price' => ['nullable','numeric','min:0'],
        'sale_price' => ['nullable','numeric','min:0'],
        'stock' => ['nullable','integer','min:0'],
        'is_active' => ['nullable','in:0,1'],
        'category_id' => ['nullable','integer'],
        'brand_id' => ['nullable','integer'],
        'description' => ['nullable','string'],
        'short_description' => ['nullable','string'],
        'meta_title' => ['nullable','string'],
        'meta_description' => ['nullable','string'],

        'attribute_value_ids' => ['nullable','array'],
        'attribute_value_ids.*' => ['integer'],

        // ✅ variants
        'variants_delete' => ['nullable','array'],
        'variants_delete.*' => ['integer'],

        'variants_existing' => ['nullable','array'],
        'variants_existing.*.sku' => ['nullable','string','max:255'],
        'variants_existing.*.regular_price' => ['nullable','numeric','min:0'],
        'variants_existing.*.sale_price' => ['nullable','numeric','min:0'],
        'variants_existing.*.stock' => ['nullable','integer','min:0'],
        'variants_existing.*.image_path' => ['nullable','string','max:500'],
        'variants_existing.*.attributes' => ['nullable','string'],

        'variants_new' => ['nullable','array'],
        'variants_new.*.sku' => ['nullable','string','max:255'],
        'variants_new.*.regular_price' => ['nullable','numeric','min:0'],
        'variants_new.*.sale_price' => ['nullable','numeric','min:0'],
        'variants_new.*.stock' => ['nullable','integer','min:0'],
        'variants_new.*.image_path' => ['nullable','string','max:500'],
        'variants_new.*.attributes' => ['nullable','string'],
    ]);

    // ✅ IMPORTANT: prevent “variants removed” when user accidentally changes type to simple
    // Option A (recommended): block switching to simple if variants exist (unless they delete all)
    $hasVariants = $product->variants()->exists();
    if ($hasVariants && $data['product_type'] === 'simple') {
        return back()
            ->withErrors(['product_type' => 'This product has variants. Delete variants first, then you can switch to Simple.'])
            ->withInput();
    }

    DB::transaction(function () use ($request, $product, $data) {

        // 1) Update product main fields
        $product->update([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'slug' => $data['slug'] ?? null,
            'product_type' => $data['product_type'],
            'regular_price' => $data['regular_price'] ?? 0,
            'sale_price' => $data['sale_price'] ?? null,
            'stock' => $data['stock'] ?? 0,
            'is_active' => (int)($data['is_active'] ?? 1),
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'description' => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
        ]);

        // 2) Sync attribute values (if you use pivot)
        if ($request->has('attribute_value_ids')) {
            // change this to your real relation name:
            // $product->attributeValues()->sync($data['attribute_value_ids'] ?? []);
            if (method_exists($product, 'attributeValues')) {
                $product->attributeValues()->sync($data['attribute_value_ids'] ?? []);
            }
        }

        // 3) Delete only checked variants (DO NOT remove others)
        $deleteIds = collect($data['variants_delete'] ?? [])
            ->map(fn($v) => (int)$v)
            ->unique()
            ->values();

        if ($deleteIds->count()) {
            $product->variants()->whereIn('id', $deleteIds)->delete();
        }

        // helper: safe JSON decode
        $parseAttrs = function ($raw) {
            if (is_array($raw)) return $raw;
            $raw = trim((string)$raw);
            if ($raw === '') return [];
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        };

        // 4) Update existing variants (ONLY ones sent)
        $existing = $data['variants_existing'] ?? [];
        foreach ($existing as $variantId => $row) {
            $variantId = (int)$variantId;

            // skip if user marked it for deletion
            if ($deleteIds->contains($variantId)) continue;

            $variant = $product->variants()->where('id', $variantId)->first();
            if (!$variant) continue;

            $attrs = $parseAttrs($row['attributes'] ?? '');

            $variant->update([
                'sku' => $row['sku'] ?? null,
                'regular_price' => $row['regular_price'] ?? 0,
                'sale_price' => $row['sale_price'] ?? null,
                'stock' => $row['stock'] ?? 0,
                'image_path' => $row['image_path'] ?? null,
                'attributes' => $attrs,
            ]);
        }

        // 5) Create new variants
        $news = $data['variants_new'] ?? [];
        foreach ($news as $row) {
            // ignore empty rows
            $hasAny =
                !empty($row['sku']) ||
                !empty($row['regular_price']) ||
                !empty($row['sale_price']) ||
                !empty($row['stock']) ||
                !empty($row['image_path']) ||
                !empty($row['attributes']);

            if (!$hasAny) continue;

            $attrs = $parseAttrs($row['attributes'] ?? '');

            $product->variants()->create([
                'product_id' => $product->id,
                'sku' => $row['sku'] ?? null,
                'regular_price' => $row['regular_price'] ?? 0,
                'sale_price' => $row['sale_price'] ?? null,
                'stock' => $row['stock'] ?? 0,
                'image_path' => $row['image_path'] ?? null,
                'attributes' => $attrs,
            ]);
        }
    });

    return back()->with('success', 'Product updated successfully!');
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

    // ✅ GET: /api/products
    public function apiProducts(Request $request)
    {
        $q = $request->query('q');

        $products = Product::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%$q%")->orWhere('sku', 'like', "%$q%"))
            ->with(['category', 'brand'])
            ->latest()
            ->get();

        $products->transform(function ($p) {
            $p->featured_image_url = $p->featured_image ? Storage::disk('public')->url($p->featured_image) : null;
            return $p;
        });

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }


    // ✅ GET: /api/products/{id}
    public function apiProductShow($id)
    {
        $product = Product::with([
            'gallery',
            'variants',
            'attributeValues.attribute',
            'category',
            'brand',
        ])->find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $product->featured_image_url = $product->featured_image ? Storage::disk('public')->url($product->featured_image) : null;
        $product->download_file_url  = $product->download_file ? Storage::disk('public')->url($product->download_file) : null;

        // gallery urls
        $product->gallery->transform(function ($g) {
            $g->image_url = $g->image_path ? Storage::disk('public')->url($g->image_path) : null;
            return $g;
        });

        // variant image urls
        $product->variants->transform(function ($v) {
            $v->image_url = $v->image_path ? Storage::disk('public')->url($v->image_path) : null;
            return $v;
        });

        // ✅ if variable product: group attributes nicely (Attribute -> Values)
        if ($product->product_type === 'variable') {
            $product->attributes = $product->attributeValues
                ->groupBy(fn($val) => $val->pivot->attribute_id)
                ->map(function ($values) {
                    $first = $values->first();
                    return [
                        'attribute_id' => $first->pivot->attribute_id,
                        'attribute_name' => optional($first->attribute)->name,
                        'attribute_slug' => optional($first->attribute)->slug,
                        'values' => $values->map(fn($v) => [
                            'id' => $v->id,
                            'value' => $v->value,
                        ])->values(),
                    ];
                })
                ->values();
        } else {
            $product->attributes = [];
        }

        // remove raw pivot list if you want cleaner response
        unset($product->attributeValues);

        return response()->json([
            'success' => true,
            'product' => $product,
        ]);
    }


    // ✅ GET: /api/products/{id}/related
    public function apiRelatedProducts($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $related = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['category', 'brand'])
            ->latest()
            ->take(12)
            ->get();

        $related->transform(function ($p) {
            $p->featured_image_url = $p->featured_image ? Storage::disk('public')->url($p->featured_image) : null;
            return $p;
        });

        return response()->json([
            'success' => true,
            'product_id' => $product->id,
            'related' => $related,
        ]);
    }
    // Product data by brand slug
    public function apiProductsByBrandSlug($slug)
    {
        $brand = \App\Models\Brand::where('slug', $slug)->first();

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $products = \App\Models\Product::where('brand_id', $brand->id)
            ->where('is_active', 1)
            ->with([
                'category:id,name,slug',
                'brand:id,name,slug',
                // ✅ include variants for variable product cards
                'variants' => function ($q) {
                    $q->select([
                        'id',
                        'product_id',
                        'regular_price',
                        'sale_price',
                        'stock',
                        'image_path',
                        'attributes', // if this is a json column in variants table
                    ]);
                }
            ])
            ->select([
                'id',
                'brand_id',
                'category_id',
                'name',
                'slug',
                'sku',
                'product_type',
                'regular_price',
                'sale_price',
                'stock',
                'featured_image',
                'short_description',
                'description',
                'is_active',
                'created_at',
            ])
            ->latest()
            ->get();

        $products->transform(function ($product) {
            // product image url
            $product->featured_image_url = $product->featured_image
                ? \Storage::disk('public')->url($product->featured_image)
                : null;

            // ✅ variant image url
            if ($product->relationLoaded('variants') && $product->variants) {
                $product->variants->transform(function ($v) {
                    $v->image_url = $v->image_path
                        ? \Storage::disk('public')->url($v->image_path)
                        : null;
                    return $v;
                });
            }

            // ✅ optional: compute range for variable products (useful for listing cards)
            if ($product->product_type === 'variable' && $product->variants && $product->variants->count()) {
                $prices = $product->variants->map(function ($v) {
                    return $v->sale_price ?? $v->regular_price;
                })->filter(function ($p) {
                    return $p !== null;
                })->map(function ($p) {
                    return (float) $p;
                });

                $product->min_price = $prices->count() ? $prices->min() : null;
                $product->max_price = $prices->count() ? $prices->max() : null;
            } else {
                $product->min_price = null;
                $product->max_price = null;
            }

            return $product;
        });

        return response()->json([
            'success' => true,
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
            ],
            'count' => $products->count(),
            'products' => $products
        ]);
    }
}
