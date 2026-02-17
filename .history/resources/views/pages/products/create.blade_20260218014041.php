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
        <div id="variantBox" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="font-semibold">Variants</div>
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        Set price, stock, sku, image per variant combination.
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
  // -----------------------------
  // Helpers
  // -----------------------------
  function escapeHtml(str = "") {
    return String(str)
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");
  }

  // Laravel attributes list
  const ATTRIBUTES = @json($attributes->map(fn($a) => ['id'=>$a->id,'name'=>$a->name]));

  // -----------------------------
  // AJAX: Load attribute values
  // endpoint: route('attributeValues') -> returns [{id,label}]
  // -----------------------------
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

  // -----------------------------
  // UI: Toggle blocks
  // -----------------------------
  function toggleProductTypeUI() {
    const type = document.getElementById("productType")?.value;

    const simpleBlock = document.getElementById("simpleBlock");
    const downloadBlock = document.getElementById("downloadBlock");
    const variableBox = document.getElementById("variableBox");
    const variantBox  = document.getElementById("variantBox");

    if (!simpleBlock || !downloadBlock || !variableBox || !variantBox) return;

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

  // -----------------------------
  // Attribute rows UI
  // Each row: attribute select + values multiselect
  // -----------------------------
  function makeAttrRow(index) {
    const row = document.createElement("div");
    row.className =
      "grid grid-cols-1 md:grid-cols-12 gap-3 items-end rounded-2xl border border-slate-200 p-3 dark:border-slate-800";

    row.innerHTML = `
      <div class="md:col-span-4">
        <label class="text-sm font-semibold">Attribute</label>
        <select
          name="variable_attributes[${index}][attribute_id]"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800 attr-select">
          <option value="">Select attribute</option>
          ${ATTRIBUTES.map(a => `<option value="${a.id}">${escapeHtml(a.name)}</option>`).join("")}
        </select>
      </div>

      <div class="md:col-span-7">
        <label class="text-sm font-semibold">Values</label>
        <select multiple
          name="variable_attributes[${index}][value_ids][]"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800 values-select">
          <option value="">Select attribute first</option>
        </select>
        <div class="text-xs text-slate-500 mt-1">Hold Ctrl/⌘ to select multiple.</div>
      </div>

      <div class="md:col-span-1 flex justify-end">
        <button type="button"
          class="remove-attr rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
          ✕
        </button>
      </div>
    `;

    const attrSelect = row.querySelector(".attr-select");
    const valuesSelect = row.querySelector(".values-select");
    const removeBtn = row.querySelector(".remove-attr");

    attrSelect.addEventListener("change", async (e) => {
      await loadValues(e.target.value, valuesSelect, []);
    });

    removeBtn.addEventListener("click", () => {
      row.remove();
      // when attribute rows change, variants should be regenerated by user
    });

    return row;
  }

  // -----------------------------
  // Variant generation
  // -----------------------------

  // Collect chosen attributes & selected values from UI
  function collectSelections() {
    const wrap = document.getElementById("attrWrap");
    if (!wrap) return [];

    const rows = [...wrap.querySelectorAll("div")];
    const selections = [];

    for (const row of rows) {
      const attrSelect = row.querySelector(".attr-select");
      const valuesSelect = row.querySelector(".values-select");

      const attributeId = attrSelect?.value ? parseInt(attrSelect.value) : null;
      const attributeName = attrSelect?.selectedOptions?.[0]?.textContent?.trim() || "";

      const valueIds = valuesSelect
        ? [...valuesSelect.selectedOptions].map(o => parseInt(o.value)).filter(Boolean)
        : [];

      const valueLabels = valuesSelect
        ? [...valuesSelect.selectedOptions].map(o => o.textContent.trim())
        : [];

      if (attributeId && valueIds.length) {
        selections.push({
          attribute_id: attributeId,
          attribute_name: attributeName,
          value_ids: valueIds,
          value_labels: valueLabels
        });
      }
    }

    return selections;
  }

  // Cartesian product for combinations
  function cartesian(selections) {
    // selections: [{attribute_id, attribute_name, value_ids, value_labels}]
    let result = [{ attrs: {} }];

    for (const sel of selections) {
      const newRes = [];
      for (const r of result) {
        for (let i = 0; i < sel.value_ids.length; i++) {
          const valueId = sel.value_ids[i];
          const valueLabel = sel.value_labels[i];

          const next = JSON.parse(JSON.stringify(r));
          next.attrs[sel.attribute_name] = {
            attribute_id: sel.attribute_id,
            value_id: valueId,
            value_label: valueLabel
          };
          newRes.push(next);
        }
      }
      result = newRes;
    }

    return result;
  }

  // Create a stable key like "Color:Red|Size:M"
  function comboKey(attrsObj) {
    const keys = Object.keys(attrsObj).sort();
    return keys.map(k => `${k}:${attrsObj[k].value_label}`).join("|");
  }

  function renderVariantTable(combos) {
    const wrap = document.getElementById("variantTableWrap");
    if (!wrap) return;

    if (!combos.length) {
      wrap.innerHTML = `<div class="text-sm text-slate-500">No variants generated.</div>`;
      return;
    }

    // Build table
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
      const attrs = c.attrs;
      const key = comboKey(attrs);

      const display = Object.keys(attrs)
        .sort()
        .map(k => `${escapeHtml(k)}: <span class="font-semibold">${escapeHtml(attrs[k].value_label)}</span>`)
        .join(", ");

      // Convert to "attributes_json" for backend
      // We want simple JSON like {"Size":"M","Color":"Red"} (human readable)
      const attributesJson = {};
      Object.keys(attrs).forEach(name => {
        attributesJson[name] = attrs[name].value_label;
      });

      html += `
        <tr class="border-t border-slate-200 dark:border-slate-800">
          <td class="p-3 text-sm">
            ${display}
            <input type="hidden" name="variants[${idx}][attributes_json]" value='${escapeHtml(JSON.stringify(attributesJson))}'>
            <input type="hidden" name="variants[${idx}][key]" value="${escapeHtml(key)}">
          </td>

          <td class="p-3">
            <input name="variants[${idx}][sku]" class="w-40 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </td>

          <td class="p-3">
            <input type="number" step="0.01" name="variants[${idx}][regular_price]"
              class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </td>

          <td class="p-3">
            <input type="number" step="0.01" name="variants[${idx}][sale_price]"
              class="w-28 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </td>

          <td class="p-3">
            <input type="number" name="variants[${idx}][stock]"
              class="w-24 rounded-xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </td>

          <td class="p-3">
            <input type="file" name="variants[${idx}][image]" accept="image/*"
              class="text-sm text-slate-600 dark:text-slate-300" />
          </td>
        </tr>
      `;
    });

    html += `</tbody></table>`;

    wrap.innerHTML = html;
  }

  // -----------------------------
  // Init
  // -----------------------------
  document.addEventListener("DOMContentLoaded", () => {
    const productType = document.getElementById("productType");
    const btnAddAttr = document.getElementById("btnAddAttr");
    const attrWrap = document.getElementById("attrWrap");
    const btnGenerateVariants = document.getElementById("btnGenerateVariants");

    toggleProductTypeUI();

    productType?.addEventListener("change", () => {
      toggleProductTypeUI();

      // if switching to variable and no rows -> add 1 default row
      if (productType.value === "variable" && attrWrap && attrWrap.children.length === 0) {
        attrWrap.appendChild(makeAttrRow(0));
      }
    });

    let attrIndex = 0;
    btnAddAttr?.addEventListener("click", () => {
      if (!attrWrap) return;
      attrWrap.appendChild(makeAttrRow(attrIndex++));
    });

    // Ensure one row if already variable on load
    if (productType?.value === "variable" && attrWrap && attrWrap.children.length === 0) {
      attrWrap.appendChild(makeAttrRow(0));
    }

    // Generate Variants
    btnGenerateVariants?.addEventListener("click", () => {
      const selections = collectSelections();

      if (!selections.length) {
        alert("Please select at least one attribute and values.");
        return;
      }

      const combos = cartesian(selections);

      if (!combos.length) {
        alert("No combinations created. Select values for each attribute.");
        return;
      }

      renderVariantTable(combos);
    });
  });
</script>





@endsection