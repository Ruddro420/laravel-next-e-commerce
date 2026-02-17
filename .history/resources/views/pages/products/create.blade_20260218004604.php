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
    
    /* Hide elements */
    .hidden {
        display: none !important;
    }

    /* Variant row styling */
    .variant-item {
        @apply relative;
    }
    .variant-item:hover {
        @apply border-indigo-200 dark:border-indigo-800;
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
          <option value="simple" {{ old('product_type') == 'simple' ? 'selected' : '' }}>Simple Product</option>
          <option value="variable" {{ old('product_type') == 'variable' ? 'selected' : '' }}>Variable Product</option>
          <option value="downloadable" {{ old('product_type') == 'downloadable' ? 'selected' : '' }}>Downloadable Product</option>
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">SKU <span class="text-xs text-slate-500">(Stock Keeping Unit)</span></label>
        <input name="sku" value="{{ old('sku') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    {{-- Rich Text Editors --}}
    <div class="grid grid-cols-1 gap-4">
      <div>
        <label class="text-sm font-semibold">Short Description</label>
        <div id="shortDescEditorContainer">
          <div class="quill-editor" id="shortDescEditor">{!! old('short_description') !!}</div>
        </div>
        <textarea name="short_description" id="shortDescTextarea" class="hidden">{{ old('short_description') }}</textarea>
      </div>
      
      <div>
        <label class="text-sm font-semibold">Description</label>
        <div id="descEditorContainer">
          <div class="quill-editor" id="descEditor">{!! old('description') !!}</div>
        </div>
        <textarea name="description" id="descTextarea" class="hidden">{{ old('description') }}</textarea>
      </div>
    </div>

    {{-- Category / Brand / Shipping --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

      <div>
        <label class="text-sm font-semibold">Shipping Price</label>
        <input name="shipping_price" value="{{ old('shipping_price') }}" type="number" step="0.01" min="0"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    {{-- Simple Product Block --}}
    <div id="simpleBlock" class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <h3 class="text-lg font-semibold mb-4">Pricing & Stock</h3>
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
          <label class="text-sm font-semibold">Stock Quantity</label>
          <input name="stock" value="{{ old('stock', 0) }}" type="number" min="0"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
    </div>

    {{-- Downloadable Product Block --}}
    <div id="downloadBlock" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <h3 class="text-lg font-semibold mb-4">Downloadable Product</h3>
      <div class="space-y-4">
        <div>
          <label class="text-sm font-semibold">Download File (PDF/ZIP/DOC)</label>
          <input type="file" name="download_file" accept=".pdf,.zip,.doc,.docx"
            class="mt-2 block w-full text-sm text-slate-600 dark:text-slate-300" />
          <p class="mt-1 text-xs text-slate-500">Max file size: 10MB. Allowed: PDF, ZIP, DOC, DOCX</p>
        </div>
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
        </div>
      </div>
    </div>

    {{-- Variable Product Block with Variants --}}
    <div id="variantBlock" class="hidden rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-lg font-semibold">Product Variants</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400">Create variants by combining attributes</p>
        </div>
        <div class="flex gap-2">
          <button type="button" id="btnAddVariant"
            class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100">
            + Add Variant Manually
          </button>
        </div>
      </div>

      {{-- Variants List --}}
      <div id="variantList" class="space-y-4">
        <!-- Variants will be added here -->
      </div>

      {{-- No variants message --}}
      <div id="noVariantsMessage" class="text-center py-8 text-slate-500 dark:text-slate-400">
        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <p class="mt-2">No variants added yet. Click the button above to add your first variant.</p>
      </div>
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
      <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
      <label for="is_active" class="text-sm font-medium">Active (visible in store)</label>
    </div>

    <div class="flex items-center justify-end gap-2">
      <a href="{{ route('products.index') }}"
        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        Cancel
      </a>
      <button type="submit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Product
      </button>
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
<script>
(function(){
    // Initialize Quill editors
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
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean'],
                ['link', 'image']
            ]
        }
    });

    // Set initial content from old input
    const shortDescTextarea = document.getElementById('shortDescTextarea');
    const descTextarea = document.getElementById('descTextarea');
    
    if (shortDescTextarea.value) {
        shortDescEditor.root.innerHTML = shortDescTextarea.value;
    }
    if (descTextarea.value) {
        descEditor.root.innerHTML = descTextarea.value;
    }

    // Update textareas on form submit
    document.querySelector('form').addEventListener('submit', function() {
        shortDescTextarea.value = shortDescEditor.root.innerHTML;
        descTextarea.value = descEditor.root.innerHTML;
    });

    // Slug auto-generation
    const nameInput = document.getElementById('pName');
    const slugInput = document.getElementById('pSlug');
    
    function toSlug(v) { 
        return v.toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-'); 
    }
    
    if (nameInput) {
        nameInput.addEventListener('input', function() {
            if (!slugInput.value.trim()) {
                slugInput.value = toSlug(nameInput.value);
            }
        });
    }

    // Product Type Toggle - CRITICAL FIX
    const typeSelect = document.getElementById('pType');
    const simpleBlock = document.getElementById('simpleBlock');
    const downloadBlock = document.getElementById('downloadBlock');
    const variantBlock = document.getElementById('variantBlock');
    const variantList = document.getElementById('variantList');
    const noVariantsMessage = document.getElementById('noVariantsMessage');

    function syncType() {
        const selectedType = typeSelect.value;
        console.log('Selected type:', selectedType); // Debug log
        
        // Hide all blocks first
        simpleBlock.classList.add('hidden');
        downloadBlock.classList.add('hidden');
        variantBlock.classList.add('hidden');
        
        // Show relevant block based on type
        if (selectedType === 'simple') {
            simpleBlock.classList.remove('hidden');
        } else if (selectedType === 'variable') {
            variantBlock.classList.remove('hidden');
            // Show no variants message if no variants exist
            if (variantList.children.length === 0) {
                noVariantsMessage.classList.remove('hidden');
            } else {
                noVariantsMessage.classList.add('hidden');
            }
        } else if (selectedType === 'downloadable') {
            downloadBlock.classList.remove('hidden');
        }
    }
    
    // Add event listener and call immediately
    typeSelect.addEventListener('change', syncType);
    syncType(); // Call on page load

    // Variant Management
    let variantCounter = 0;
    const btnAddVariant = document.getElementById('btnAddVariant');

    function addVariantRow() {
        const variantHtml = `
            <div class="variant-item rounded-2xl border border-slate-200 p-4 dark:border-slate-800" data-variant-idx="${variantCounter}">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold">Variant #${variantCounter + 1}</span>
                        <span class="text-xs text-slate-500">(Fill in the details below)</span>
                    </div>
                    <button type="button" class="remove-variant text-xs font-semibold text-rose-600 hover:text-rose-700">
                        <svg class="h-5 w-5 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Remove
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div class="col-span-2">
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Attributes (JSON format)</label>
                        <textarea 
                            name="variants[${variantCounter}][attributes_json]" 
                            rows="2"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800 font-mono"
                            placeholder='{"Color": "Red", "Size": "M", "Material": "Cotton"}' 
                            required>{"Size": "M", "Color": "Black"}</textarea>
                        <p class="mt-1 text-xs text-slate-400">Enter attributes in JSON format</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU (Optional)</label>
                        <input 
                            type="text"
                            name="variants[${variantCounter}][sku]" 
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
                            placeholder="e.g., PROD-RED-M" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Regular Price</label>
                        <input 
                            type="number"
                            name="variants[${variantCounter}][regular_price]" 
                            step="0.01" 
                            min="0"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
                            placeholder="0.00" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sale Price</label>
                        <input 
                            type="number"
                            name="variants[${variantCounter}][sale_price]" 
                            step="0.01" 
                            min="0"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
                            placeholder="0.00" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Stock Quantity</label>
                        <input 
                            type="number"
                            name="variants[${variantCounter}][stock]" 
                            min="0" 
                            value="0"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Variant Image</label>
                        <input 
                            type="file" 
                            name="variants[${variantCounter}][image]" 
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-300" />
                    </div>
                </div>
            </div>
        `;
        
        variantList.insertAdjacentHTML('beforeend', variantHtml);
        variantCounter++;
        
        // Hide the no variants message
        noVariantsMessage.classList.add('hidden');
    }

    // Add variant button click handler
    btnAddVariant.addEventListener('click', function() {
        addVariantRow();
    });

    // Remove variant handler
    variantList.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-variant');
        if (removeBtn) {
            const variantItem = removeBtn.closest('.variant-item');
            if (variantItem) {
                variantItem.remove();
                
                // Show no variants message if no variants left
                if (variantList.children.length === 0) {
                    noVariantsMessage.classList.remove('hidden');
                }
            }
        }
    });

    // Add initial variant if none exist and type is variable
    if (typeSelect.value === 'variable') {
        addVariantRow();
    }

})();
</script>
@endpush