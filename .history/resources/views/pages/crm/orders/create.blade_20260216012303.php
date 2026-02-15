@extends('layouts.app')
@section('title','Add Order')
@section('subtitle','CRM')
@section('pageTitle','Add Order')
@section('pageDesc','Create order with coupon, tax and payment method.')

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

    {{-- Customer --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="text-sm font-semibold">Customer</label>
        <select id="customerSelect" name="customer_id"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="">Select customer</option>
          @foreach($customers as $c)
            <option value="{{ $c->id }}"
              data-billing="{{ e($c->billing_address ?? '') }}"
              data-shipping="{{ e($c->shipping_address ?? '') }}">
              {{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}
            </option>
          @endforeach
        </select>
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Customer addresses auto-fill below.</div>
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
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
      </div>
      <div>
        <label class="text-sm font-semibold">Shipping Address</label>
        <textarea id="shippingAddr" name="shipping_address" rows="4"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
      </div>
    </div>

    {{-- Items --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Order Items</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Pick product, qty and price. Stock updates automatically on save.</div>
        </div>
        <button type="button" id="btnAddItem"
          class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
          + Add Item
        </button>
      </div>

      <div id="itemsWrap" class="mt-4 space-y-3"></div>

      <div id="stockError" class="hidden mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"></div>
    </div>

    {{-- Coupon + Tax + Shipping --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm font-semibold">Coupon Code</label>
        <input id="couponCode" name="coupon_code"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="SAVE10 (optional)" />
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Coupon validated on Save.</div>
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

      <div>
        <label class="text-sm font-semibold">Shipping</label>
        <input id="shipping" name="shipping" type="number" step="0.01" value="0"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
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
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="bKash/Nagad/Rocket TXN" />
        </div>

        <div>
          <label class="text-sm font-semibold">Amount Paid</label>
          <input id="amountPaid" name="payment[amount_paid]" type="number" step="0.01" value="0"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
      <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">COD usually paid = 0, due = total.</div>
    </div>

    {{-- Summary --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-2">Summary (Preview)</div>
      <div class="grid grid-cols-1 md:grid-cols-5 gap-3 text-sm">
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Subtotal</div>
          <div id="sumSubtotal" class="font-semibold">0.00</div>
        </div>
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Discount</div>
          <div class="font-semibold">Calculated on Save</div>
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
          <div class="text-xs text-slate-500 dark:text-slate-400">Due</div>
          <div id="sumDue" class="font-semibold">0.00</div>
        </div>
      </div>
      <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
        Coupon discount is applied server-side to prevent fake discounts.
      </div>
    </div>

    <div>
      <label class="text-sm font-semibold">Note</label>
      <textarea name="note" rows="3"
        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
    </div>

    <div class="flex justify-end gap-2">
      <a href="{{ route('crm.orders') }}"
        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        Cancel
      </a>
      <button id="btnSubmit" class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Order
      </button>
    </div>
  </form>
</div>

<script>
(function(){
  // Products dataset from server
  const products = @json(
    $products->map(function($p){
      return [
        'id' => $p->id,
        'name' => $p->name,
        'sku' => $p->sku,
        'price' => (float)($p->sale_price ?? $p->regular_price ?? 0),
        'stock' => (int)($p->stock ?? 0),
      ];
    })->values()
  );
)();

  const productMap = new Map(products.map(p => [String(p.id), p]));

  // customer auto-fill
  const customerSelect = document.getElementById('customerSelect');
  const billing = document.getElementById('billingAddr');
  const shippingA = document.getElementById('shippingAddr');
  customerSelect?.addEventListener('change', ()=>{
    const opt = customerSelect.options[customerSelect.selectedIndex];
    billing.value = opt?.dataset.billing || '';
    shippingA.value = opt?.dataset.shipping || '';
  });

  // items
  const wrap = document.getElementById('itemsWrap');
  const btn = document.getElementById('btnAddItem');
  const stockError = document.getElementById('stockError');
  const btnSubmit = document.getElementById('btnSubmit');
  let idx = 0;

  const shipping = document.getElementById('shipping');
  const taxSelect = document.getElementById('taxSelect');
  const paid = document.getElementById('amountPaid');

  const sumSubtotal = document.getElementById('sumSubtotal');
  const sumTax = document.getElementById('sumTax');
  const sumTotal = document.getElementById('sumTotal');
  const sumDue = document.getElementById('sumDue');

  function escapeHtml(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function productOptionsHtml(){
    let html = `<option value="">Select product</option>`;
    products.forEach(p=>{
      const label = `${p.name}${p.sku ? ' — '+p.sku : ''} (Stock: ${p.stock})`;
      html += `<option value="${p.id}" data-stock="${p.stock}">${escapeHtml(label)}</option>`;
    });
    return html;
  }

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
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400" data-stocklabel>Stock: —</div>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Product Name (auto)</label>
            <input name="items[${i}][product_name]" data-name required readonly
              class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800 dark:border-slate-700" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU (auto)</label>
            <input name="items[${i}][sku]" data-sku readonly
              class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm dark:bg-slate-800 dark:border-slate-700" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Qty</label>
            <input name="items[${i}][qty]" type="number" min="1" value="1" required data-qty
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Price (auto)</label>
            <input name="items[${i}][price]" type="number" step="0.01" min="0" value="0" required data-price
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

  function showStockError(msg){
    if(!msg){
      stockError.classList.add('hidden');
      stockError.textContent = '';
      btnSubmit.disabled = false;
      btnSubmit.classList.remove('opacity-60','cursor-not-allowed');
      return;
    }
    stockError.classList.remove('hidden');
    stockError.textContent = msg;
    btnSubmit.disabled = true;
    btnSubmit.classList.add('opacity-60','cursor-not-allowed');
  }

  function validateStockAll(){
    let error = '';

    wrap.querySelectorAll('[data-item]').forEach(item=>{
      const pid = item.querySelector('[data-product]')?.value || '';
      const qty = parseInt(item.querySelector('[data-qty]')?.value || '0', 10);

      if(!pid) return;

      const p = productMap.get(String(pid));
      if(!p) return;

      if(qty > p.stock){
        error = `Not enough stock for "${p.name}". Available: ${p.stock}, Requested: ${qty}.`;
      }
    });

    showStockError(error);
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
    sumDue.textContent = due.toFixed(2);

    validateStockAll();
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

  wrap.addEventListener('change', (e)=>{
    const sel = e.target.closest('[data-product]');
    if(!sel) return;

    const item = sel.closest('[data-item]');
    const pid = sel.value;

    const nameInput = item.querySelector('[data-name]');
    const skuInput = item.querySelector('[data-sku]');
    const priceInput = item.querySelector('[data-price]');
    const stockLabel = item.querySelector('[data-stocklabel]');

    const p = productMap.get(String(pid));
    if(!p){
      nameInput.value = '';
      skuInput.value = '';
      priceInput.value = 0;
      stockLabel.textContent = 'Stock: —';
      calc();
      return;
    }

    nameInput.value = p.name || '';
    skuInput.value = p.sku || '';
    priceInput.value = Number(p.price || 0).toFixed(2);
    stockLabel.textContent = `Stock: ${p.stock}`;

    // qty safety
    const qtyInput = item.querySelector('[data-qty]');
    if(parseInt(qtyInput.value || '1', 10) > p.stock){
      qtyInput.value = Math.max(1, p.stock);
    }

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
    const needTrx = (m === 'bkash' || m === 'nagad' || m === 'rocket');
    trxWrap.classList.toggle('hidden', !needTrx);
  }
  method.addEventListener('change', syncPay);
  syncPay();

  // init with 1 item
  btn.click();
})();
</script>
@endsection
