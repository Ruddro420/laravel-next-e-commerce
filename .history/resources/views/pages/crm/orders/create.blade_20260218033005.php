@extends('layouts.app')
@section('title','Add Order')
@section('subtitle','CRM')
@section('pageTitle','Add Order')
@section('pageDesc','Create order with tax and payment.')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  @if($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('crm.orders.store') }}" class="space-y-6">
    @csrf

    {{-- Customer + Status --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Customer</label>
        <select id="customerSelect" name="customer_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Walk-in</option>
          @foreach($customers as $c)
            <option value="{{ $c->id }}"
              data-billing="{{ e($c->billing_address ?? '') }}"
              data-shipping="{{ e($c->shipping_address ?? '') }}">
              {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Order Status</label>
        <select name="status"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="processing">Processing</option>
          <option value="complete">Complete</option>
          <option value="hold">Hold</option>
        </select>
      </div>
    </div>

    {{-- Addresses --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Billing Address</label>
        <textarea id="billingAddr" name="billing_address" rows="4"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('billing_address') }}</textarea>
      </div>
      <div>
        <label class="text-sm font-semibold">Shipping Address</label>
        <textarea id="shippingAddr" name="shipping_address" rows="4"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('shipping_address') }}</textarea>
      </div>
    </div>

    {{-- Items --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Order Items</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Choose product and qty. Price auto-fills.</div>
        </div>
        <button type="button" id="btnAddItem"
          class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
          + Add Item
        </button>
      </div>

      <div id="itemsWrap" class="mt-4 space-y-3"></div>
    </div>

    {{-- Shipping + Tax --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Shipping</label>
        <input id="shipping" name="shipping" type="number" step="0.01" value="{{ old('shipping',0) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Tax</label>
        <select id="taxSelect" name="tax_rate_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">No Tax</option>
          @foreach($taxRates as $t)
            <option value="{{ $t->id }}" data-rate="{{ $t->rate }}" data-mode="{{ $t->mode }}">
              {{ $t->name }} ({{ $t->rate }}% {{ $t->mode }})
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Payment --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold">Payment</div>

      <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-semibold">Method</label>
          <select id="payMethod" name="payment[method]"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="cod">Cash on Delivery</option>
            <option value="bkash">bKash</option>
            <option value="nagad">Nagad</option>
            <option value="rocket">Rocket</option>
          </select>
        </div>

        <div id="trxWrap" class="hidden">
          <label class="text-sm font-semibold">Transaction ID</label>
          <input id="trxId" name="payment[transaction_id]"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Amount Paid</label>
          <input id="amountPaid" name="payment[amount_paid]" type="number" step="0.01" value="{{ old('payment.amount_paid',0) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
    </div>

    {{-- Summary --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Summary (Live)</div>
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
    </div>

    <div>
      <label class="text-sm font-semibold">Note</label>
      <textarea name="note" rows="3"
        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('note') }}</textarea>
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ route('crm.orders') }}"
        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        Cancel
      </a>
      <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Order
      </button>
    </div>
  </form>
</div>

@php
  $productsJs = $products->map(fn($p) => [
    'id' => $p->id,
    'name' => $p->name,
    'sku' => $p->sku,
    'price' => (float)($p->sale_price ?? $p->regular_price ?? 0),
  ])->values();
@endphp

<script>
document.addEventListener('DOMContentLoaded', () => {
  const products = @json($productsJs);
  const productMap = new Map(products.map(p => [String(p.id), p]));

  const itemsWrap = document.getElementById('itemsWrap');
  const btnAddItem = document.getElementById('btnAddItem');

  const shipping = document.getElementById('shipping');
  const taxSelect = document.getElementById('taxSelect');
  const payMethod = document.getElementById('payMethod');
  const trxWrap = document.getElementById('trxWrap');
  const amountPaid = document.getElementById('amountPaid');

  const sumSubtotal = document.getElementById('sumSubtotal');
  const sumTax = document.getElementById('sumTax');
  const sumTotal = document.getElementById('sumTotal');
  const sumPaid = document.getElementById('sumPaid');
  const sumDue = document.getElementById('sumDue');

  const customerSelect = document.getElementById('customerSelect');
  const billingAddr = document.getElementById('billingAddr');
  const shippingAddr = document.getElementById('shippingAddr');

  customerSelect?.addEventListener('change', () => {
    const opt = customerSelect.options[customerSelect.selectedIndex];
    billingAddr.value = opt?.dataset.billing || '';
    shippingAddr.value = opt?.dataset.shipping || '';
  });

  function escapeHtml(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function productOptionsHtml(selectedId = ''){
    let html = `<option value="">Select product</option>`;
    products.forEach(p => {
      const sel = String(selectedId) === String(p.id) ? 'selected' : '';
      html += `<option value="${p.id}" ${sel}>${escapeHtml(p.name)} ${p.sku ? '— '+escapeHtml(p.sku) : ''}</option>`;
    });
    return html;
  }

  let idx = 0;

  function row(i){
    return `
      <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800" data-item>
        <div class="flex items-center justify-between">
          <div class="text-sm font-semibold">Item #${i+1}</div>
          <button type="button" class="text-xs font-semibold text-rose-600" data-remove>Remove</button>
        </div>

        <div class="mt-3 grid grid-cols-1 md:grid-cols-6 gap-3">
          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Product</label>
            <select name="items[${i}][product_id]" data-product
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
              ${productOptionsHtml()}
            </select>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Product Name</label>
            <input name="items[${i}][product_name]" data-name readonly required
              class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800 dark:border-slate-700" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU</label>
            <input name="items[${i}][sku]" data-sku readonly
              class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800 dark:border-slate-700" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Qty</label>
            <input name="items[${i}][qty]" type="number" min="1" value="1" required data-qty
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Price</label>
            <input name="items[${i}][price]" type="number" step="0.01" min="0" value="0" required data-price
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Line</label>
            <input type="text" readonly value="0.00" data-line
              class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800 dark:border-slate-700" />
          </div>
        </div>
      </div>
    `;
  }

  function syncPay(){
    const m = payMethod.value;
    trxWrap.classList.toggle('hidden', !(m === 'bkash' || m === 'nagad' || m === 'rocket'));
  }

  function fillProduct(itemEl){
    const pid = itemEl.querySelector('[data-product]').value;
    const p = productMap.get(String(pid));
    const name = itemEl.querySelector('[data-name]');
    const sku = itemEl.querySelector('[data-sku]');
    const price = itemEl.querySelector('[data-price]');

    if(!p){
      name.value = '';
      sku.value = '';
      price.value = '0';
      return;
    }

    name.value = p.name || '';
    sku.value = p.sku || '';
    price.value = Number(p.price || 0).toFixed(2);
  }

  function calc(){
    let subtotal = 0;

    itemsWrap.querySelectorAll('[data-item]').forEach(item => {
      const qty = Number(item.querySelector('[data-qty]').value || 0);
      const price = Number(item.querySelector('[data-price]').value || 0);
      const line = qty * price;
      subtotal += line;
      item.querySelector('[data-line]').value = line.toFixed(2);
    });

    const ship = Number(shipping.value || 0);

    const taxOpt = taxSelect.options[taxSelect.selectedIndex];
    const taxRate = Number(taxOpt?.dataset.rate || 0);
    const taxMode = taxOpt?.dataset.mode || 'exclusive';

    let tax = 0;
    if(taxRate > 0 && taxMode === 'exclusive'){
      tax = (subtotal * taxRate) / 100;
    }

    const total = subtotal + ship + tax;
    const paid = Number(amountPaid.value || 0);
    const due = Math.max(0, total - paid);

    sumSubtotal.textContent = subtotal.toFixed(2);
    sumTax.textContent = tax.toFixed(2);
    sumTotal.textContent = total.toFixed(2);
    sumPaid.textContent = paid.toFixed(2);
    sumDue.textContent = due.toFixed(2);
  }

  btnAddItem.addEventListener('click', (e) => {
    e.preventDefault();
    itemsWrap.insertAdjacentHTML('beforeend', row(idx));
    idx++;
    calc();
  });

  itemsWrap.addEventListener('click', (e) => {
    const rm = e.target.closest('[data-remove]');
    if(!rm) return;
    rm.closest('[data-item]').remove();
    calc();
  });

  itemsWrap.addEventListener('change', (e) => {
    const sel = e.target.closest('[data-product]');
    if(sel){
      const itemEl = sel.closest('[data-item]');
      fillProduct(itemEl);
      calc();
    }
  });

  itemsWrap.addEventListener('input', (e) => {
    if(e.target.matches('[data-qty],[data-price]')) calc();
  });

  [shipping, taxSelect, amountPaid].forEach(el => el.addEventListener('input', calc));
  payMethod.addEventListener('change', syncPay);

  syncPay();

  // start with 1 row
  btnAddItem.click();
});
</script>
@endsection
