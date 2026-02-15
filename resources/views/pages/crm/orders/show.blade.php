@extends('layouts.app')
@section('title','Order Details')
@section('subtitle','CRM')
@section('pageTitle','Order Details')
@section('pageDesc',$order->order_number)

@section('content')
<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

  <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-xs text-slate-500 dark:text-slate-400">Order</div>
        <div class="text-xl font-bold">{{ $order->order_number }}</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">{{ $order->created_at->format('Y-m-d H:i') }}</div>
      </div>
      <div class="flex gap-2">
        <a href="{{ route('crm.orders.edit',$order) }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold dark:border-slate-800">Edit</a>
        <a href="{{ route('crm.orders') }}" class="rounded-2xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white">Back</a>
      </div>
    </div>

    <div class="mt-6">
      <div class="font-semibold mb-2">Items</div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
              <th class="py-2 pr-3">Product</th>
              <th class="py-2 pr-3">SKU</th>
              <th class="py-2 pr-3">Qty</th>
              <th class="py-2 pr-3">Price</th>
              <th class="py-2 pr-3">Line Total</th>
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

    @if($order->note)
      <div class="mt-6">
        <div class="font-semibold">Note</div>
        <div class="mt-2 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line">{{ $order->note }}</div>
      </div>
    @endif
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="font-semibold">Customer</div>
    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
      <div><span class="font-semibold">Name:</span> {{ $order->customer?->name ?? '—' }}</div>
      <div><span class="font-semibold">Email:</span> {{ $order->customer?->email ?? '—' }}</div>
      <div><span class="font-semibold">Phone:</span> {{ $order->customer?->phone ?? '—' }}</div>
    </div>

    <div class="mt-5 font-semibold">Order Status</div>
    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">
      <div><span class="font-semibold">Status:</span> {{ $order->status }}</div>
    </div>

    <div class="mt-5 font-semibold">Payment</div>
    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300 space-y-1">
      <div><span class="font-semibold">Method:</span> {{ strtoupper($order->payment?->method ?? '—') }}</div>
      <div><span class="font-semibold">Status:</span> {{ $order->payment?->status ?? '—' }}</div>
      <div><span class="font-semibold">TXN:</span> {{ $order->payment?->transaction_id ?? '—' }}</div>
      <div><span class="font-semibold">Paid:</span> {{ number_format($order->payment?->amount_paid ?? 0,2) }}</div>
      <div><span class="font-semibold">Due:</span> {{ number_format($order->payment?->amount_due ?? 0,2) }}</div>
    </div>

    <div class="mt-5 font-semibold">Coupon & Tax</div>
    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300 space-y-1">
      <div><span class="font-semibold">Coupon:</span> {{ $order->coupon_code ?? '—' }}</div>
      <div><span class="font-semibold">Discount:</span> {{ number_format($order->coupon_discount,2) }}</div>
      <div><span class="font-semibold">Tax:</span> {{ number_format($order->tax_amount,2) }}</div>
    </div>

    <div class="mt-5 font-semibold">Totals</div>
    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300 space-y-1">
      <div class="flex justify-between"><span>Subtotal</span><span>{{ number_format($order->subtotal,2) }}</span></div>
      <div class="flex justify-between"><span>Shipping</span><span>{{ number_format($order->shipping,2) }}</span></div>
      <div class="flex justify-between"><span>Tax</span><span>{{ number_format($order->tax_amount,2) }}</span></div>
      <div class="flex justify-between"><span>Discount</span><span>{{ number_format($order->coupon_discount,2) }}</span></div>
      <div class="flex justify-between font-semibold pt-2 border-t border-slate-100 dark:border-slate-800">
        <span>Total</span><span>{{ number_format($order->total,2) }}</span>
      </div>
    </div>

    <div class="mt-5 font-semibold">Addresses</div>
    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300 space-y-2">
      <div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Billing</div>
        <div class="whitespace-pre-line">{{ $order->billing_address ?? '—' }}</div>
      </div>
      <div>
        <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Shipping</div>
        <div class="whitespace-pre-line">{{ $order->shipping_address ?? '—' }}</div>
      </div>
    </div>
  </div>

</div>
@endsection
