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
                    <option value="simple" {{ old('product_type','simple')=='simple'?'selected':'' }}>Simple Product</option>
                    <option value="variable" {{ old('product_type')=='variable'?'selected':'' }}>Variable Product</option>
                    <option value="downloadable" {{ old('product_type')=='downloadable'?'selected':'' }}>Downloadable Product</option>
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
                      <option value="{{ $c->id }}" {{ old('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-sm font-semibold">Brand</label>
                <select name="brand_id"
                    class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                    <option value="">Select</option>
                    @foreach($brands as $b)
                      <option value="{{ $b->id }}" {{ old('brand_id')==$b->id?'selected':'' }}>{{ $b->name }}</option>
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

        {{-- Variants --}}
        <div id="variantBox" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold">Variants</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Generate combinations and set price/sku/stock/image per variant.
                    </div>
                </div>

                <button type="button" id="btnGenerateVariants"
                    class="rounded-2xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                    Generate Variants
                </button>
            </div>

            <div id="variantTableWrap" class="mt-4 overflow-x-auto"></div>
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
  function escapeHtml(str = "") {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  const ATTRIBUTES = @json($attributes->map(fn($a) => ['id'=>$a->id,'name'=>$a->name]));

  async function loadValues(attributeId, selectEl, selectedIds = []) {
    if (!attributeId || !selectEl) return;

    selectEl.innerHTML = `<option value="">Loading...</option>`;
    const url = `{{ route('attributeValues') }}?attribute_id=${attributeId}`;

    try {
      const res = await fetch(url, {
        method: "GET",
        credentials: "same-origin",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "application/json",
        },
      });

      const contentType = res.headers.get("content-type") || "";
      if (!res.ok) {
        const text = await res.text();
        console.error("attribute-values failed:", res.status, text);
        selectEl.innerHTML = `<option value="">Error ${res.status}</option>`;
        return;
      }

      if (!contentType.includes("application/json")) {
        const text = await res.text();
        console.error("Expected JSON but got:", text);
        selectEl.innerHTML = `<option value="">Not JSON (auth/route)</option>`;
        return;
      }

      const data = await res.json();
      if (!Array.isArray(data) || data.length === 0) {
        selectEl.innerHTML = `<option value="">No values found</option>`;
        return;
      }

      selectEl.innerHTML = data.map(v => {
        const id = parseInt(v.id);
        const label = v.label ?? "";
        const sel = selectedIds.includes(id) ? "selected" : "";
        return `<option value="${id}" ${sel}>${escapeHtml(label)}</option>`;
      }).join("");

    } catch (err) {
      console.error("Error loading values:", err);
      selectEl.innerHTML = `<option value="">Error loading values</option>`;
    }
  }

  function toggleProductTypeUI() {
    const type = document.getElementById("productType")?.value;

    const simpleBlock = document.getElementById("simpleBlock");
    const downloadBlock = document.getElementById("downloadBlock");
    const variableBox = document.getElementById("variableBox");
    const variantBox = document.getElementById("variantBox");

    if (!simpleBlock || !downloadBlock || !variableBox || !variantBox) return;

    // Hide all first
    simpleBlock.classList.add("hidden");
    downloadBlock.classList.add("hidden");
    variableBox.classList.add("hidden");
    variantBox.classList.add("hidden");

    if (type === "simple") {
      simpleBlock.classList.remove("hidden");
    } else if (type === "downloadable") {
      simpleBlock.classList.remove("hidden");
      downloadBlock.classList.remove("hidden");
    } else if (type === "variable") {
      variableBox.classList.remove("hidden");
      variantBox.classList.remove("hidden");
    }
  }

  function makeAttrRow(index) {
    const row = document.createElement("div");
    row.className =
      "grid grid-cols-1 md:grid-cols-12 gap-3 items-end rounded-2xl border border-slate-200 p-3 dark:border-slate-800";

    row.innerHTML = `
      <div class="md:col-span-4">
        <label class="text-sm font-semibold">Attribute</label>
        <select class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800 attr-select">
          <option value="">Select attribute</option>
          ${ATTRIBUTES.map(a => `<option value="${a.id}">${escapeHtml(a.name)}</option>`).join("")}
        </select>
      </div>

      <div class="md:col-span-7">
        <label class="text-sm font-semibold">Values</label>
        <select multiple class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800 values-select">
          <option value="">Select attribute first</option>
        </select>
        <div class="text-xs text-slate-500 mt-1">Hold Ctrl/⌘ to select multiple.</div>
      </div>

      <div class="md:col-span-1 flex justify-end">
        <button type="button"
          class="remove-attr rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">✕</button>
      </div>
    `;

    const attrSelect = row.querySelector(".attr-select");
    const valuesSelect = row.querySelector(".values-select");
    const removeBtn = row.querySelector(".remove-attr");

    attrSelect.addEventListener("change", async (e) => {
      await loadValues(e.target.value, valuesSelect, []);
    });

    removeBtn.addEventListener("click", () => row.remove());

    return row;
  }

  function collectSelections() {
    const wrap = document.getElementById("attrWrap");
    if (!wrap) return [];

    const rows = [...wrap.children];
    const selections = [];

    for (const row of rows) {
      const attrSelect = row.querySelector(".attr-select");
      const valuesSelect = row.querySelector(".values-select");
      const attributeId = attrSelect?.value ? parseInt(attrSelect.value) : null;
      const attributeName = attrSelect?.selectedOptions?.[0]?.textContent?.trim() || "";

      const selected = valuesSelect ? [...valuesSelect.selectedOptions] : [];
      const valueIds = selected.map(o => parseInt(o.value)).filter(Boolean);
      const valueLabels = selected.map(o => o.textContent.trim());

      if (attributeId && valueIds.length) {
        selections.push({ attributeId, attributeName, valueIds, valueLabels });
      }
    }
    return selections;
  }

  function cartesian(selections) {
    let res = [{ attrs: {} }];

    for (const sel of selections) {
      const next = [];
      for (const r of res) {
        for (let i = 0; i < sel.valueIds.length; i++) {
          const copy = JSON.parse(JSON.stringify(r));
          copy.attrs[sel.attributeName] = sel.valueLabels[i]; // store label
          next.push(copy);
        }
      }
      res = next;
    }
    return res;
  }

  function renderVariants(combos) {
    const wrap = document.getElementById("variantTableWrap");
    if (!wrap) return;

    if (!combos.length) {
      wrap.innerHTML = `<div class="text-sm text-slate-500">No variants generated.</div>`;
      return;
    }

    let html = `
      <table class="min-w-full border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
        <thead class="bg-slate-50 dark:bg-slate-800">
          <tr class="text-left text-xs uppercase tracking-wide text-slate-600 dark:text-slate-300">
            <th class="p-3">Variant</th>
            <th class="p-3">SKU</th>
            <th class="p-3">Regular</th>
            <th class="p-3">Sale</th>
            <th class="p-3">Stock</th>
            <th class="p-3">Image</th>
          </tr>
        </thead>
        <tbody>
    `;

    combos.forEach((c, idx) => {
      const keys = Object.keys(c.attrs).sort();
      const label = keys.map(k => `${escapeHtml(k)}: <b>${escapeHtml(c.attrs[k])}</b>`).join(", ");
      const attributesJson = {};
      keys.forEach(k => attributesJson[k] = c.attrs[k]);

      html += `
        <tr class="border-t border-slate-200 dark:border-slate-800">
          <td class="p-3 text-sm">
            ${label}
            <input type="hidden" name="variants[${idx}][attributes_json]" value='${escapeHtml(JSON.stringify(attributesJson))}'>
          </td>
          <td class="p-3"><input name="variants[${idx}][sku]" class="w-40 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" /></td>
          <td class="p-3"><input type="number" step="0.01" name="variants[${idx}][regular_price]" class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" /></td>
          <td class="p-3"><input type="number" step="0.01" name="variants[${idx}][sale_price]" class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" /></td>
          <td class="p-3"><input type="number" name="variants[${idx}][stock]" class="w-24 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" /></td>
          <td class="p-3"><input type="file" accept="image/*" name="variants[${idx}][image]" class="text-sm text-slate-600 dark:text-slate-300" /></td>
        </tr>
      `;
    });

    html += `</tbody></table>`;
    wrap.innerHTML = html;
  }

  document.addEventListener("DOMContentLoaded", () => {
    const productType = document.getElementById("productType");
    const btnAddAttr = document.getElementById("btnAddAttr");
    const attrWrap = document.getElementById("attrWrap");
    const btnGenerateVariants = document.getElementById("btnGenerateVariants");

    // Always toggle on load
    toggleProductTypeUI();

    // Toggle on change
    productType?.addEventListener("change", () => {
      toggleProductTypeUI();

      // auto add one attribute row when switching to variable
      if (productType.value === "variable" && attrWrap && attrWrap.children.length === 0) {
        attrWrap.appendChild(makeAttrRow(0));
      }
    });

    // add attribute row
    let attrIndex = 0;
    btnAddAttr?.addEventListener("click", () => {
      if (!attrWrap) return;
      attrWrap.appendChild(makeAttrRow(attrIndex++));
    });

    // if already variable on load, create one row
    if (productType?.value === "variable" && attrWrap && attrWrap.children.length === 0) {
      attrWrap.appendChild(makeAttrRow(0));
    }

    // generate variants
    btnGenerateVariants?.addEventListener("click", () => {
      const selections = collectSelections();
      if (!selections.length) {
        alert("Select attributes and values first.");
        return;
      }
      const combos = cartesian(selections);
      renderVariants(combos);
    });
  });
</script>
@endsection
