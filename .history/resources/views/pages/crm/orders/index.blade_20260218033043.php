@extends('layouts.app')
@section('title','Order')
@section('subtitle','CRM')
@section('pageTitle','Order Details')
@section('pageDesc',$order->order_number)

@section('content')
<div class="space-y-4">

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-start justify-between gap-3">
      <div>
        <div class="text-lg font-semibold">{{ $order->order_number }}</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">
          Date: {{ $order->created_at->format('Y-m-d H:i') }}
        </div>
      </div>

      <div class="flex gap-2">
        <a href="{{ route('crm.orders.edit',$order) }}"
          class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Edit</a>

        <a href="{{ route('crm.orders') }}"
          class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">Back</a>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Customer</div>
        <div class="font-semibold">{{ $order->customer?->name ?? 'Walk-in' }}</div>
        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $order->customer?->phone ?? '' }}</div>
      </div>

      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Payment</div>
        <div class="font-semibold uppercase">{{ $order->payment?->method ?? '—' }}</div>
        <div class="text-xs">
          Status:
          <span class="font-semibold {{ ($order->payment?->status==='paid') ? 'text-emerald-600' : 'text-amber-600' }}">
            {{ $order->payment?->status ?? '—' }}
          </span>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Totals</div>
        <div>Subtotal: <span class="font-semibold">{{ number_format($order->subtotal,2) }}</span></div>
        <div>Shipping: <span class="font-semibold">{{ number_format($order->shipping,2) }}</span></div>
        <div>Tax: <span class="font-semibold">{{ number_format($order->tax_amount,2) }}</span></div>
        <div>Total: <span class="font-semibold">{{ number_format($order->total,2) }}</span></div>
        <div>Paid: <span class="font-semibold">{{ number_format($order->payment?->amount_paid ?? 0,2) }}</span></div>
        <div>Due: <span class="font-semibold">{{ number_format($order->payment?->amount_due ?? 0,2) }}</span></div>
      </div>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="font-semibold mb-3">Items</div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase text-slate-500 dark:text-slate-400">
            <th class="py-2 pr-3">Product</th>
            <th class="py-2 pr-3">SKU</th>
            <th class="py-2 pr-3">Qty</th>
            <th class="py-2 pr-3">Price</th>
            <th class="py-2 pr-3">Line</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->items as $it)
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-2 pr-3 font-semibold">{{ $it->product_name }}</td>
              <td class="py-2 pr-3">{{ $it->sku ?? '—' }}</td>
              <td class="py-2 pr-3">{{ $it->qty }}</td>
              <td class="py-2 pr-3">{{ number_format($it->price,2) }}</td>
              <td class="py-2 pr-3 font-semibold">{{ number_format($it->line_total,2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection
