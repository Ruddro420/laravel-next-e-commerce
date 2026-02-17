@extends('layouts.app')
@section('title',$product->name)
@section('subtitle','Products')
@section('pageTitle','Product Details')
@section('pageDesc',$product->slug)

@php
  // Group selected attribute values by attribute (from pivot)
  // Requires: Product->attributeValues() belongsToMany withPivot('attribute_id')
  $selectedAttrs = collect($product->attributeValues ?? [])
      ->groupBy(fn($av) => $av->pivot->attribute_id ?? null);

  // Build map attribute_id -> attribute_name
  $attrNameMap = collect($product->attributeValues ?? [])
      ->mapWithKeys(function($av){
          $aid = $av->pivot->attribute_id ?? null;
          $name = $av->attribute->name ?? 'Attribute';
          return [$aid => $name];
      });

  // Helper to print variant attributes like "Size: M, Color: Red"
  $formatVariantAttrs = function($attrs){
      if (!is_array($attrs)) return '—';
      return collect($attrs)->map(function($val, $key){
          return $key . ': ' . $val;
      })->implode(', ');
  };
@endphp

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

  {{-- MAIN --}}
  <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h2 class="text-xl font-bold">{{ $product->name }}</h2>
        <div class="text-sm text-slate-500 dark:text-slate-400">
          Type: <span class="font-semibold">{{ ucfirst($product->product_type) }}</span>
          <span class="mx-2">•</span>
          Slug: <span class="font-semibold">{{ $product->slug }}</span>
        </div>
      </div>

      <div class="flex gap-2">
        <a href="{{ route('products.edit',$product) }}"
           class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
          Edit
        </a>
        <a href="{{ route('products.index') }}"
           class="rounded-2xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
          Back
        </a>
      </div>
    </div>

    {{-- Descriptions --}}
    <div class="mt-6 text-sm text-slate-700 dark:text-slate-200">
      <div class="font-semibold mb-1">Short Description</div>
      <div class="text-slate-600 dark:text-slate-300">
        {!! $product->short_description ?: '—' !!}
      </div>
    </div>

    <div class="mt-4 text-sm text-slate-700 dark:text-slate-200">
      <div class="font-semibold mb-1">Description</div>
      <div class="text-slate-600 dark:text-slate-300 whitespace-pre-line">
        {!! $product->description ?: '—' !!}
      </div>
    </div>

    {{-- ✅ VARIABLE: Selected Attributes (from pivot) --}}
    @if($product->product_type === 'variable')
      <div class="mt-8">
        <div class="font-semibold mb-2">Selected Attributes</div>

        @if($selectedAttrs->count())
          <div class="space-y-3">
            @foreach($selectedAttrs as $aid => $values)
              <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                <div class="text-sm font-semibold">
                  {{ $attrNameMap[$aid] ?? 'Attribute' }}
                </div>

                <div class="mt-2 flex flex-wrap gap-2">
                  @foreach($values as $v)
                    <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200">
                      {{ $v->value ?? '—' }}
                    </span>
                  @endforeach
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-sm text-slate-500 dark:text-slate-400">— No attributes saved for this product</div>
        @endif
      </div>

      {{-- ✅ VARIABLE: Variants (clear view) --}}
      <div class="mt-8">
        <div class="font-semibold mb-2">Variants</div>

        @if($product->variants && $product->variants->count())
          <div class="space-y-3">
            @foreach($product->variants as $v)
              <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
                <div class="flex items-start gap-3">

                  @if($v->image_path)
                    <img class="h-14 w-14 rounded-2xl object-cover border border-slate-200 dark:border-slate-800"
                         src="{{ asset('storage/'.$v->image_path) }}" alt="">
                  @else
                    <div class="h-14 w-14 rounded-2xl bg-slate-100 dark:bg-slate-800"></div>
                  @endif

                  <div class="flex-1">
                    <div class="text-sm font-semibold">
                      {{ $formatVariantAttrs($v->attributes) }}
                    </div>

                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400 flex flex-wrap gap-x-4 gap-y-1">
                      <div><span class="font-semibold">Regular:</span> {{ $v->regular_price ?? '—' }}</div>
                      <div><span class="font-semibold">Sale:</span> {{ $v->sale_price ?? '—' }}</div>
                      <div><span class="font-semibold">Stock:</span> {{ $v->stock ?? '—' }}</div>
                      <div><span class="font-semibold">SKU:</span> {{ $v->sku ?? '—' }}</div>
                    </div>
                  </div>

                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-sm text-slate-500 dark:text-slate-400">— No variants found</div>
        @endif
      </div>
    @endif
  </div>

  {{-- SIDE --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="font-semibold">Quick Info</div>

    <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
      <div><span class="font-semibold">SKU:</span> {{ $product->sku ?? '—' }}</div>
      <div><span class="font-semibold">Barcode:</span> {{ $product->barcode ?? '—' }}</div>

      <div><span class="font-semibold">Category:</span> {{ $product->category->name ?? '—' }}</div>
      <div><span class="font-semibold">Brand:</span> {{ $product->brand->name ?? '—' }}</div>

      <div><span class="font-semibold">Stock:</span> {{ $product->stock ?? '—' }}</div>

      <div><span class="font-semibold">Regular:</span> {{ $product->regular_price ?? '—' }}</div>
      <div><span class="font-semibold">Sale:</span> {{ $product->sale_price ?? '—' }}</div>

      <div><span class="font-semibold">Shipping:</span> {{ $product->shipping_price ?? '—' }}</div>
      <div><span class="font-semibold">Status:</span> {{ $product->is_active ? 'Active' : 'Inactive' }}</div>
    </div>

    <div class="mt-6">
      <div class="font-semibold mb-2">Featured Image</div>
      @if($product->featured_image)
        <img class="w-full rounded-2xl border border-slate-200 dark:border-slate-800"
             src="{{ asset('storage/'.$product->featured_image) }}" alt="">
      @else
        <div class="h-40 rounded-2xl bg-slate-100 dark:bg-slate-800"></div>
      @endif
    </div>

    <div class="mt-6">
      <div class="font-semibold mb-2">Gallery</div>
      @if($product->gallery && $product->gallery->count())
        <div class="grid grid-cols-3 gap-2">
          @foreach($product->gallery as $g)
            <img class="h-20 w-full rounded-xl object-cover border border-slate-200 dark:border-slate-800"
                 src="{{ asset('storage/'.$g->image_path) }}" alt="">
          @endforeach
        </div>
      @else
        <div class="text-sm text-slate-500 dark:text-slate-400">— No gallery images</div>
      @endif
    </div>

    @if($product->product_type === 'downloadable' && $product->download_file)
      <div class="mt-6">
        <div class="font-semibold mb-2">Download</div>
        <a class="text-indigo-600 text-sm font-semibold"
           href="{{ asset('storage/'.$product->download_file) }}" target="_blank">
          Download File
        </a>
      </div>
    @endif
  </div>

</div>
@endsection
