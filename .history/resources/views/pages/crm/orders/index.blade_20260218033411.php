@extends('layouts.app')
@section('title','Orders')
@section('subtitle','CRM')
@section('pageTitle','Orders')
@section('pageDesc','Manage orders, payments and items.')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
  <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="text-lg font-semibold">Orders</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400">Create, update, view and delete orders.</p>
    </div>

    <div class="flex gap-2">
      <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $q ?? '' }}"
          class="w-full sm:w-72 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search order/customer..." />
        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Search
        </button>
      </form>

      <a href="{{ route('crm.orders.create') }}"
        class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Add Order
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
          <th class="py-3 pr-3">Order</th>
          <th class="py-3 pr-3">Customer</th>
          <th class="py-3 pr-3">Status</th>
          <th class="py-3 pr-3">Payment</th>
          <th class="py-3 pr-3">Total</th>
          <th class="py-3 pr-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($orders as $o)
          <tr class="border-t border-slate-100 dark:border-slate-800">
            <td class="py-3 pr-3">
              <div class="font-semibold">{{ $o->order_number }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ $o->created_at->format('Y-m-d') }}</div>
            </td>

            <td class="py-3 pr-3">
              <div class="font-semibold">{{ $o->customer?->name ?? 'Walk-in' }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ $o->customer?->phone ?? '—' }}</div>
            </td>

            <td class="py-3 pr-3 capitalize">
              @php
                $badge = match($o->status){
                  'processing' => 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200',
                  'complete' => 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200',
                  default => 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200',
                };
              @endphp
              <span class="inline-flex rounded-xl border px-2 py-1 text-xs font-semibold {{ $badge }}">{{ $o->status }}</span>
            </td>

            <td class="py-3 pr-3">
              <div class="text-xs font-semibold uppercase">{{ $o->payment?->method ?? '—' }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ $o->payment?->status ?? '—' }}</div>
            </td>

            <td class="py-3 pr-3 font-semibold">{{ number_format($o->total,2) }}</td>

            <td class="py-3 pr-3">
              <div class="flex gap-3">
                <a class="text-xs font-semibold text-indigo-600" href="{{ route('crm.orders.show',$o) }}">View</a>
                <a class="text-xs font-semibold text-slate-700 dark:text-slate-200" href="{{ route('crm.orders.edit',$o) }}">Edit</a>
                <form method="POST" action="{{ route('crm.orders.destroy',$o) }}" data-delete-form class="inline">
                  @csrf @method('DELETE')
                  <button class="text-xs font-semibold text-rose-600" type="submit">Delete</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="py-10 text-center text-slate-500 dark:text-slate-400">No orders found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $orders->links() }}</div>
</div>

<script>
  document.querySelectorAll('[data-delete-form]').forEach(f=>{
    f.addEventListener('submit', (e)=>{
      if(!confirm('Delete this order? This cannot be undone.')) e.preventDefault();
    });
  });
</script>
@endsection
