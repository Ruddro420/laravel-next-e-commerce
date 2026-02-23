@extends('layouts.app')

@section('title','Edit Product')
@section('subtitle','CRM')
@section('pageTitle','Edit Product')
@section('pageDesc', $product->name)

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  @if(session('success'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err)
          <li>{{ $err }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @php
    $selectedAttrValueIds = collect(old('attribute_value_ids', $product->attributeValues?->pluck('id')?->toArray() ?? []))
      ->map(fn($v)=> (int)$v)
      ->values()
      ->all();

    $productType = old('product_type', $product->product_type ?? 'simple');
  @endphp

  <form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Basic Info --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Product Name <span class="text-red-500">*</span></label>
        <input name="name" value="{{ old('name',$product->name) }}" required
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">SKU</label>
        <input name="sku" value="{{ old('sku',$product->sku) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    {{-- Product Type --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Product Type <span class="text-red-500">*</span></label>
        <select id="productType" name="product_type" required
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="simple" {{ $productType === 'simple' ? 'selected' : '' }}>Simple Product</option>
          <option value="variable" {{ $productType === 'variable' ? 'selected' : '' }}>Variable Product (with variants)</option>
        </select>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          Simple: single product with no variants | Variable: product with multiple variants (size, color, etc.)
        </div>
      </div>
      <div>
        <label class="text-sm font-semibold">SLUG</label>
        <input name="slug" value="{{ old('slug',$product->slug) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    {{-- Price & Stock Info --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="text-sm font-semibold">Regular Price</label>
        <input name="regular_price" type="number" step="0.01" min="0"
          value="{{ old('regular_price',$product->regular_price) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Sale Price</label>
        <input name="sale_price" type="number" step="0.01" min="0"
          value="{{ old('sale_price',$product->sale_price) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Stock</label>
        <input name="stock" type="number" step="1" min="0"
          value="{{ old('stock',$product->stock) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Use 0 if out of stock.</div>
      </div>

      <div>
        <label class="text-sm font-semibold">Status</label>
        @php $active = old('is_active', (int)($product->is_active ?? 1)); @endphp
        <select name="is_active"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="1" {{ (int)$active===1?'selected':'' }}>Active</option>
          <option value="0" {{ (int)$active===0?'selected':'' }}>Inactive</option>
        </select>
      </div>
    </div>

    {{-- Category / Brand --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Category</label>
        <select name="category_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select category</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ (string)old('category_id',$product->category_id)===(string)$cat->id ? 'selected' : '' }}>
              {{ $cat->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Brand</label>
        <select name="brand_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select brand</option>
          @foreach($brands as $b)
            <option value="{{ $b->id }}" {{ (string)old('brand_id',$product->brand_id)===(string)$b->id ? 'selected' : '' }}>
              {{ $b->name }}
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Description --}}
    <div>
      <label class="text-sm font-semibold">Description</label>
      <textarea name="description" rows="4"
        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('description',$product->description) }}</textarea>
    </div>

    {{-- Short Description --}}
    <div>
      <label class="text-sm font-semibold">Short Description</label>
      <textarea name="short_description" rows="2"
        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('short_description',$product->short_description) }}</textarea>
    </div>

    {{-- Attributes --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-1">Attributes</div>
      <div class="text-xs text-slate-500 dark:text-slate-400 mb-3">Select all values this product has (like POS attribute pick).</div>

      <div class="space-y-4">
        @forelse($attributes as $attr)
          <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
            <div class="font-semibold text-sm">{{ $attr->name }}</div>

            <div class="mt-3 flex flex-wrap gap-2">
              @foreach($attr->values as $val)
                @php $checked = in_array((int)$val->id, $selectedAttrValueIds, true); @endphp
                <label class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:border-slate-800">
                  <input type="checkbox" name="attribute_value_ids[]"
                    value="{{ $val->id }}" {{ $checked ? 'checked' : '' }}
                    class="rounded border-slate-300 dark:border-slate-700">
                  <span>{{ $val->value }}</span>
                </label>
              @endforeach
            </div>
          </div>
        @empty
          <div class="text-sm text-slate-500 dark:text-slate-400">No attributes found.</div>
        @endforelse
      </div>
    </div>

    {{-- Featured Image --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Featured Image</div>

      @if($product->featured_image)
        <div class="mb-3">
          <div class="rounded-xl border border-slate-200 overflow-hidden dark:border-slate-800 w-48">
            <img src="{{ asset('storage/' . $product->featured_image) }}" class="h-32 w-full object-cover" alt="{{ $product->name }}">
          </div>
        </div>
      @endif

      <label class="text-sm font-semibold">Upload New Featured Image</label>
      <input type="file" name="featured_image" accept="image/*"
        class="mt-2 block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/20 dark:file:text-indigo-300" />
      <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Leave empty to keep current image.</div>
    </div>

    {{-- Gallery (optional) --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Gallery Images</div>

      @if($product->gallery && $product->gallery->count())
        <div class="mb-3 grid grid-cols-2 md:grid-cols-6 gap-3">
          @foreach($product->gallery as $img)
            <div class="relative rounded-xl border border-slate-200 overflow-hidden dark:border-slate-800">
              <img src="{{ asset('storage/' . ($img->path ?? $img->image ?? '')) }}" class="h-24 w-full object-cover" alt="">
            </div>
          @endforeach
        </div>
      @else
        <div class="text-sm text-slate-500 dark:text-slate-400 mb-3">No gallery images.</div>
      @endif

      <label class="text-sm font-semibold">Upload New Images (optional)</label>
      <input type="file" name="images[]" multiple accept="image/*"
        class="mt-2 block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/20 dark:file:text-indigo-300" />
      <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">You can select multiple images.</div>
    </div>

    {{-- ✅ Variants (Editable + Add/Remove) --}}
    <div id="variantsSection" class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between gap-3">
        <div>
          <div class="font-semibold">Variants (Editable)</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">
            Attributes must be valid JSON like: {"Color":"Red","Size":"XL"}
          </div>
        </div>

        <button type="button" id="btnAddVariant"
          class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
          + Add Variant
        </button>
      </div>

      <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase text-slate-500 dark:text-slate-400">
              <th class="py-2 pr-3">Delete</th>
              <th class="py-2 pr-3">SKU</th>
              <th class="py-2 pr-3">Regular</th>
              <th class="py-2 pr-3">Sale</th>
              <th class="py-2 pr-3">Stock</th>
              <th class="py-2 pr-3">Image Path</th>
              <th class="py-2 pr-3">Attributes (JSON)</th>
            </tr>
          </thead>

          <tbody id="variantsTbody">
            {{-- Existing variants --}}
            @foreach($product->variants ?? [] as $v)
              @php
                $attrs = $v->attributes;
                if (!is_array($attrs)) {
                  $attrs = json_decode((string)$attrs, true) ?: [];
                }
                $attrsJson = json_encode($attrs, JSON_UNESCAPED_UNICODE);
              @endphp
              <tr class="border-t border-slate-100 dark:border-slate-800" data-variant-row>
                <td class="py-2 pr-3">
                  <label class="inline-flex items-center gap-2 text-xs">
                    <input type="checkbox" name="variants_delete[]" value="{{ $v->id }}"
                      class="rounded border-slate-300 dark:border-slate-700">
                    <span class="text-rose-600 font-semibold">Delete</span>
                  </label>
                </td>

                <td class="py-2 pr-3">
                  <input name="variants_existing[{{ $v->id }}][sku]" value="{{ old("variants_existing.$v->id.sku", $v->sku) }}"
                    class="w-44 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
                </td>

                <td class="py-2 pr-3">
                  <input name="variants_existing[{{ $v->id }}][regular_price]" type="number" step="0.01" min="0"
                    value="{{ old("variants_existing.$v->id.regular_price", $v->regular_price) }}"
                    class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
                </td>

                <td class="py-2 pr-3">
                  <input name="variants_existing[{{ $v->id }}][sale_price]" type="number" step="0.01" min="0"
                    value="{{ old("variants_existing.$v->id.sale_price", $v->sale_price) }}"
                    class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
                </td>

                <td class="py-2 pr-3">
                  <input name="variants_existing[{{ $v->id }}][stock]" type="number" step="1" min="0"
                    value="{{ old("variants_existing.$v->id.stock", $v->stock) }}"
                    class="w-24 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
                </td>

                <td class="py-2 pr-3">
                  <input name="variants_existing[{{ $v->id }}][image_path]" value="{{ old("variants_existing.$v->id.image_path", $v->image_path) }}"
                    class="w-64 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
                </td>

                <td class="py-2 pr-3">
                  <textarea name="variants_existing[{{ $v->id }}][attributes]" rows="2"
                    class="w-[420px] rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
                    placeholder='{"Color":"Red","Size":"XL"}'>{{ old("variants_existing.$v->id.attributes", $attrsJson) }}</textarea>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">
          Tip: Keep sale_price empty/null if no sale.
        </div>
      </div>
    </div>

    {{-- SEO --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">SEO Information</div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Meta Title</label>
          <input name="meta_title" value="{{ old('meta_title', $product->meta_title) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Meta Description</label>
          <textarea name="meta_description" rows="2"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('meta_description', $product->meta_description) }}</textarea>
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ route('products.index') }}"
        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        Back
      </a>

      <button type="submit"
        class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Update Product
      </button>
    </div>

  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const productType = document.getElementById('productType');
  const variantsSection = document.getElementById('variantsSection');
  const variantsTbody = document.getElementById('variantsTbody');
  const btnAddVariant = document.getElementById('btnAddVariant');

  function toggleVariants() {
    if (!productType || !variantsSection) return;
    variantsSection.style.display = (productType.value === 'variable') ? 'block' : 'none';
  }

  productType?.addEventListener('change', toggleVariants);
  toggleVariants();

  let newIdx = 0;

  function newVariantRow(i){
    return `
      <tr class="border-t border-slate-100 dark:border-slate-800" data-variant-row>
        <td class="py-2 pr-3">
          <button type="button" data-remove-new
            class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
            Remove
          </button>
        </td>

        <td class="py-2 pr-3">
          <input name="variants_new[${i}][sku]"
            class="w-44 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </td>

        <td class="py-2 pr-3">
          <input name="variants_new[${i}][regular_price]" type="number" step="0.01" min="0"
            class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </td>

        <td class="py-2 pr-3">
          <input name="variants_new[${i}][sale_price]" type="number" step="0.01" min="0"
            class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </td>

        <td class="py-2 pr-3">
          <input name="variants_new[${i}][stock]" type="number" step="1" min="0" value="0"
            class="w-24 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </td>

        <td class="py-2 pr-3">
          <input name="variants_new[${i}][image_path]"
            class="w-64 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="storage/path/to/image.jpg" />
        </td>

        <td class="py-2 pr-3">
          <textarea name="variants_new[${i}][attributes]" rows="2"
            class="w-[420px] rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder='{"Color":"Red","Size":"XL"}'></textarea>
        </td>
      </tr>
    `;
  }

  btnAddVariant?.addEventListener('click', () => {
    if (!variantsTbody) return;
    variantsTbody.insertAdjacentHTML('beforeend', newVariantRow(newIdx));
    newIdx++;
  });

  variantsTbody?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-remove-new]');
    if (!btn) return;
    btn.closest('tr')?.remove();
  });
});
</script>

@endsection