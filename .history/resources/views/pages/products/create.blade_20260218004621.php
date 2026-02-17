@extends('layouts.app')
@section('title','Add Product')
@section('subtitle','Products')
@section('pageTitle','Add Product')
@section('pageDesc','Create a new product (simple, variable, downloadable).')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  @if($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf

    {{-- Basic --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Name</label>
        <input id="pName" name="name" value="{{ old('name') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Slug</label>
        <input id="pSlug" name="slug" value="{{ old('slug') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="leave empty for auto" />
      </div>

      <div>
        <label class="text-sm font-semibold">Product Type</label>
        <select id="pType" name="product_type"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="simple">Simple Product</option>
          <option value="variable">Variable Product</option>
          <option value="downloadable">Downloadable Product</option>
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">SKU</label>
        <input name="sku" value="{{ old('sku') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Barcode</label>
        <input name="barcode" value="{{ old('barcode') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Shipping Price</label>
        <input name="shipping_price" value="{{ old('shipping_price') }}" type="number" step="0.01"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    {{-- Description --}}
    <div class="grid grid-cols-1 gap-4">
      <div>
        <label class="text-sm font-semibold">Short Description</label>
        <textarea name="short_description" rows="3"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('short_description') }}</textarea>
      </div>
      <div>
        <label class="text-sm font-semibold">Description</label>
        <textarea name="description" rows="6"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('description') }}</textarea>
      </div>
    </div>

    {{-- Category / Brand --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Category</label>
        <select name="category_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}">{{ $c->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Brand</label>
        <select name="brand_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select</option>
          @foreach($brands as $b)
            <option value="{{ $b->id }}">{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Simple/Downloadable pricing --}}
    <div id="simpleBlock" class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-semibold">Regular Price</label>
          <input name="regular_price" value="{{ old('regular_price') }}" type="number" step="0.01"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Sale Price</label>
          <input name="sale_price" value="{{ old('sale_price') }}" type="number" step="0.01"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Stock</label>
          <input name="stock" value="{{ old('stock') }}" type="number"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
    </div>

    {{-- Download file --}}
    <div id="downloadBlock" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <label class="text-sm font-semibold">Download File (PDF/ZIP)</label>
      <input type="file" name="download_file" accept=".pdf,.zip"
        class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
    </div>

    {{-- Variable variants --}}
    <div id="variantBlock" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Variants</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Each variant uses JSON attributes. Example: {"Size":"M","Color":"Black"}</div>
        </div>
        <button type="button" id="btnAddVariant"
          class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
          + Add Variant
        </button>
      </div>

      <div id="variantList" class="mt-4 space-y-3"></div>
    </div>

    {{-- Images --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <label class="text-sm font-semibold">Featured Image</label>
        <input type="file" name="featured_image" accept="image/*"
          class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
      </div>

      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <label class="text-sm font-semibold">Gallery Images</label>
        <input type="file" name="gallery_images[]" multiple accept="image/*"
          class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
      </div>
    </div>

    <div class="flex items-center justify-end gap-2">
      <a href="{{ route('products.index') }}"
        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        Cancel
      </a>
      <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Product
      </button>
    </div>
  </form>
</div>

<script>
(function(){
  // slug auto (only if slug empty)
  const name = document.getElementById('pName');
  const slug = document.getElementById('pSlug');
  function toSlug(v){ return v.toLowerCase().trim().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-'); }
  name?.addEventListener('input', ()=>{ if(slug.value.trim()) return; slug.value = toSlug(name.value); });

  // type toggle
  const type = document.getElementById('pType');
  const simpleBlock = document.getElementById('simpleBlock');
  const variantBlock = document.getElementById('variantBlock');
  const downloadBlock = document.getElementById('downloadBlock');

  function syncType(){
    const v = type.value;
    simpleBlock.classList.toggle('hidden', v === 'variable');
    variantBlock.classList.toggle('hidden', v !== 'variable');
    downloadBlock.classList.toggle('hidden', v !== 'downloadable');
  }
  type.addEventListener('change', syncType);
  syncType();

  // variants
  const list = document.getElementById('variantList');
  const addBtn = document.getElementById('btnAddVariant');
  let idx = 0;

  function variantRow(i){
    return `
      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold">Variant #${i+1}</div>
          <button type="button" class="text-xs font-semibold text-rose-600" data-remove>Remove</button>
        </div>

        <div class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-2">
          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Attributes JSON</label>
            <input name="variants[${i}][attributes_json]" placeholder='{"Size":"M","Color":"Black"}'
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Variant Image</label>
            <input type="file" name="variants[${i}][image]" accept="image/*"
              class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Regular Price</label>
            <input name="variants[${i}][regular_price]" type="number" step="0.01"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sale Price</label>
            <input name="variants[${i}][sale_price]" type="number" step="0.01"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Stock</label>
            <input name="variants[${i}][stock]" type="number"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU</label>
            <input name="variants[${i}][sku]"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>
        </div>
      </div>
    `;
  }

  addBtn?.addEventListener('click', ()=>{
    list.insertAdjacentHTML('beforeend', variantRow(idx));
    idx++;
  });

  list?.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-remove]');
    if(!btn) return;
    btn.closest('.rounded-2xl')?.remove();
  });
})();
</script>
@endsection
