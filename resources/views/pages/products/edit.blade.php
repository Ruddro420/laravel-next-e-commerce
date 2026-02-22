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
    // Selected attribute value IDs for pre-check
    $selectedAttrValueIds = collect(old('attribute_value_ids', $product->attributeValues?->pluck('id')?->toArray() ?? []))
      ->map(fn($v)=> (int)$v)
      ->values()
      ->all();
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
        @php $productType = old('product_type', $product->product_type ?? 'simple'); @endphp
        <select name="product_type" required
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
            <div class="relative rounded-xl border border-slate-200 overflow-hidden dark:border-slate-800 group">
              <img src="{{ asset('storage/' . ($img->path ?? $img->image ?? '')) }}" class="h-24 w-full object-cover" alt="">
              <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <button type="button" class="text-white bg-red-500 rounded-full p-1" onclick="this.closest('div').remove()">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
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

    {{-- Variants (simple editable UI, optional) --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Variants</div>

      @if($product->variants && $product->variants->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left text-xs uppercase text-slate-500 dark:text-slate-400">
                <th class="py-2 pr-3">Name</th>
                <th class="py-2 pr-3">SKU</th>
                <th class="py-2 pr-3">Price</th>
                <th class="py-2 pr-3">Stock</th>
                <th class="py-2 pr-3">Attributes</th>
              </tr>
            </thead>
            <tbody>
              @foreach($product->variants as $v)
                <tr class="border-t border-slate-100 dark:border-slate-800">
                  <td class="py-2 pr-3">{{ $v->name ?? '—' }}</td>
                  <td class="py-2 pr-3">{{ $v->sku ?? '—' }}</td>
                  <td class="py-2 pr-3">
                    @if($v->sale_price)
                      <span class="line-through text-slate-400">{{ number_format($v->regular_price ?? 0, 2) }}</span>
                      <span class="text-emerald-600 font-semibold">{{ number_format($v->sale_price, 2) }}</span>
                    @else
                      {{ number_format($v->regular_price ?? 0, 2) }}
                    @endif
                  </td>
                  <td class="py-2 pr-3">{{ $v->stock ?? 0 }}</td>
                  <td class="py-2 pr-3">
                    @if($v->attributes && is_array($v->attributes))
                      @foreach($v->attributes as $key => $value)
                        <span class="inline-block bg-slate-100 rounded-full px-2 py-1 text-xs mr-1 mb-1 dark:bg-slate-800">{{ $key }}: {{ $value }}</span>
                      @endforeach
                    @else
                      —
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">
          To manage variants, use the variant management section in product details.
        </div>
      @else
        <div class="text-sm text-slate-500 dark:text-slate-400">No variants. You can add variants after creating the product.</div>
      @endif
    </div>

    {{-- Meta Information (SEO) --}}
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

@push('scripts')
<script>
  // Optional: Show/hide variant section based on product type selection
  document.addEventListener('DOMContentLoaded', function() {
    const productTypeSelect = document.querySelector('select[name="product_type"]');
    const variantsSection = document.querySelector('.rounded-2xl.border-slate-200.p-4:has(.font-semibold:contains("Variants"))');
    
    if (productTypeSelect && variantsSection) {
      function toggleVariantsSection() {
        if (productTypeSelect.value === 'variable') {
          variantsSection.style.display = 'block';
        } else {
          variantsSection.style.display = 'none';
        }
      }
      
      productTypeSelect.addEventListener('change', toggleVariantsSection);
      toggleVariantsSection(); // Initial state
    }
  });
</script>
@endpush

@endsection