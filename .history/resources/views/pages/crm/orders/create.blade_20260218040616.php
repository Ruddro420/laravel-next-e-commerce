@extends('layouts.app')
@section('title','Add Order')
@section('subtitle','CRM')
@section('pageTitle','Add Order')
@section('pageDesc','POS style order create with product attributes.')

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
              data-shipping="{{ e($c->shipping_address ?? '') }}"
              {{ old('customer_id') == $c->id ? 'selected' : '' }}>
              {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
            </option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Order Status</label>
        @php $st = old('status','processing'); @endphp
        <select name="status"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
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
        <textarea id="billingAddr" name="billing_address" rows="3"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('billing_address') }}</textarea>
      </div>
      <div>
        <label class="text-sm font-semibold">Shipping Address</label>
        <textarea id="shippingAddr" name="shipping_address" rows="3"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('shipping_address') }}</textarea>
      </div>
    </div>

    {{-- Items --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Order Items (POS)</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Select product → attributes show like POS.</div>
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
            <option value="{{ $t->id }}"
              data-rate="{{ $t->rate }}"
              data-mode="{{ $t->mode }}"
              {{ old('tax_rate_id') == $t->id ? 'selected' : '' }}>
              {{ $t->name }} ({{ $t->rate }}% {{ $t->mode }})
            </option>
          @endforeach
        </select>
      </div>
    </div>

    {{-- Payment --}}
    @php
      $pm = old('payment.method','cod');
      $trx = old('payment.transaction_id','');
      $paidVal = old('payment.amount_paid',0);
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

        <div id="trxWrap" class="{{ in_array($pm,['bkash','nagad','rocket']) ? '' : 'hidden' }}">
          <label class="text-sm font-semibold">Transaction ID</label>
          <input id="trxId" name="payment[transaction_id]" value="{{ $trx }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Amount Paid</label>
          <input id="amountPaid" name="payment[amount_paid]" type="number" step="0.01" value="{{ $paidVal }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
    </div>

    {{-- Summary --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Summary</div>
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
  /**
   * IMPORTANT:
   * Your Product model must provide attributes structure.
   * Example structure (recommended):
   * $product->attributes_json = [
   *    { "name":"Size", "options":[{"value":"S","price":0},{"value":"M","price":10}] },
   *    { "name":"Color", "options":[{"value":"Red","price":0},{"value":"Black","price":0}] }
   * ];
   *
   * If your DB stores as json column: attributes_json
   */
  $productsJs = $products->map(fn($p) => [
    'id' => $p->id,
    'name' => $p->name,
    'sku' => $p->sku,
    'price' => (float)($p->sale_price ?? $p->regular_price ?? $p->price ?? 0),
    'stock' => $p->stock ?? null,
    'attributes' => $p->attributes_json ?? [], // ✅ must be array
  ])->values();

  $oldItems = old('items', []);
@endphp

<script>
document.addEventListener('DOMContentLoaded', () => {
  const products = @json($productsJs);
  const productMap = new Map(products.map(p => [String(p.id), p]));

  const existingItems = @json($oldItems);

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
      const stockTxt = (p.stock === null || typeof p.stock === 'undefined') ? '' : ` (Stock: ${p.stock})`;
      html += `<option value="${p.id}" ${sel}>${escapeHtml(p.name)}${p.sku ? ' — '+escapeHtml(p.sku) : ''}${stockTxt}</option>`;
    });
    return html;
  }

  function renderAttributes(i, attrs = [], selected = {}) {
    if (!Array.isArray(attrs) || attrs.length === 0) return '';

    return `
      <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3" data-attrs>
        ${attrs.map((a, idx) => {
          const key = a.name || ('Attr '+(idx+1));
          const options = Array.isArray(a.options) ? a.options : [];
          const selectedVal = selected?.[key] ?? '';

          return `
            <div>
              <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">${escapeHtml(key)}</label>
              <select name="items[${i}][attributes][${escapeHtml(key)}]" data-attr-select data-attr-name="${escapeHtml(key)}"
                class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                <option value="">Select</option>
                ${options.map(op => {
                  const val = op.value ?? op;
                  const price = op.price ?? 0;
                  const sel = String(selectedVal) === String(val) ? 'selected' : '';
                  return `<option value="${escapeHtml(val)}" data-price="${price}" ${sel}>${escapeHtml(val)}${price ? ' (+৳'+price+')' : ''}</option>`;
                }).join('')}
              </select>
            </div>
          `;
        }).join('')}
      </div>
    `;
  }

  let idx = 0;

  function row(i, data = null){
    const pid = data?.product_id ?? '';
    const qty = data?.qty ?? 1;
    const price = data?.price ?? 0;
    const name = data?.product_name ?? '';
    const sku = data?.sku ?? '';
    const selectedAttrs = data?.attributes ?? {};

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
              ${productOptionsHtml(pid)}
            </select>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400" data-stocklabel>Stock: —</div>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Product Name</label>
            <input name="items[${i}][product_name]" data-name required
              value="${escapeHtml(name)}"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU</label>
            <input name="items[${i}][sku]" data-sku
              value="${escapeHtml(sku)}"
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
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Line</label>
            <input type="text" readonly value="0.00" data-line
              class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800 dark:border-slate-700" />
          </div>
        </div>

        <div class="mt-2" data-attrs-wrap>
          {{-- attributes render here --}}
        </div>

        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
          POS Tip: Attributes can change price automatically.
        </div>
      </div>
    `;
  }

  function syncPay(){
    const m = payMethod.value;
    trxWrap.classList.toggle('hidden', !(m === 'bkash' || m === 'nagad' || m === 'rocket'));
  }

  function fillFromProduct(itemEl){
    const pid = itemEl.querySelector('[data-product]').value;
    const p = productMap.get(String(pid));

    const nameEl = itemEl.querySelector('[data-name]');
    const skuEl = itemEl.querySelector('[data-sku]');
    const priceEl = itemEl.querySelector('[data-price]');
    const stockLabel = itemEl.querySelector('[data-stocklabel]');
    const attrsWrap = itemEl.querySelector('[data-attrs-wrap]');

    if(!p){
      stockLabel.textContent = 'Stock: —';
      attrsWrap.innerHTML = '';
      return;
    }

    // fill base
    nameEl.value = p.name || '';
    skuEl.value = p.sku || '';
    priceEl.value = Number(p.price || 0).toFixed(2);

    const stockTxt = (p.stock === null || typeof p.stock === 'undefined') ? '∞' : p.stock;
    stockLabel.textContent = `Stock: ${stockTxt}`;

    // render attributes UI
    const i = itemEl.dataset.index;
    const html = renderAttributes(i, p.attributes || [], {});
    attrsWrap.innerHTML = html;

    // after rendering attrs, recalc price based on attr prices
    applyAttributePrice(itemEl);
  }

  function applyAttributePrice(itemEl){
    const priceEl = itemEl.querySelector('[data-price]');
    const pid = itemEl.querySelector('[data-product]').value;
    const p = productMap.get(String(pid));
    if(!p) return;

    let base = Number(p.price || 0);
    let add = 0;

    itemEl.querySelectorAll('[data-attr-select]').forEach(sel => {
      const opt = sel.options[sel.selectedIndex];
      const extra = Number(opt?.dataset.price || 0);
      add += extra;
    });

    priceEl.value = (base + add).toFixed(2);
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
    const rate = Number(taxOpt?.dataset.rate || 0);
    const mode = taxOpt?.dataset.mode || 'exclusive';

    const base = subtotal + ship;
    let tax = 0;

    if(rate > 0){
      if(mode === 'exclusive'){
        tax = (base * rate) / 100;
      } else {
        const div = 1 + (rate/100);
        tax = base - (base/div);
      }
    }

    const total = (mode === 'inclusive') ? base : (base + tax);

    const paid = Number(amountPaid.value || 0);
    const due = Math.max(0, total - paid);

    sumSubtotal.textContent = subtotal.toFixed(2);
    sumTax.textContent = tax.toFixed(2);
    sumTotal.textContent = total.toFixed(2);
    sumPaid.textContent = paid.toFixed(2);
    sumDue.textContent = due.toFixed(2);
  }

  function addRow(data=null){
    itemsWrap.insertAdjacentHTML('beforeend', row(idx, data));
    const itemEl = itemsWrap.lastElementChild;

    itemEl.dataset.index = idx; // ✅ store index for attribute render
    idx++;

    // If already has product_id, load product + attributes
    const pid = itemEl.querySelector('[data-product]')?.value;
    if(pid){
      fillFromProduct(itemEl);
    }

    calc();
  }

  btnAddItem.addEventListener('click', (e) => {
    e.preventDefault();
    addRow(null);
  });

  itemsWrap.addEventListener('click', (e) => {
    const rm = e.target.closest('[data-remove]');
    if(!rm) return;
    rm.closest('[data-item]').remove();
    calc();
  });

  // product changed
  itemsWrap.addEventListener('change', (e) => {
    if(e.target.matches('[data-product]')){
      const itemEl = e.target.closest('[data-item]');
      fillFromProduct(itemEl);
      calc();
      return;
    }

    // attribute changed -> update price
    if(e.target.matches('[data-attr-select]')){
      const itemEl = e.target.closest('[data-item]');
      applyAttributePrice(itemEl);
      calc();
    }
  });

  itemsWrap.addEventListener('input', (e) => {
    if(e.target.matches('[data-qty],[data-price]')) calc();
  });

  [shipping, taxSelect, amountPaid].forEach(el => el?.addEventListener('input', calc));
  taxSelect?.addEventListener('change', calc);
  payMethod?.addEventListener('change', syncPay);

  syncPay();

  // initial rows
  if(Array.isArray(existingItems) && existingItems.length){
    existingItems.forEach(it => addRow(it));
  } else {
    addRow(null);
  }

  calc();
});
</script>
@endsection
