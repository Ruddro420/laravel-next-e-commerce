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
                <select id="productType" name="product_type"
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
                <input id="short_description_input" type="hidden" name="short_description" value="{{ old('short_description') }}">
                <trix-editor input="short_description_input"
                    class="mt-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm dark:bg-slate-900 dark:border-slate-800"></trix-editor>

            </div>
            <div>
                <label class="text-sm font-semibold">Description</label>
                <input id="description_input" type="hidden" name="description" value="{{ old('description') }}">
                <trix-editor input="description_input"
                    class="mt-2 rounded-2xl border border-slate-200 bg-white p-3 text-sm dark:bg-slate-900 dark:border-slate-800"></trix-editor>

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
        <!-- <div id="variantBlock" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
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
        </div> -->

        {{-- Variable Attributes (only for variable product) --}}
        {{-- Variable Attributes --}}
        <div id="variableBox" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold">Variable Attributes</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Select attribute and choose options (values).
                    </div>
                </div>

                <button type="button" id="btnAddAttr"
                    class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
                    + Add Attribute
                </button>
            </div>

            <div id="attrWrap" class="mt-4 space-y-3"></div>
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
async function loadValues(attributeId, selectEl, selectedIds = []) {
  if (!attributeId || !selectEl) return;

  selectEl.innerHTML = `<option value="">Loading...</option>`;

  const url = `{{ route('attributeValues') }}?attribute_id=${attributeId}`;

  try {
    const res = await fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });

    // If auth redirect happens, you'll usually get HTML here
    const contentType = res.headers.get("content-type") || "";

    if (!res.ok) {
      const text = await res.text();
      console.error("Fetch failed:", res.status, text);
      selectEl.innerHTML = `<option value="">Error: ${res.status}</option>`;
      return;
    }

    // If server responded with HTML, it means redirect/login page
    if (!contentType.includes("application/json")) {
      const text = await res.text();
      console.error("Expected JSON but got:", text);
      selectEl.innerHTML = `<option value="">Not JSON (check auth/route)</option>`;
      return;
    }

    const data = await res.json();

    if (!Array.isArray(data) || data.length === 0) {
      selectEl.innerHTML = `<option value="">No values found</option>`;
      return;
    }

    selectEl.innerHTML = data.map(v => {
      const sel = selectedIds.includes(parseInt(v.id)) ? 'selected' : '';
      return `<option value="${v.id}" ${sel}>${escapeHtml(v.label)}</option>`;
    }).join('');

  } catch (err) {
    console.error("Error loading values:", err);
    selectEl.innerHTML = `<option value="">Error loading values</option>`;
  }
}
</script>


@endsection