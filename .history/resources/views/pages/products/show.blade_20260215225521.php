@extends('layouts.app')
@section('title',$product->name)
@section('subtitle','Products')
@section('pageTitle','Product Details')
@section('pageDesc',$product->slug)

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

  <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-start justify-between">
      <div>
        <h2 class="text-xl font-bold">{{ $product->name }}</h2>
        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $product->product_type }}</div>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('products.edit',$product) }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold dark:border-slate-800">Edit</a>
        <a href="{{ route('products.index') }}" class="rounded-2xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">Back</a>
      </div>
    </div>

    <div class="mt-4 text-sm text-slate-700 dark:text-slate-200">
      <div class="font-semibold mb-1">Short Description</div>
      <div class="text-slate-600 dark:text-slate-300">{{ $product->short_description ?: '—' }}</div>
    </div>

    <div class="mt-4 text-sm text-slate-700 dark:text-slate-200">
      <div class="font-semibold mb-1">Description</div>
      <div class="text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $product->description ?: '—' }}</div>
    </div>

    @if($product->product_type === 'variable')
      <div class="mt-6">
        <div class="font-semibold mb-2">Variants</div>
        <div class="space-y-3">
          @foreach($product->variants as $v)
            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
              <div class="flex items-center gap-3">
                @if($v->image_path)
                  <img class="h-10 w-10 rounded-xl object-cover border border-slate-200 dark:border-slate-800" src="{{ asset('storage/'.$v->image_path) }}" alt="">
                @else
                  <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800"></div>
                @endif
                <div class="text-sm">
                  <div class="font-semibold">{{ json_encode($v->attributes) }}</div>
                  <div class="text-slate-500 dark:text-slate-400">
                    Price: {{ $v->sale_price ?? $v->regular_price ?? '—' }} | Stock: {{ $v->stock ?? '—' }} | SKU: {{ $v->sku ?? '—' }}
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="font-semibold">Quick Info</div>

    <div class="mt-3 space-y-2 text-sm text-slate-600 dark:text-slate-300">
      <div><span class="font-semibold">SKU:</span> {{ $product->sku ?? '—' }}</div>
      <div><span class="font-semibold">Barcode:</span> {{ $product->barcode ?? '—' }}</div>
      <div><span class="font-semibold">Stock:</span> {{ $product->stock ?? '—' }}</div>
      <div><span class="font-semibold">Regular:</span> {{ $product->regular_price ?? '—' }}</div>
      <div><span class="font-semibold">Sale:</span> {{ $product->sale_price ?? '—' }}</div>
      <div><span class="font-semibold">Shipping:</span> {{ $product->shipping_price ?? '—' }}</div>
    </div>

    <div class="mt-5">
      <div class="font-semibold mb-2">Featured Image</div>
      @if($product->featured_image)
        <img class="w-full rounded-2xl border border-slate-200 dark:border-slate-800" src="{{ asset('storage/'.$product->featured_image) }}" alt="">
      @else
        <div class="h-40 rounded-2xl bg-slate-100 dark:bg-slate-800"></div>
      @endif
    </div>

    <div class="mt-5">
      <div class="font-semibold mb-2">Gallery</div>
      <div class="grid grid-cols-3 gap-2">
        @foreach($product->gallery as $g)
          <img class="h-20 w-full rounded-xl object-cover border border-slate-200 dark:border-slate-800" src="{{ asset('storage/'.$g->image_path) }}" alt="">
        @endforeach
      </div>
    </div>

    @if($product->product_type === 'downloadable' && $product->download_file)
      <div class="mt-5">
        <div class="font-semibold mb-2">Download</div>
        <a class="text-indigo-600 text-sm font-semibold" href="{{ asset('storage/'.$product->download_file) }}" target="_blank">
          Download File
        </a>
      </div>
    @endif
  </div>

</div>
@endsection
