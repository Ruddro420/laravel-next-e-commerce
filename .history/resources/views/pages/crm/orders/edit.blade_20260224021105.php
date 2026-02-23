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
          <option value="">Walk-in</option>
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
        @php $st = old('status',$order->status); @endphp
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
          <div class="text-xs text-slate-500 dark:text-slate-400">Edit items and totals update instantly.</div>
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
        <input id="shipping" name="shipping" type="number" step="0.01" value="{{ old('shipping',$order->shipping) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
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
    </div>

    {{-- Payment --}}
    @php
    $pm = old('payment.method', $order->payment?->method ?? 'cod');
    $trx = old('payment.transaction_id', $order->payment?->transaction_id ?? '');
    $paidVal = old('payment.amount_paid', $order->payment?->amount_paid ?? 0);
    @endphp

    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold">Payment</div>

      <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-semibold">Method</label>
          <select id="payMethod" name="payment[method]"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="cod" {{ $pm==='cod'?'selected':'' }}>Cash on Delivery</option>
            <option value="cash_received" {{ $pm==='cash_received'?'selected':'' }}>Cash Received (Paid)</option>
            <option value="bkash" {{ $pm==='bkash'?'selected':'' }}>bKash</option>
            <option value="nagad" {{ $pm==='nagad'?'selected':'' }}>Nagad</option>
            <option value="rocket" {{ $pm==='rocket'?'selected':'' }}>Rocket</option>
          </select>
          <div class="text-xs text-slate-500 mt-1">
            “Cash Received” means the order is already paid in cash.
          </div>
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
          <div id="paidHint" class="text-xs text-slate-500 mt-1 hidden">
            Auto set to Total for “Cash Received”.
          </div>
        </div>
      </div>
    </div>

    {{-- Summary --}}
    {{-- Summary --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold mb-3">Summary (Live)</div>

      <div class="flex flex-wrap md:flex-nowrap items-center justify-between gap-6 text-sm">

        <div class="flex flex-col">
          <span class="text-xs text-slate-500 dark:text-slate-400">Subtotal</span>
          <span id="sumSubtotal" class="font-semibold text-base">0.00</span>
        </div>

        <div class="flex flex-col">
          <span class="text-xs text-slate-500 dark:text-slate-400">Tax</span>
          <span id="sumTax" class="font-semibold text-base">0.00</span>
        </div>

        <div class="flex flex-col">
          <span class="text-xs text-slate-500 dark:text-slate-400">Shipping</span>
          <span id="sumShipping" class="font-semibold text-base">0.00</span>
        </div>

        {{-- ✅ Coupon / Discount --}}
        <div class="flex flex-col">
          <span class="text-xs text-emerald-600 dark:text-emerald-400">Discount</span>
          <span id="sumDiscount" class="font-semibold text-base text-emerald-600">0.00</span>
        </div>

        <div class="flex flex-col">
          <span class="text-xs text-slate-500 dark:text-slate-400">Total</span>
          <span id="sumTotal" class="font-bold text-base">0.00</span>
        </div>

        <div class="flex flex-col">
          <span class="text-xs text-slate-500 dark:text-slate-400">Paid</span>
          <span id="sumPaid" class="font-semibold text-base text-emerald-600">0.00</span>
        </div>

        <div class="flex flex-col">
          <span class="text-xs text-slate-500 dark:text-slate-400">Due</span>
          <span id="sumDue" class="font-semibold text-base text-rose-600">0.00</span>
        </div>

      </div>
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

@php
// Products for dropdown
$productsJs = $products->map(function($p){
return [
'id' => $p->id,
'name' => $p->name,
'sku' => $p->sku,
'price' => (float)($p->sale_price ?? $p->regular_price ?? 0),
'stock' => (int)($p->stock ?? 0),
];
})->values()->toArray();

// ✅ Variants for dropdown (attributes-based label)
// NOTE: better to load in controller, but works here too.
$productIds = $products->pluck('id')->values()->toArray();
$variantsJs = \App\Models\ProductVariant::whereIn('product_id', $productIds)->get()->map(function($v){
$attrs = is_array($v->attributes) ? $v->attributes : (json_decode($v->attributes, true) ?: []);
$label = collect($attrs)->map(fn($val,$key) => "{$key}: {$val}")->implode(', ');

return [
'id' => (int)$v->id,
'product_id' => (int)$v->product_id,
'sku' => $v->sku,
'regular_price' => (float)($v->regular_price ?? 0),
'sale_price' => $v->sale_price !== null ? (float)$v->sale_price : null,
'stock' => $v->stock !== null ? (int)$v->stock : null,
'label' => $label ?: ("Variant #".$v->id),
'attributes' => $attrs,
];
})->values()->toArray();
@endphp

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const products = @json($productsJs);
    const variants = @json($variantsJs);

    const productMap = new Map(products.map(p => [String(p.id), p]));
    const variantsByProduct = new Map();

    variants.forEach(v => {
      const pid = String(v.product_id);
      if (!variantsByProduct.has(pid)) variantsByProduct.set(pid, []);
      variantsByProduct.get(pid).push(v);
    });

    // existing items from DB
    const existingItems = @json($order - > items);

    const itemsWrap = document.getElementById('itemsWrap');
    const btnAddItem = document.getElementById('btnAddItem');

    const shipping = document.getElementById('shipping');
    const taxSelect = document.getElementById('taxSelect');

    const payMethod = document.getElementById('payMethod');
    const trxWrap = document.getElementById('trxWrap');
    const trxId = document.getElementById('trxId');
    const amountPaid = document.getElementById('amountPaid');
    const paidHint = document.getElementById('paidHint');

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

    function escapeHtml(s) {
      return String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", "&#039;");
    }

    function productOptionsHtml(selectedId = '') {
      let html = `<option value="">(Keep manual item)</option>`;
      products.forEach(p => {
        const sel = String(selectedId) === String(p.id) ? 'selected' : '';
        const label = `${p.name}${p.sku ? ' — '+p.sku : ''} (Stock: ${p.stock})`;
        html += `<option value="${p.id}" ${sel}>${escapeHtml(label)}</option>`;
      });
      return html;
    }

    function variantOptionsHtml(productId = '', selectedVid = '') {
      if (!productId) {
        return `<option value="">No Variant</option>`;
      }
      const list = variantsByProduct.get(String(productId)) || [];
      let html = `<option value="">No Variant</option>`;
      list.forEach(v => {
        const sel = String(selectedVid) === String(v.id) ? 'selected' : '';
        const label = `${v.label}${v.sku ? ' — '+v.sku : ''}${v.stock !== null ? ' (Stock: '+v.stock+')' : ''}`;
        html += `<option value="${v.id}" ${sel}>${escapeHtml(label)}</option>`;
      });
      return html;
    }

    let idx = 0;

    function row(i, data = null) {
      const pid = data?.product_id ?? '';
      const vid = data?.variant_id ?? ''; // ✅ existing variant_id
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

        <div class="mt-3 grid grid-cols-1 md:grid-cols-7 gap-3">
          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Product</label>
            <select name="items[${i}][product_id]" data-product
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
              ${productOptionsHtml(pid)}
            </select>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400" data-stocklabel>
              Stock: —
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Variant</label>
            <select name="items[${i}][variant_id]" data-variant
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
              ${variantOptionsHtml(pid, vid)}
            </select>
            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400" data-variantlabel>
              Variant: —
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Product Name</label>
            <input name="items[${i}][product_name]" data-name required
              value="${escapeHtml(name)}"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>

          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">SKU</label>
            <input name="items[${i}][sku]" data-sku value="${escapeHtml(sku)}"
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
      </div>
    `;
    }

    function setVariantSelectOptions(itemEl) {
      const productSel = itemEl.querySelector('[data-product]');
      const variantSel = itemEl.querySelector('[data-variant]');
      if (!variantSel) return;

      const pid = productSel?.value || '';
      const currentVid = variantSel.value || '';

      variantSel.innerHTML = variantOptionsHtml(pid, currentVid);
    }

    function getSelectedVariant(pid, vid) {
      if (!pid || !vid) return null;
      const list = variantsByProduct.get(String(pid)) || [];
      return list.find(v => String(v.id) === String(vid)) || null;
    }

    function fillFromProductAndVariant(itemEl) {
      const productSel = itemEl.querySelector('[data-product]');
      const variantSel = itemEl.querySelector('[data-variant]');

      const pid = productSel?.value || '';
      const p = productMap.get(String(pid)) || null;

      const nameInput = itemEl.querySelector('[data-name]');
      const skuInput = itemEl.querySelector('[data-sku]');
      const priceInput = itemEl.querySelector('[data-price]');
      const stockLabel = itemEl.querySelector('[data-stocklabel]');
      const variantLabelEl = itemEl.querySelector('[data-variantlabel]');
      const qtyInput = itemEl.querySelector('[data-qty]');

      // If product not selected, keep manual fields
      if (!p) {
        stockLabel.textContent = 'Stock: —';
        if (variantLabelEl) variantLabelEl.textContent = 'Variant: —';
        return;
      }

      // Ensure variant dropdown updated for this product
      setVariantSelectOptions(itemEl);

      // Auto-fill from product (baseline)
      if (!nameInput.value) nameInput.value = p.name || '';
      if (!skuInput.value) skuInput.value = p.sku || '';
      if (!priceInput.value || Number(priceInput.value) === 0) {
        priceInput.value = Number(p.price || 0).toFixed(2);
      }
      stockLabel.textContent = `Stock: ${p.stock}`;

      // Apply variant overrides if selected
      const vid = variantSel?.value || '';
      const v = getSelectedVariant(pid, vid);

      if (v) {
        const vPrice = (v.sale_price !== null && v.sale_price !== undefined) ?
          Number(v.sale_price) :
          Number(v.regular_price || 0);

        // Replace SKU / price with variant values (common expectation)
        if (v.sku) skuInput.value = v.sku;
        if (Number.isFinite(vPrice)) priceInput.value = vPrice.toFixed(2);

        // Stock display
        if (v.stock !== null && v.stock !== undefined) {
          stockLabel.textContent = `Stock: ${v.stock} (variant)`;
          const q = parseInt(qtyInput.value || '1', 10);
          if (q > v.stock) qtyInput.value = Math.max(1, v.stock);
        }

        if (variantLabelEl) variantLabelEl.textContent = `Variant: ${v.label || ('Variant #'+v.id)}`;
      } else {
        if (variantLabelEl) variantLabelEl.textContent = 'Variant: —';
      }
    }

    function syncPay() {
      const m = payMethod?.value;

      const needTrx = (m === 'bkash' || m === 'nagad' || m === 'rocket');
      trxWrap?.classList.toggle('hidden', !needTrx);
      if (!needTrx && trxId) trxId.value = '';

      // If cash_received, auto set paid to total
      const isCashReceived = (m === 'cash_received');
      paidHint?.classList.toggle('hidden', !isCashReceived);

      if (isCashReceived) {
        // set amountPaid to current total after calc() updates sumTotal
        // We call calc() after this anyway, but safe:
        const totalNow = Number(sumTotal?.textContent || 0);
        if (amountPaid) amountPaid.value = totalNow.toFixed(2);
      }
    }

    function calc() {
      let subtotal = 0;

      itemsWrap.querySelectorAll('[data-item]').forEach(item => {
        fillFromProductAndVariant(item);

        const qty = Number(item.querySelector('[data-qty]')?.value || 0);
        const price = Number(item.querySelector('[data-price]')?.value || 0);
        const line = qty * price;

        subtotal += line;
        item.querySelector('[data-line]').value = line.toFixed(2);
      });

      const ship = Number(shipping?.value || 0);

      const taxOpt = taxSelect?.options[taxSelect.selectedIndex];
      const rate = Number(taxOpt?.dataset.rate || 0);
      const mode = (taxOpt?.dataset.mode || 'exclusive');

      const base = subtotal + ship;
      let tax = 0;

      if (rate > 0) {
        if (mode === 'exclusive') {
          tax = (base * rate) / 100;
        } else {
          const div = 1 + (rate / 100);
          tax = base - (base / div);
        }
      }

      const total = (mode === 'inclusive') ? base : (base + tax);

      // If Cash Received, force paid = total
      if (payMethod?.value === 'cash_received' && amountPaid) {
        amountPaid.value = total.toFixed(2);
      }

      const paid = Number(amountPaid?.value || 0);
      const due = Math.max(0, total - paid);

      sumSubtotal.textContent = subtotal.toFixed(2);
      sumTax.textContent = tax.toFixed(2);
      sumTotal.textContent = total.toFixed(2);
      sumPaid.textContent = paid.toFixed(2);
      sumDue.textContent = due.toFixed(2);
    }

    // Add item
    btnAddItem?.addEventListener('click', (e) => {
      e.preventDefault();
      itemsWrap.insertAdjacentHTML('beforeend', row(idx));
      idx++;
      calc();
    });

    // Remove item
    itemsWrap?.addEventListener('click', (e) => {
      const rm = e.target.closest('[data-remove]');
      if (!rm) return;
      rm.closest('[data-item]').remove();
      calc();
    });

    // Recalc on changes
    itemsWrap?.addEventListener('change', (e) => {
      if (e.target.matches('[data-product],[data-variant]')) calc();
    });

    itemsWrap?.addEventListener('input', (e) => {
      if (e.target.matches('[data-qty],[data-price]')) calc();
    });

    [shipping, amountPaid].forEach(el => {
      el?.addEventListener('input', calc);
    });
    taxSelect?.addEventListener('change', calc);

    payMethod?.addEventListener('change', () => {
      syncPay();
      calc();
    });

    // Render existing items
    if (Array.isArray(existingItems) && existingItems.length) {
      existingItems.forEach(it => {
        itemsWrap.insertAdjacentHTML('beforeend', row(idx, it));
        idx++;
      });
    } else {
      btnAddItem?.click();
    }

    // init
    syncPay();
    calc();
  });
</script>
@endsection