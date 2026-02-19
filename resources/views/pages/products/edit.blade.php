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
        <label class="text-sm font-semibold">Product Name</label>
        <input name="name" value="{{ old('name',$product->name) }}" required
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">SKU</label>
        <input name="sku" value="{{ old('sku',$product->sku) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

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

    {{-- Gallery (optional) --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Gallery</div>

      @if($product->gallery && $product->gallery->count())
        <div class="mb-3 grid grid-cols-2 md:grid-cols-6 gap-3">
          @foreach($product->gallery as $img)
            <div class="rounded-xl border border-slate-200 overflow-hidden dark:border-slate-800">
              {{-- adjust field name (path/url) based on your model --}}
              <img src="{{ asset($img->path ?? $img->url ?? '') }}" class="h-24 w-full object-cover" alt="">
            </div>
          @endforeach
        </div>
      @else
        <div class="text-sm text-slate-500 dark:text-slate-400 mb-3">No gallery images.</div>
      @endif

      <label class="text-sm font-semibold">Upload New Images (optional)</label>
      <input type="file" name="images[]" multiple
        class="mt-2 block w-full text-sm"
      />
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
              </tr>
            </thead>
            <tbody>
              @foreach($product->variants as $v)
                <tr class="border-t border-slate-100 dark:border-slate-800">
                  <td class="py-2 pr-3">{{ $v->name ?? '—' }}</td>
                  <td class="py-2 pr-3">{{ $v->sku ?? '—' }}</td>
                  <td class="py-2 pr-3">{{ number_format($v->price ?? 0, 2) }}</td>
                  <td class="py-2 pr-3">{{ $v->stock ?? 0 }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-sm text-slate-500 dark:text-slate-400">No variants.</div>
      @endif
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
@endsection
