@extends('layouts.app')
@section('title','Payment')
@section('subtitle','CRM')
@section('pageTitle','Order Payment')
@section('pageDesc',$order->order_number)

@section('content')
<div class="max-w-3xl mx-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  @if(session('success'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  <div class="flex items-start justify-between">
    <div>
      <div class="text-xs text-slate-500 dark:text-slate-400">Order</div>
      <div class="text-xl font-bold">{{ $order->order_number }}</div>
      <div class="text-sm text-slate-500 dark:text-slate-400">Total: {{ number_format($order->total,2) }}</div>
    </div>

    <div class="flex gap-2">
      <a href="{{ route('crm.orders.show',$order) }}" class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold dark:border-slate-800">
        Back
      </a>
    </div>
  </div>

  @php
    $pm = old('method', $order->payment?->method ?? 'cod');
    $trx = old('transaction_id', $order->payment?->transaction_id ?? '');
    $paid = old('amount_paid', $order->payment?->amount_paid ?? 0);
  @endphp

  <form method="POST" action="{{ route('crm.orders.payment.update',$order) }}" class="mt-6 space-y-4">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm font-semibold">Method</label>
        <select id="payMethod" name="method"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="cod" {{ $pm==='cod'?'selected':'' }}>Cash on Delivery</option>
          <option value="bkash" {{ $pm==='bkash'?'selected':'' }}>bKash</option>
          <option value="nagad" {{ $pm==='nagad'?'selected':'' }}>Nagad</option>
          <option value="rocket" {{ $pm==='rocket'?'selected':'' }}>Rocket</option>
        </select>
      </div>

      <div id="trxWrap" class="hidden">
        <label class="text-sm font-semibold">Transaction ID</label>
        <input id="trxId" name="transaction_id" value="{{ $trx }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="TXN id" />
      </div>

      <div>
        <label class="text-sm font-semibold">Amount Paid</label>
        <input id="amountPaid" name="amount_paid" type="number" step="0.01" value="{{ $paid }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Total</div>
        <div id="sumTotal" class="font-semibold">{{ number_format($order->total,2) }}</div>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Paid</div>
        <div id="sumPaid" class="font-semibold">0.00</div>
      </div>
      <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="text-xs text-slate-500 dark:text-slate-400">Due</div>
        <div id="sumDue" class="font-semibold">0.00</div>
      </div>
    </div>

    <div class="flex justify-end gap-2">
      <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Update Payment
      </button>
    </div>
  </form>
</div>

<script>
(function(){
  const method = document.getElementById('payMethod');
  const trxWrap = document.getElementById('trxWrap');
  const paid = document.getElementById('amountPaid');

  const sumPaid = document.getElementById('sumPaid');
  const sumDue = document.getElementById('sumDue');

  const total = parseFloat("{{ (float)$order->total }}");

  function syncPay(){
    const m = method.value;
    trxWrap.classList.toggle('hidden', !(m === 'bkash' || m === 'nagad' || m === 'rocket'));
  }

  function calc(){
    const p = parseFloat(paid.value || 0);
    const due = Math.max(0, total - p);
    sumPaid.textContent = p.toFixed(2);
    sumDue.textContent = due.toFixed(2);
  }

  method.addEventListener('change', syncPay);
  paid.addEventListener('input', calc);

  syncPay();
  calc();
})();
</script>
@endsection
