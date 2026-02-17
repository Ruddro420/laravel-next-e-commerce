@extends('layouts.app')
@section('title','Add Product')
@section('subtitle','Products')
@section('pageTitle','Add Product')
@section('pageDesc','Create a new product (simple, variable, downloadable).')

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
    /* Quill editor customization */
    .quill-editor {
        @apply mt-2 rounded-2xl border border-slate-200 bg-white dark:bg-slate-900 dark:border-slate-800;
    }
    .ql-toolbar {
        @apply rounded-t-2xl border-b border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700;
    }
    .ql-container {
        @apply rounded-b-2xl font-sans text-sm dark:text-slate-200;
        min-height: 120px;
    }
    .ql-editor {
        @apply min-h-[120px];
    }
    .ql-editor.ql-blank::before {
        @apply text-slate-400 dark:text-slate-500 not-italic;
    }
    
    /* Attribute chips styling */
    .attribute-chip {
        @apply inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300;
    }
    .attribute-value-badge {
        @apply rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300;
    }
</style>
@endpush

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
        <label class="text-sm font-semibold">Name <span class="text-rose-500">*</span></label>
        <input id="pName" name="name" value="{{ old('name') }}" required
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
        <label class="text-sm font-semibold">SKU <span class="text-xs text-slate-500">(Stock Keeping Unit)</span></label>
        <input name="sku" value="{{ old('sku') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Shipping Price</label>
        <input name="shipping_price" value="{{ old('shipping_price') }}" type="number" step="0.01" min="0"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    {{-- Rich Text Editors --}}
    <div class="grid grid-cols-1 gap-4">
      <div>
        <label class="text-sm font-semibold">Short Description</label>
        <div class="quill-editor" id="shortDescEditor"></div>
        <textarea name="short_description" id="shortDescTextarea" class="hidden">{{ old('short_description') }}</textarea>
      </div>
      
      <div>
        <label class="text-sm font-semibold">Description</label>
        <div class="quill-editor" id="descEditor"></div>
        <textarea name="description" id="descTextarea" class="hidden">{{ old('description') }}</textarea>
      </div>
    </div>

    {{-- Category / Brand --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Category</label>
        <select name="category_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select Category</option>
          @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Brand</label>
        <select name="brand_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select Brand</option>
          @foreach($brands as $b)
            <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Simple/Downloadable pricing --}}
    <div id="simpleBlock" class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-semibold">Regular Price</label>
          <input name="regular_price" value="{{ old('regular_price') }}" type="number" step="0.01" min="0"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Sale Price</label>
          <input name="sale_price" value="{{ old('sale_price') }}" type="number" step="0.01" min="0"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Stock</label>
          <input name="stock" value="{{ old('stock', 0) }}" type="number" min="0"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
    </div>

    {{-- Download file --}}
    <div id="downloadBlock" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <label class="text-sm font-semibold">Download File (PDF/ZIP)</label>
      <input type="file" name="download_file" accept=".pdf,.zip,.doc,.docx"
        class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
      <p class="mt-1 text-xs text-slate-500">Max file size: 10MB. Allowed: PDF, ZIP, DOC, DOCX</p>
    </div>

    {{-- Variable variants with Dynamic Attributes --}}
    <div id="variantBlock" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between mb-4">
        <div>
          <div class="font-semibold">Product Variants</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Create variants by combining attributes</div>
        </div>
        <div class="flex gap-2">
          <button type="button" id="btnGenerateVariants"
            class="rounded-2xl bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
            Generate from Attributes
          </button>
          <button type="button" id="btnAddVariant"
            class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
            + Add Manual Variant
          </button>
        </div>
      </div>

      {{-- Attribute Selection Section --}}
      <div class="mb-6 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
        <div class="mb-3 flex items-center justify-between">
          <h4 class="text-sm font-semibold">Select Attributes</h4>
          <button type="button" id="btnAddAttribute" 
            class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
            + Add Attribute
          </button>
        </div>
        
        <div id="attributesContainer" class="space-y-3">
          <!-- Attributes will be added here dynamically -->
        </div>
        
        <div id="selectedAttributesPreview" class="mt-3 flex flex-wrap gap-2">
          <!-- Selected attributes preview will appear here -->
        </div>
      </div>

      <div id="variantList" class="mt-4 space-y-3"></div>
    </div>

    {{-- Images --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <label class="text-sm font-semibold">Featured Image</label>
        <input type="file" name="featured_image" accept="image/*"
          class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
        <p class="mt-1 text-xs text-slate-500">Recommended size: 800x800px</p>
      </div>

      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <label class="text-sm font-semibold">Gallery Images</label>
        <input type="file" name="gallery_images[]" multiple accept="image/*"
          class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
        <p class="mt-1 text-xs text-slate-500">You can select multiple images</p>
      </div>
    </div>

    {{-- Status --}}
    <div class="flex items-center gap-2">
      <input type="checkbox" name="is_active" id="is_active" value="1" checked
        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
      <label for="is_active" class="text-sm font-medium">Active (visible in store)</label>
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

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
(function(){
  // Slug auto-generation
  const name = document.getElementById('pName');
  const slug = document.getElementById('pSlug');
  function toSlug(v){ return v.toLowerCase().trim().replace(/[^a-z0-9\s-]/g,'').replace(/\s+/g,'-').replace(/-+/g,'-'); }
  name?.addEventListener('input', ()=>{ if(!slug.value.trim()) slug.value = toSlug(name.value); });

  // Rich Text Editors
  const shortDescEditor = new Quill('#shortDescEditor', {
    theme: 'snow',
    placeholder: 'Write a short description...',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['link']
      ]
    }
  });
  
  const descEditor = new Quill('#descEditor', {
    theme: 'snow',
    placeholder: 'Write detailed description...',
    modules: {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'script': 'sub'}, { 'script': 'super' }],
        [{ 'indent': '-1'}, { 'indent': '+1' }],
        [{ 'direction': 'rtl' }],
        [{ 'size': ['small', false, 'large', 'huge'] }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'color': [] }, { 'background': [] }],
        [{ 'font': [] }],
        [{ 'align': [] }],
        ['clean'],
        ['link', 'image']
      ]
    }
  });

  // Set initial content
  const oldShortDesc = document.getElementById('shortDescTextarea').value;
  const oldDesc = document.getElementById('descTextarea').value;
  if (oldShortDesc) shortDescEditor.root.innerHTML = oldShortDesc;
  if (oldDesc) descEditor.root.innerHTML = oldDesc;

  // Update textareas on form submit
  document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('shortDescTextarea').value = shortDescEditor.root.innerHTML;
    document.getElementById('descTextarea').value = descEditor.root.innerHTML;
  });

  // Type toggle
  const type = document.getElementById('pType');
  const simpleBlock = document.getElementById('simpleBlock');
  const variantBlock = document.getElementById('variantBlock');
  const downloadBlock = document.getElementById('downloadBlock');

  function syncType(){
    const v = type.value;
    simpleBlock.classList.toggle('hidden', v === 'variable');
    variantBlock.classList.toggle('hidden', v !== 'variable');
    downloadBlock.classList.toggle('hidden', v !== 'downloadable');
    
    // Disable/enable relevant fields
    const priceInputs = simpleBlock.querySelectorAll('input');
    priceInputs.forEach(input => {
      input.disabled = v === 'variable';
    });
  }
  type.addEventListener('change', syncType);
  syncType();

  // Attributes Management
  const attributesData = @json($attributes ?? []); // You need to pass attributes from controller
  let attributeCounter = 0;
  const attributesContainer = document.getElementById('attributesContainer');
  const selectedAttributesPreview = document.getElementById('selectedAttributesPreview');
  const variantList = document.getElementById('variantList');
  let variantCounter = 0;

  // Add attribute dropdown
  document.getElementById('btnAddAttribute').addEventListener('click', function() {
    const attributeHtml = `
      <div class="attribute-row flex items-start gap-2" data-attr-idx="${attributeCounter}">
        <div class="flex-1">
          <select class="attribute-select w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="">Select Attribute</option>
            @foreach($attributes ?? [] as $attr)
              <option value="{{ $attr->id }}" data-values='@json($attr->values ?? [])'>{{ $attr->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="flex-1">
          <select class="attribute-values w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" multiple size="3">
            <option value="">Select values (multiple)</option>
          </select>
        </div>
        <button type="button" class="remove-attribute text-rose-600 hover:text-rose-700">
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    `;
    attributesContainer.insertAdjacentHTML('beforeend', attributeHtml);
    attributeCounter++;
  });

  // Handle attribute selection changes
  attributesContainer.addEventListener('change', function(e) {
    if (e.target.classList.contains('attribute-select')) {
      const row = e.target.closest('.attribute-row');
      const valuesSelect = row.querySelector('.attribute-values');
      const selectedOption = e.target.options[e.target.selectedIndex];
      
      if (selectedOption.value) {
        try {
          const values = JSON.parse(selectedOption.dataset.values || '[]');
          valuesSelect.innerHTML = values.map(v => 
            `<option value="${v}">${v}</option>`
          ).join('');
        } catch (e) {
          console.error('Error parsing attribute values:', e);
        }
      } else {
        valuesSelect.innerHTML = '<option value="">Select values (multiple)</option>';
      }
    }
    updateAttributesPreview();
  });

  // Remove attribute
  attributesContainer.addEventListener('click', function(e) {
    if (e.target.closest('.remove-attribute')) {
      e.target.closest('.attribute-row').remove();
      updateAttributesPreview();
    }
  });

  // Update preview and generate variants
  function updateAttributesPreview() {
    const selectedAttributes = [];
    document.querySelectorAll('.attribute-row').forEach(row => {
      const select = row.querySelector('.attribute-select');
      const valuesSelect = row.querySelector('.attribute-values');
      const attributeName = select.options[select.selectedIndex]?.text;
      const selectedValues = Array.from(valuesSelect.selectedOptions).map(opt => opt.value);
      
      if (attributeName && selectedValues.length > 0) {
        selectedAttributes.push({
          name: attributeName,
          values: selectedValues
        });
      }
    });

    // Update preview chips
    selectedAttributesPreview.innerHTML = selectedAttributes.map(attr => `
      <div class="attribute-chip">
        <span>${attr.name}:</span>
        ${attr.values.map(v => `<span class="attribute-value-badge">${v}</span>`).join('')}
      </div>
    `).join('');
  }

  // Generate variants from selected attributes
  document.getElementById('btnGenerateVariants').addEventListener('click', function() {
    const selectedAttributes = [];
    document.querySelectorAll('.attribute-row').forEach(row => {
      const select = row.querySelector('.attribute-select');
      const valuesSelect = row.querySelector('.attribute-values');
      const attributeName = select.options[select.selectedIndex]?.text;
      const selectedValues = Array.from(valuesSelect.selectedOptions).map(opt => opt.value);
      
      if (attributeName && selectedValues.length > 0) {
        selectedAttributes.push({
          name: attributeName,
          values: selectedValues
        });
      }
    });

    if (selectedAttributes.length === 0) {
      alert('Please select at least one attribute with values');
      return;
    }

    // Generate combinations
    const combinations = cartesianProduct(selectedAttributes.map(attr => attr.values));
    
    // Clear existing variants
    variantList.innerHTML = '';
    variantCounter = 0;

    // Create variant rows
    combinations.forEach(combination => {
      const attrJson = {};
      combination.forEach((value, index) => {
        attrJson[selectedAttributes[index].name] = value;
      });

      addVariantRow(JSON.stringify(attrJson, null, 2));
    });
  });

  // Cartesian product helper
  function cartesianProduct(arrays) {
    return arrays.reduce((acc, curr) => {
      return acc.flatMap(a => curr.map(c => [...a, c]));
    }, [[]]);
  }

  // Manual variant addition
  document.getElementById('btnAddVariant').addEventListener('click', function() {
    addVariantRow('');
  });

  function addVariantRow(attributesJson = '') {
    const html = `
      <div class="variant-item rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="flex items-center justify-between mb-3">
          <div class="text-sm font-semibold">Variant #${variantCounter + 1}</div>
          <button type="button" class="remove-variant text-xs font-semibold text-rose-600">Remove</button>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
          <div class="col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Attributes JSON</label>
            <textarea name="variants[${variantCounter}][attributes_json]" rows="2"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800 font-mono"
              placeholder='{"Color": "Red", "Size": "M"}' required>${attributesJson}</textarea>
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU</label>
            <input name="variants[${variantCounter}][sku]" 
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Regular Price</label>
            <input name="variants[${variantCounter}][regular_price]" type="number" step="0.01" min="0"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sale Price</label>
            <input name="variants[${variantCounter}][sale_price]" type="number" step="0.01" min="0"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Stock</label>
            <input name="variants[${variantCounter}][stock]" type="number" min="0" value="0"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Variant Image</label>
            <input type="file" name="variants[${variantCounter}][image]" accept="image/*"
              class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
          </div>
        </div>
      </div>
    `;
    
    variantList.insertAdjacentHTML('beforeend', html);
    variantCounter++;
  }

  // Remove variant
  variantList.addEventListener('click', function(e) {
    if (e.target.closest('.remove-variant')) {
      e.target.closest('.variant-item').remove();
    }
  });

})();
</script>
@endpush
@endsection