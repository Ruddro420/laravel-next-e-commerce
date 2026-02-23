@extends('layouts.app')
@section('title','Order')
@section('subtitle','CRM')
@section('pageTitle',$order->order_number)
@section('pageDesc','Order details, items, totals and payment.')

@section('content')
<div class="space-y-4">

  @if(session('success'))
  <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
    {{ session('success') }}
  </div>
  @endif

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="text-xs text-slate-500 dark:text-slate-400">Order</div>
        <div class="text-lg font-semibold">{{ $order->order_number }}</div>
        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $order->created_at->format('Y-m-d H:i') }}</div>
      </div>

      <div class="flex gap-2">
        <a href="{{ route('crm.orders.edit',$order) }}"
          class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
          Edit
        </a>
        <a href="{{ route('crm.orders') }}"
          class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Back
        </a>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Customer</div>
        <div class="font-semibold">{{ $order->customer?->name ?? 'Walk-in' }}</div>
        <div class="text-xs text-slate-500 dark:text-slate-400">{{ $order->customer?->phone ?? '—' }}</div>
      </div>

      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Status</div>
        <div class="font-semibold capitalize">{{ $order->status }}</div>
      </div>

      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Payment</div>
        <div class="font-semibold uppercase">{{ $order->payment?->method ?? '—' }}</div>
        <div class="text-xs text-slate-500 dark:text-slate-400">Status: {{ $order->payment?->status ?? '—' }}</div>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-2 pr-3">Product</th>
            <th class="py-2 pr-3">Variant</th>
            <th class="py-2 pr-3">SKU</th>
            <th class="py-2 pr-3">Qty</th>
            <th class="py-2 pr-3">Price</th>
            <th class="py-2 pr-3">Line</th>
          </tr>
        </thead>
        <tbody>
          @foreach($order->items as $it)
          <tr class="border-t border-slate-100 dark:border-slate-800">

            {{-- Product Name --}}
            <td class="py-2 pr-3 font-semibold">
              {{ $it->product?->name ?? $it->product_name }}
            </td>

            {{-- Variant --}}
            <td class="py-2 pr-3 text-slate-600 dark:text-slate-300">
              @if($it->variant)
              @php
              $attrs = is_array($it->variant->attributes)
              ? $it->variant->attributes
              : json_decode($it->variant->attributes, true);
              @endphp
              @if($attrs)
              {{ collect($attrs)->map(fn($v, $k) => "{$k}: {$v}")->join(', ') }}
              @else
              {{ $it->variant->sku ?? '—' }}
              @endif
              @else
              —
              @endif
            </td>

            {{-- SKU --}}
            <td class="py-2 pr-3 text-slate-500 dark:text-slate-400">
              {{ $it->sku ?? $it->variant?->sku ?? '—' }}
            </td>

            {{-- Qty --}}
            <td class="py-2 pr-3">
              {{ $it->qty }}
            </td>

            {{-- Price --}}
            <td class="py-2 pr-3">
              {{ number_format($it->price,2) }}
            </td>

            {{-- Line Total --}}
            <td class="py-2 pr-3 font-semibold">
              {{ number_format($it->line_total,2) }}
            </td>

          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

   @php
  $couponCode = $order->coupon_code ?? $order->coupon?->code ?? null;
  $couponDiscount = (float)($order->coupon_discount ?? 0);
  $discount = (float)($order->discount ?? 0);
  $discountAmount = $couponDiscount > 0 ? $couponDiscount : $discount;

  $paid = (float)($order->payment?->amount_paid ?? 0);
  $due = max(0, (float)$order->total - $paid);
@endphp

    <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Subtotal</div>
        <div class="font-semibold">{{ number_format($order->subtotal,2) }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Tax</div>
        <div class="font-semibold">{{ number_format($order->tax_amount,2) }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Shipping</div>
        <div class="font-semibold">{{ number_format($order->shipping,2) }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Total</div>
        <div class="font-semibold">{{ number_format($order->total,2) }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Paid / Due</div>
        <div class="font-semibold">{{ number_format($paid,2) }} / {{ number_format($due,2) }}</div>
      </div>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Billing Address</div>
        <div class="whitespace-pre-line">{{ $order->billing_address ?? '—' }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Shipping Address</div>
        <div class="whitespace-pre-line">{{ $order->shipping_address ?? '—' }}</div>
      </div>
    </div>

    @if($order->note)
    <div class="mt-4 rounded-xl border border-slate-200 p-3 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">Note</div>
      <div class="whitespace-pre-line">{{ $order->note }}</div>
    </div>
    @endif

  </div>
</div>
@endsection