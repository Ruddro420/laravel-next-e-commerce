@extends('layouts.app')
@section('title','Stock')
@section('subtitle','Products')
@section('pageTitle','Stock Management')
@section('pageDesc','Monitor and update product stock')

@section('content')
<div class="space-y-6">

  @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- Filters --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <form method="GET" action="{{ route('products.stock') }}" class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
      <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
        <input name="q" value="{{ $q ?? '' }}"
          class="w-full sm:w-72 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm outline-none placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search by name or SKU..." />

        <select name="filter"
          class="w-full sm:w-56 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="" {{ ($filter ?? '')==='' ? 'selected' : '' }}>All</option>
          <option value="low" {{ ($filter ?? '')==='low' ? 'selected' : '' }}>Low Stock (<=5)</option>
          <option value="out" {{ ($filter ?? '')==='out' ? 'selected' : '' }}>Out of Stock</option>
        </select>

        <button class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
          Apply
        </button>

        <a href="{{ route('products.stock') }}"
          class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Reset
        </a>
      </div>

      <div class="text-xs text-slate-500 dark:text-slate-400">
        Quick tip: Use <span class="font-semibold">Stock Details</span> to log IN/OUT adjustments.
      </div>
    </form>
  </div>

  {{-- Table --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-soft dark:bg-slate-900 dark:border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-950/40">
          <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4">SKU</th>
            <th class="py-3 px-4">Type</th>
            <th class="py-3 px-4">Price</th>
            <th class="py-3 px-4">Stock</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($products as $p)
            @php
              $stk = (int)($p->stock ?? 0);
              $isOut = $stk <= 0;
              $isLow = $stk > 0 && $stk <= 5;
              $price = $p->sale_price ?? $p->regular_price;
            @endphp
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-3 px-4">
                <div class="font-semibold">{{ $p->name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $p->id }} • Slug: {{ $p->slug }}</div>
              </td>

              <td class="py-3 px-4">{{ $p->sku ?? '—' }}</td>

              <td class="py-3 px-4">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                  {{ $p->product_type==='simple' ? 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200'
                  : ($p->product_type==='variable' ? 'bg-violet-50 text-violet-700 dark:bg-violet-500/10 dark:text-violet-200'
                  : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200') }}">
                  {{ ucfirst($p->product_type) }}
                </span>
              </td>

              <td class="py-3 px-4">
                {{ $price !== null ? number_format((float)$price, 2) : '—' }}
              </td>

              <td class="py-3 px-4">
                <span class="font-bold {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900 dark:text-white') }}">
                  {{ $stk }}
                </span>
              </td>

              <td class="py-3 px-4">
                @if($isOut)
                  <span class="inline-flex rounded-full bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-200">
                    Out of stock
                  </span>
                @elseif($isLow)
                  <span class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                    Low stock
                  </span>
                @else
                  <span class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                    In stock
                  </span>
                @endif
              </td>

              <td class="py-3 px-4 text-right">
                <a href="{{ route('products.stock.show',$p) }}"
                  class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
                  Stock Details
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-10 text-center text-slate-500 dark:text-slate-400">
                No products found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
      {{ $products->links() }}
    </div>
  </div>

</div>
@endsection
