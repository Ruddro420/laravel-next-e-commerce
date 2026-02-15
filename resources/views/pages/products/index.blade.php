@extends('layouts.app')
@section('title','Products')
@section('subtitle','Products')
@section('pageTitle','All Products')
@section('pageDesc','Manage your product catalog.')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="text-lg font-semibold">Products</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">Create, edit, delete products.</p>
    </div>

    <div class="flex gap-2">
      <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $q ?? '' }}"
          class="w-full sm:w-72 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search name/SKU..." />
        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">Search</button>
      </form>

      <a href="{{ route('products.create') }}"
        class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Add Product
      </a>
    </div>
  </div>

  @if(session('success'))
    <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  <div class="mt-4 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <th class="py-3 pr-3">Product</th>
          <th class="py-3 pr-3">Type</th>
          <th class="py-3 pr-3">Price</th>
          <th class="py-3 pr-3">Stock</th>
          <th class="py-3 pr-3">SKU</th>
          <th class="py-3 pr-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($products as $p)
          <tr class="border-t border-slate-100 dark:border-slate-800">
            <td class="py-3 pr-3">
              <div class="flex items-center gap-3">
                @if($p->featured_image)
                  <img class="h-10 w-10 rounded-xl object-cover border border-slate-200 dark:border-slate-800"
                       src="{{ asset('storage/'.$p->featured_image) }}" alt="">
                @else
                  <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800"></div>
                @endif
                <div>
                  <div class="font-semibold text-slate-800 dark:text-slate-100">{{ $p->name }}</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400">{{ $p->slug }}</div>
                </div>
              </div>
            </td>
            <td class="py-3 pr-3 capitalize">{{ $p->product_type }}</td>
            <td class="py-3 pr-3">
              @if($p->product_type === 'variable')
                <span class="text-slate-500 dark:text-slate-400">Variants</span>
              @else
                <span class="font-semibold">
                  {{ $p->sale_price ? number_format($p->sale_price,2) : number_format($p->regular_price ?? 0,2) }}
                </span>
              @endif
            </td>
            <td class="py-3 pr-3">{{ $p->product_type === 'variable' ? '—' : ($p->stock ?? '—') }}</td>
            <td class="py-3 pr-3">{{ $p->sku ?? '—' }}</td>
            <td class="py-3 pr-3">
              <div class="flex gap-2">
                <a href="{{ route('products.show',$p) }}" class="text-xs font-semibold text-indigo-600">View</a>
                <a href="{{ route('products.edit',$p) }}" class="text-xs font-semibold text-slate-700 dark:text-slate-200">Edit</a>
                <form method="POST" action="{{ route('products.destroy',$p) }}" class="inline" data-delete-form>
                  @csrf @method('DELETE')
                  <button type="submit" class="text-xs font-semibold text-rose-600">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-10 text-center text-slate-500 dark:text-slate-400">No products found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $products->links() }}</div>
</div>

<script>
  // Delete confirm
  document.querySelectorAll('[data-delete-form]').forEach(f=>{
    f.addEventListener('submit', (e)=>{
      if(!confirm('Are you sure you want to delete this product?')) e.preventDefault();
    });
  });
</script>
@endsection
