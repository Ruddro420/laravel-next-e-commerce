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
(function(){
    // Fix 1: Attributes data - properly formatted
    const ATTRS = @json(($attributes ?? collect())->map(function($a) {
        return [
            'id' => $a->id, 
            'name' => $a->name,
            'values' => $a->values->pluck('value') ?? []
        ];
    })->values());
    
    // Fix 2: Existing variants data (for edit page)
    const EXISTING = @json(isset($product) ? $product->variants : []);
    
    console.log('Attributes loaded:', ATTRS); // Debug log
    console.log('Existing variants:', EXISTING); // Debug log

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

    // Set initial content from old input
    const shortDescTextarea = document.getElementById('shortDescTextarea');
    const descTextarea = document.getElementById('descTextarea');
    
    if (shortDescTextarea && shortDescTextarea.value) {
        shortDescEditor.root.innerHTML = shortDescTextarea.value;
    }
    if (descTextarea && descTextarea.value) {
        descEditor.root.innerHTML = descTextarea.value;
    }

    // Update textareas on form submit
    document.querySelector('form')?.addEventListener('submit', function() {
        if (shortDescTextarea) {
            shortDescTextarea.value = shortDescEditor.root.innerHTML;
        }
        if (descTextarea) {
            descTextarea.value = descEditor.root.innerHTML;
        }
    });

    // Slug auto-generation
    const nameInput = document.getElementById('pName');
    const slugInput = document.getElementById('pSlug');
    
    function toSlug(v) { 
        if (!v) return '';
        return v.toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-'); 
    }
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('input', function() {
            if (!slugInput.value.trim()) {
                slugInput.value = toSlug(nameInput.value);
            }
        });
    }

    // Product Type Toggle
    const typeSelect = document.getElementById('pType');
    const simpleBlock = document.getElementById('simpleBlock');
    const downloadBlock = document.getElementById('downloadBlock');
    const variantBlock = document.getElementById('variantBlock');
    const variantList = document.getElementById('variantList');
    const noVariantsMessage = document.getElementById('noVariantsMessage');

    function syncType() {
        if (!typeSelect) return;
        
        const selectedType = typeSelect.value;
        console.log('Selected type:', selectedType);
        
        // Hide all blocks first
        if (simpleBlock) simpleBlock.classList.add('hidden');
        if (downloadBlock) downloadBlock.classList.add('hidden');
        if (variantBlock) variantBlock.classList.add('hidden');
        
        // Show relevant block based on type
        if (selectedType === 'simple') {
            if (simpleBlock) simpleBlock.classList.remove('hidden');
        } else if (selectedType === 'variable') {
            if (variantBlock) variantBlock.classList.remove('hidden');
            // Show no variants message if no variants exist
            if (variantList && noVariantsMessage) {
                if (variantList.children.length === 0) {
                    noVariantsMessage.classList.remove('hidden');
                } else {
                    noVariantsMessage.classList.add('hidden');
                }
            }
        } else if (selectedType === 'downloadable') {
            if (downloadBlock) downloadBlock.classList.remove('hidden');
        }
    }
    
    if (typeSelect) {
        typeSelect.addEventListener('change', syncType);
        syncType();
    }

    // Variant Management
    let variantCounter = 0;
    const btnAddVariant = document.getElementById('btnAddVariant');

    function addVariantRow(existingData = null) {
        if (!variantList) return;
        
        const currentIdx = variantCounter;
        const attributesJson = existingData ? 
            JSON.stringify(existingData.attributes_json, null, 2) : 
            '{"Size": "M", "Color": "Black"}';
        
        const sku = existingData?.sku || '';
        const regularPrice = existingData?.regular_price || '';
        const salePrice = existingData?.sale_price || '';
        const stock = existingData?.stock || 0;

        const variantHtml = `
            <div class="variant-item rounded-2xl border border-slate-200 p-4 dark:border-slate-800" data-variant-idx="${currentIdx}">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold">Variant #${currentIdx + 1}</span>
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
                            name="variants[${currentIdx}][attributes_json]" 
                            rows="2"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800 font-mono"
                            placeholder='{"Color": "Red", "Size": "M"}' 
                            required>${attributesJson.replace(/"/g, '&quot;')}</textarea>
                        <p class="mt-1 text-xs text-slate-400">Enter attributes in JSON format</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU (Optional)</label>
                        <input 
                            type="text"
                            name="variants[${currentIdx}][sku]" 
                            value="${sku}"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
                            placeholder="e.g., PROD-RED-M" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Regular Price</label>
                        <input 
                            type="number"
                            name="variants[${currentIdx}][regular_price]" 
                            value="${regularPrice}"
                            step="0.01" 
                            min="0"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
                            placeholder="0.00" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Sale Price</label>
                        <input 
                            type="number"
                            name="variants[${currentIdx}][sale_price]" 
                            value="${salePrice}"
                            step="0.01" 
                            min="0"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
                            placeholder="0.00" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Stock Quantity</label>
                        <input 
                            type="number"
                            name="variants[${currentIdx}][stock]" 
                            value="${stock}"
                            min="0"
                            class="mt-1 w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Variant Image</label>
                        <input 
                            type="file" 
                            name="variants[${currentIdx}][image]" 
                            accept="image/*"
                            class="mt-1 block w-full text-sm text-slate-600 dark:text-slate-300" />
                        ${existingData?.image ? `<p class="mt-1 text-xs text-slate-500">Current: ${existingData.image}</p>` : ''}
                    </div>
                </div>
            </div>
        `;
        
        variantList.insertAdjacentHTML('beforeend', variantHtml);
        variantCounter++;
        
        // Hide the no variants message
        if (noVariantsMessage) {
            noVariantsMessage.classList.add('hidden');
        }
    }

    // Add variant button click handler
    if (btnAddVariant) {
        btnAddVariant.addEventListener('click', function() {
            addVariantRow();
        });
    }

    // Remove variant handler
    if (variantList) {
        variantList.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.remove-variant');
            if (removeBtn) {
                const variantItem = removeBtn.closest('.variant-item');
                if (variantItem) {
                    variantItem.remove();
                    
                    // Show no variants message if no variants left
                    if (variantList.children.length === 0 && noVariantsMessage) {
                        noVariantsMessage.classList.remove('hidden');
                    }
                }
            }
        });
    }

    // Load existing variants if any (for edit page)
    if (EXISTING && EXISTING.length > 0) {
        EXISTING.forEach(variant => {
            addVariantRow(variant);
        });
    } else if (typeSelect && typeSelect.value === 'variable') {
        // Add initial variant if none exist and type is variable
        addVariantRow();
    }

})();
</script>

@endsection