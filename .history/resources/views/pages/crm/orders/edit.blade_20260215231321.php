@extends('layouts.app')
@section('title','Edit Order')
@section('subtitle','CRM')
@section('pageTitle','Edit Order')
@section('pageDesc',$order->order_number)

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  @if(session('success'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('crm.orders.update',$order) }}" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Customer + Status --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Customer</label>
        <select id="customerSelect" name="customer_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select customer</option>
          @foreach($customers as $c)
            <option value="{{ $c->id }}"
              data-billing="{{ e($c->billing_address ?? '') }}"
              data-shipping="{{ e($c->shipping_address ?? '') }}"
              {{ (old('customer_id',$order->customer_id)==$c->id) ? 'selected' : '' }}>
              {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Order Status</label>
        <select name="status"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          @php $st = old('status',$order->status); @endphp
          <option value="processing" {{ $st==='processing'?'selected':'' }}>Processing</option>
          <option value="complete" {{ $st==='complete'?'selected':'' }}>Complete</option>
          <option value="hold" {{ $st==='hold'?'selected':'' }}>Hold</option>
        </select>
      </div>
    </div>

    {{-- Addresses --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Billing Address</label>
        <textarea id="billingAddr" name="billing_address" rows="4"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('billing_address',$order->billing_address) }}</textarea>
      </div>
      <div>
        <label class="text-sm font-semibold">Shipping Address</label>
        <textarea id="shippingAddr" name="shipping_address" rows="4"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('shipping_address',$order->shipping_address) }}</textarea>
      </div>
    </div>

    {{-- Items --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Order Items</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Update qty or price and totals update instantly.</div>
        </div>
        <button type="button" id="btnAddItem"
          class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
          + Add Item
        </button>
      </div>
      <div id="itemsWrap" class="mt-4 space-y-3"></div>
    </div>

    {{-- Coupon + Tax + Shipping --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm font-semibold">Coupon Code</label>
        <input id="couponCode" name="coupon_code" value="{{ old('coupon_code',$order->coupon_code) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="SAVE10 (optional)" />
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Discount is validated on Save.</div>
      </div>

      <div>
        <label class="text-sm font-semibold">Tax</label>
        <select id="taxSelect" name="tax_rate_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">No Tax</option>
          @foreach($taxRates as $t)
            <option value="{{ $t->id }}" data-rate="{{ $t->rate }}" data-mode="{{ $t->mode }}"
              {{ (old('tax_rate_id',$order->tax_rate_id)==$t->id) ? 'selected' : '' }}>
              {{ $t->name }} ({{ $t->rate }}% {{ $t->mode }})
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Shipping</label>
        <input id="shipping" name="shipping" type="number" step="0.01" value="{{ old('shipping',$order->shipping) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
    </div>

    {{-- Payment --}}
    @php
      $pm = old('payment.method', $order->payment?->method ?? 'cod');
      $trx = old('payment.transaction_id', $order->payment?->transaction_id ?? '');
      $paid = old('payment.amount_paid', $order->payment?->amount_paid ?? 0);
    @endphp
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold">Payment</div>
      <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-semibold">Method</label>
          <select id="payMethod" name="payment[method]"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="cod" {{ $pm==='cod'?'selected':'' }}>Cash on Delivery</option>
            <option value="bkash" {{ $pm==='bkash'?'selected':'' }}>bKash</option>
            <option value="nagad" {{ $pm==='nagad'?'selected':'' }}>Nagad</option>
            <option value="rocket" {{ $pm==='rocket'?'selected':'' }}>Rocket</option>
          </select>
        </div>

        <div id="trxWrap" class="hidden">
          <label class="text-sm font-semibold">Transaction ID</label>
          <input id="trxId" name="payment[transaction_id]" value="{{ $trx }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="TXN id" />
        </div>

        <div>
          <label class="text-sm font-semibold">Amount Paid</label>
          <input id="amountPaid" name="payment[amount_paid]" type="number" step="0.01" value="{{ $paid }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
    </div>

    {{-- Summary Preview --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Summary (Preview)</div>
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Subtotal</div>
          <div id="sumSubtotal" class="font-semibold">0.00</div>
        </div>
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Tax</div>
          <div id="sumTax" class="font-semibold">0.00</div>
        </div>
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Total</div>
          <div id="sumTotal" class="font-semibold">0.00</div>
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
      <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
        Coupon discount is recalculated server-side when you update.
      </div>
    </div>

    <div>
      <label class="text-sm font-semibold">Note</label>
      <textarea name="note" rows="3"
        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('note',$order->note) }}</textarea>
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ route('crm.orders.show',$order) }}"
        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        Back
      </a>
      <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Update Order
      </button>
    </div>
  </form>
</div>

<script>
(function(){
  // customer auto-fill if you change it
  const customerSelect = document.getElementById('customerSelect');
  const billing = document.getElementById('billingAddr');
  const shippingA = document.getElementById('shippingAddr');
  customerSelect?.addEventListener('change', ()=>{
    const opt = customerSelect.options[customerSelect.selectedIndex];
    billing.value = opt?.dataset.billing || '';
    shippingA.value = opt?.dataset.shipping || '';
  });

  const wrap = document.getElementById('itemsWrap');
  const btn = document.getElementById('btnAddItem');
  const shipping = document.getElementById('shipping');
  const taxSelect = document.getElementById('taxSelect');
  const paid = document.getElementById('amountPaid');

  const sumSubtotal = document.getElementById('sumSubtotal');
  const sumTax = document.getElementById('sumTax');
  const sumTotal = document.getElementById('sumTotal');
  const sumPaid = document.getElementById('sumPaid');
  const sumDue = document.getElementById('sumDue');

  // Existing items from server
  const existingItems = @json(
    $order->items->map(function($i){
      return [
        'product_name'=>$i->product_name,
        'sku'=>$i->sku,
        'qty'=>$i->qty,
        'price'=>$i->price,
      ];
    })->values()
  );

  let idx = 0;

  function row(i, data = null){
    const name = data?.product_name ?? '';
    const sku = data?.sku ?? '';
    const qty = data?.qty ?? 1;
    const price = data?.price ?? 0;

    return `
      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800" data-item>
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold">Item #${i+1}</div>
          <button type="button" class="text-xs font-semibold text-rose-600" data-remove>Remove</button>
        </div>

        <div class="mt-3 grid grid-cols-1 md:grid-cols-4 gap-3">
          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Product Name</label>
            <input name="items[${i}][product_name]" value="${escapeHtml(name)}" required
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU</label>
            <input name="items[${i}][sku]" value="${escapeHtml(sku)}"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Qty</label>
            <input name="items[${i}][qty]" type="number" min="1" value="${qty}" required data-qty
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Price</label>
            <input name="items[${i}][price]" type="number" step="0.01" min="0" value="${price}" required data-price
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Line Total</label>
            <input type="text" readonly value="0.00" data-line
              class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800 dark:border-slate-700" />
          </div>
        </div>
      </div>
    `;
  }

  function escapeHtml(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function calc(){
    let subtotal = 0;
    wrap.querySelectorAll('[data-item]').forEach(item=>{
      const qty = parseFloat(item.querySelector('[data-qty]').value || 0);
      const price = parseFloat(item.querySelector('[data-price]').value || 0);
      const line = qty * price;
      subtotal += line;
      item.querySelector('[data-line]').value = line.toFixed(2);
    });

    const ship = parseFloat(shipping.value || 0);
    const taxOpt = taxSelect.options[taxSelect.selectedIndex];
    const rate = parseFloat(taxOpt?.dataset.rate || 0);
    const mode = taxOpt?.dataset.mode || 'exclusive';

    const base = subtotal + ship;
    let tax = 0;

    if(rate > 0){
      if(mode === 'exclusive'){
        tax = (base * rate)/100;
      }else{
        const div = 1 + (rate/100);
        tax = base - (base/div);
      }
    }

    const total = (mode === 'inclusive') ? base : (base + tax);
    const paidVal = parseFloat(paid.value || 0);
    const due = Math.max(0, total - paidVal);

    sumSubtotal.textContent = subtotal.toFixed(2);
    sumTax.textContent = tax.toFixed(2);
    sumTotal.textContent = total.toFixed(2);
    sumPaid.textContent = paidVal.toFixed(2);
    sumDue.textContent = due.toFixed(2);
  }

  btn.addEventListener('click', ()=>{
    wrap.insertAdjacentHTML('beforeend', row(idx));
    idx++;
    calc();
  });

  wrap.addEventListener('click', (e)=>{
    const rm = e.target.closest('[data-remove]');
    if(!rm) return;
    rm.closest('[data-item]').remove();
    calc();
  });

  wrap.addEventListener('input', (e)=>{
    if(e.target.matches('[data-qty],[data-price]')) calc();
  });

  [shipping, taxSelect, paid].forEach(el=> el.addEventListener('input', calc));
  taxSelect.addEventListener('change', calc);

  // payment method txn id toggle
  const method = document.getElementById('payMethod');
  const trxWrap = document.getElementById('trxWrap');
  function syncPay(){
    const m = method.value;
    trxWrap.classList.toggle('hidden', !(m === 'bkash' || m === 'nagad' || m === 'rocket'));
  }
  method.addEventListener('change', syncPay);
  syncPay();

  // render existing items
  if(existingItems.length){
    existingItems.forEach(it=>{
      wrap.insertAdjacentHTML('beforeend', row(idx, it));
      idx++;
    });
  } else {
    // safety
    btn.click();
  }

  calc();
})();
</script>
@endsection
