{{-- resources/views/pages/pos/index.blade.php --}}
@extends('layouts.app')
@section('title','POS')
@section('subtitle','POS')
@section('pageTitle','Point of Sale')
@section('pageDesc','Barcode scan, quick checkout, hold sales, receipt printing, and shortcuts.')

@section('content')
@php
// This page expects:
// $customers (Collection)
// $taxRates (Collection)
// $holds (Collection, optional latest 10)
@endphp

<div class="grid grid-cols-1 xl:grid-cols-12 gap-4">

  {{-- LEFT: Products/Search --}}
  <div class="xl:col-span-7 space-y-4">

    {{-- Top actions --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="font-semibold">POS Terminal</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">
            Shortcuts: <span class="font-semibold">F2</span> Scan • <span class="font-semibold">F4</span> Hold • <span class="font-semibold">F9</span> Checkout • <span class="font-semibold">ESC</span> Close
          </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 justify-end">
          <button id="btnScan" type="button"
            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            <span>📷</span><span>Scan (F2)</span>
          </button>

          <button id="btnHoldSale" type="button"
            class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
            <span>🧾</span><span>Hold (F4)</span>
          </button>

          <a href="{{ route('pos.holds') }}"
            class="inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
            <span>📌</span><span>Holds</span>
          </a>

          <button id="btnAddCustomer" type="button"
            class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <span>➕</span><span>Customer</span>
          </button>
        </div>
      </div>
    </div>

    {{-- Search --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center gap-2">
        <div class="flex-1">
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Search products (name / SKU / barcode)</label>
          <div class="mt-2 relative">
            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">🔎</span>
            <input id="q"
              class="w-full rounded-2xl border border-slate-200 bg-white px-9 py-2.5 text-sm shadow-sm outline-none placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
              placeholder="Type to search..." autocomplete="off">
          </div>
        </div>
        <div class="pt-6">
          <button id="btnClearSearch" type="button"
            class="rounded-2xl border border-slate-200 px-3 py-2 text-sm font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
            Clear
          </button>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" id="results"></div>

      <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">
        Tip: You can scan barcode (camera) or just paste barcode into search.
      </div>
    </div>

    {{-- Recent Holds (quick load) --}}
    @if(isset($holds) && $holds->count())
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div class="font-semibold">Recent Holds</div>
        <a href="{{ route('pos.holds') }}" class="text-sm font-semibold text-indigo-600 hover:underline">View all</a>
      </div>

      <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach($holds as $h)
        <button type="button" data-loadhold="{{ $h->id }}"
          class="w-full rounded-2xl border border-slate-200 p-3 text-left hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
          <div class="flex items-center justify-between">
            <div class="font-semibold text-sm">{{ $h->ref }}</div>
            <div class="text-sm font-bold">৳{{ number_format($h->total,2) }}</div>
          </div>
          <div class="text-xs text-slate-500 dark:text-slate-400">
            {{ $h->customer?->name ?? 'Walk-in' }} • {{ $h->created_at->format('Y-m-d H:i') }}
          </div>
        </button>
        @endforeach
      </div>
    </div>
    @endif

  </div>

  {{-- RIGHT: Cart/Checkout --}}
  <div class="xl:col-span-5 space-y-4">

    {{-- Cart --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Cart</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Adjust qty, remove items, totals update instantly.</div>
        </div>
        <button id="btnClearCart" type="button"
          class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
          Clear Cart
        </button>
      </div>

      <div id="cartWrap" class="mt-4 space-y-3"></div>
    </div>

    {{-- Checkout form --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Checkout</div>

      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="md:col-span-2">
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Customer</label>
          <select id="customer"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="">Walk-in Customer</option>
            @foreach($customers as $c)
            <option value="{{ $c->id }}"
              data-billing="{{ e($c->billing_address ?? '') }}"
              data-shipping="{{ e($c->shipping_address ?? '') }}">
              {{ $c->name }} {{ $c->phone ? '(' . $c->phone . ')' : '' }}
            </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Status</label>
          <select id="status"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="processing">Processing</option>
            <option value="complete">Complete</option>
            <option value="hold">Hold</option>
          </select>
        </div>

        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Shipping</label>
          <input id="shipping" type="number" step="0.01" value="0"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
        </div>

        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Coupon Code</label>
          <input id="coupon" placeholder="SAVE10 (optional)"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
        </div>

        <div>
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Tax</label>
          <select id="taxRate"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="">No Tax</option>
            @foreach($taxRates as $t)
            <option value="{{ $t->id }}" data-rate="{{ $t->rate }}" data-mode="{{ $t->mode }}">
              {{ $t->name }} ({{ $t->rate }}% {{ $t->mode }})
            </option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-2">
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Billing Address</label>
          <textarea id="bill" rows="2"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Billing address..."></textarea>
        </div>

        <div class="md:col-span-2">
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Shipping Address</label>
          <textarea id="ship" rows="2"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Shipping address..."></textarea>
        </div>

        <div class="md:col-span-2">
          <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Note</label>
          <textarea id="note" rows="2"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Order note..."></textarea>
        </div>
      </div>

      {{-- Payment --}}
      <div class="mt-4 rounded-2xl border border-slate-200 p-3 dark:border-slate-800">
        <div class="font-semibold text-sm">Payment</div>
        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Method</label>
            <select id="payMethod"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
              <option value="cod">Cash on Delivery</option>
              <option value="bkash">bKash</option>
              <option value="nagad">Nagad</option>
              <option value="rocket">Rocket</option>
            </select>
          </div>

          <div id="trxWrap" class="hidden">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Transaction ID</label>
            <input id="trxId" placeholder="TXN id"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          </div>

          <div class="md:col-span-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Amount Paid</label>
            <input id="amountPaid" type="number" step="0.01" value="0"
              class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <div class="mt-2 flex flex-wrap gap-2">
              <button type="button" data-quickpay="100" class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">৳100</button>
              <button type="button" data-quickpay="500" class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">৳500</button>
              <button type="button" data-quickpay="1000" class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">৳1000</button>
              <button type="button" id="btnExact" class="rounded-2xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">Exact</button>
            </div>
          </div>
        </div>

        <div class="mt-2 text-xs text-slate-500 dark:text-slate-400">
          COD usually paid = 0, due = total. Mobile payments need TXN.
        </div>
      </div>

      {{-- Summary --}}
      <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
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
          <div class="text-xs text-slate-500 dark:text-slate-400">Due</div>
          <div id="sumDue" class="font-semibold">0.00</div>
        </div>
      </div>

      <div class="mt-4 flex gap-2">
        <button id="btnCheckout" type="button"
          class="flex-1 rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
          Checkout (F9)
        </button>

        <button id="btnPrintLast" type="button"
          class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
          Print Last
        </button>
      </div>

      <div id="posMsg" class="hidden mt-3 rounded-2xl border px-4 py-3 text-sm"></div>
    </div>
  </div>

</div>

{{-- Scan Modal (Camera Barcode) --}}
<div id="scanModal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/50"></div>
  <div class="relative mx-auto mt-12 w-[92%] max-w-2xl rounded-2xl bg-white p-4 shadow-soft dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div class="font-semibold">Scan Barcode</div>
      <button id="btnCloseScan" class="h-10 w-10 rounded-2xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800" type="button">✕</button>
    </div>
    <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">
      Allow camera permission. Works best in HTTPS / localhost.
    </div>

    <div class="mt-4 rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
      <div id="scanner" class="w-full bg-black" style="min-height: 320px;"></div>
    </div>

    <div class="mt-4 flex items-center justify-between gap-2">
      <div class="text-xs text-slate-500 dark:text-slate-400">Tip: Use F2 to open scan quickly.</div>
      <button id="btnStopScan" type="button"
        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
        Stop
      </button>
    </div>
  </div>
</div>

{{-- Customer Add Modal --}}
<div id="custModal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/50"></div>
  <div class="relative mx-auto mt-12 w-[92%] max-w-xl rounded-2xl bg-white p-4 shadow-soft dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div class="font-semibold">Add Customer</div>
      <button id="btnCloseCust" class="h-10 w-10 rounded-2xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800" type="button">✕</button>
    </div>

    <div id="custErr" class="hidden mt-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200"></div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
      <div class="md:col-span-2">
        <label class="text-xs font-semibold">Name</label>
        <input id="cName" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
      <div>
        <label class="text-xs font-semibold">Phone</label>
        <input id="cPhone" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
      <div>
        <label class="text-xs font-semibold">Email</label>
        <input id="cEmail" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>
      <div class="md:col-span-2">
        <label class="text-xs font-semibold">Billing Address</label>
        <textarea id="cBill" rows="2" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
      </div>
      <div class="md:col-span-2">
        <label class="text-xs font-semibold">Shipping Address</label>
        <textarea id="cShip" rows="2" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
      </div>
    </div>

    <button id="btnSaveCust"
      class="mt-4 w-full rounded-2xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
      type="button">
      Save Customer
    </button>
  </div>
</div>

{{-- Variant Select Modal --}}
<div id="variantModal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/50"></div>

  <div class="relative mx-auto mt-12 w-[92%] max-w-2xl rounded-2xl bg-white p-4 shadow-soft dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div>
        <div class="font-semibold" id="vmTitle">Choose Variant</div>
        <div class="text-xs text-slate-500 dark:text-slate-400" id="vmSub">Select variant to add to cart</div>
      </div>
      <button id="btnCloseVariant"
        class="h-10 w-10 rounded-2xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
        type="button">✕</button>
    </div>

    <div id="variantList" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3"></div>

    <div class="mt-4 flex items-center justify-end gap-2">
      <button id="btnCancelVariant" type="button"
        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
        Cancel
      </button>
    </div>
  </div>
</div>

{{-- Barcode libs: QuaggaJS for camera scan, JsBarcode for label generate --}}
<script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

<script>
  (function() {
    const csrf = @json(csrf_token());
    const productsUrl = @json(route('pos.products'));
    const checkoutUrl = @json(route('pos.checkout'));
    const customerStoreUrl = @json(route('pos.customers.store'));
    const holdsStoreUrl = @json(route('pos.holds.store'));

    // If you also added product-by-id endpoint, set to true and fill URL template:
    const hasProductOneEndpoint = false;
    const productOneUrlTpl = ''; // e.g. "/pos/product/{id}"

    // ---------- State ----------
    const cart = new Map(); // key: productId -> {id,name,sku,barcode,price,stock,qty,image}
    let lastReceiptLinks = null;
    window.__POS_HOLD_ID__ = null;

    // ---------- Elements ----------
    const q = document.getElementById('q');
    const results = document.getElementById('results');
    const cartWrap = document.getElementById('cartWrap');
    const posMsg = document.getElementById('posMsg');

    const customer = document.getElementById('customer');
    const bill = document.getElementById('bill');
    const ship = document.getElementById('ship');

    const status = document.getElementById('status');
    const shipping = document.getElementById('shipping');
    const coupon = document.getElementById('coupon');
    const taxRate = document.getElementById('taxRate');

    const payMethod = document.getElementById('payMethod');
    const trxWrap = document.getElementById('trxWrap');
    const trxId = document.getElementById('trxId');
    const amountPaid = document.getElementById('amountPaid');

    const sumSubtotal = document.getElementById('sumSubtotal');
    const sumTax = document.getElementById('sumTax');
    const sumTotal = document.getElementById('sumTotal');
    const sumDue = document.getElementById('sumDue');

    const btnCheckout = document.getElementById('btnCheckout');
    const btnHoldSale = document.getElementById('btnHoldSale');
    const btnPrintLast = document.getElementById('btnPrintLast');

    // Scan modal elements
    const scanModal = document.getElementById('scanModal');
    const btnScan = document.getElementById('btnScan');
    const btnCloseScan = document.getElementById('btnCloseScan');
    const btnStopScan = document.getElementById('btnStopScan');

    // Customer modal elements
    const custModal = document.getElementById('custModal');
    const btnAddCustomer = document.getElementById('btnAddCustomer');
    const btnCloseCust = document.getElementById('btnCloseCust');
    const btnSaveCust = document.getElementById('btnSaveCust');
    const custErr = document.getElementById('custErr');

    // ---------- UI helpers ----------
    function showMsg(type, text) {
      posMsg.classList.remove('hidden');
      posMsg.className = 'mt-3 rounded-2xl border px-4 py-3 text-sm';
      if (type === 'success') {
        posMsg.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800', 'dark:border-emerald-500/30', 'dark:bg-emerald-500/10', 'dark:text-emerald-200');
      } else if (type === 'error') {
        posMsg.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800', 'dark:border-rose-500/30', 'dark:bg-rose-500/10', 'dark:text-rose-200');
      } else {
        posMsg.classList.add('border-slate-200', 'bg-slate-50', 'text-slate-700', 'dark:border-slate-800', 'dark:bg-slate-800', 'dark:text-slate-100');
      }
      posMsg.textContent = text;
    }

    function money(n) {
      return Number(n || 0).toFixed(2);
    }

    function escapeHtml(s) {
      return String(s ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", "&#039;");
    }

    // ---------- Products search ----------
    let searchTimer = null;
    async function fetchProducts(query) {
      const url = productsUrl + '?q=' + encodeURIComponent(query || '');
      const res = await fetch(url, {
        headers: {
          'Accept': 'application/json'
        }
      });
      if (!res.ok) return [];
      return await res.json();
    }

   function renderResults(items){
  results.innerHTML = '';
  if(!items.length){
    results.innerHTML = `
      <div class="col-span-full text-sm text-slate-500 dark:text-slate-400">
        No products found.
      </div>
    `;
    return;
  }

  items.forEach(p=>{
    const isVariable = p.type === 'variable';

    const stockTxt = (p.stock !== null && p.stock !== undefined)
      ? ((p.stock <= 0) ? 'Out of stock' : ('Stock: ' + p.stock))
      : (isVariable ? 'Variable product' : 'Stock: —');

    const stockCls = (p.stock !== null && p.stock !== undefined && p.stock <= 0)
      ? 'text-rose-600'
      : 'text-slate-500 dark:text-slate-400';

    const actionText = isVariable ? 'Choose Variant' : 'Add to cart';
    const actionColor = isVariable ? 'text-emerald-600' : 'text-indigo-600';

    results.insertAdjacentHTML('beforeend', `
      <button type="button" data-add="${p.id}"
        class="group rounded-2xl border border-slate-200 bg-white p-3 text-left shadow-sm hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        <div class="flex gap-3">
          <div class="h-12 w-12 shrink-0 overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800 grid place-items-center">
            ${p.image ? `<img src="${p.image}" class="h-full w-full object-cover">` : `<span class="text-slate-400">🧾</span>`}
          </div>
          <div class="min-w-0 flex-1">
            <div class="font-semibold truncate">${escapeHtml(p.name)}</div>
            <div class="text-xs ${stockCls}">${stockTxt}</div>
            <div class="mt-1 text-sm font-bold">৳${money(p.price)}</div>
            <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
              ${escapeHtml(p.sku || '')} ${p.barcode ? ' • ' + escapeHtml(p.barcode) : ''}
            </div>
          </div>
        </div>

        <div class="mt-2 text-xs font-semibold ${actionColor} group-hover:underline">
          ${actionText}
        </div>
      </button>
    `);
  });

  results.querySelectorAll('[data-add]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.getAttribute('data-add');
      const prod = items.find(x => String(x.id) === String(id));
      if(!prod) return;

      if(prod.type === 'variable'){
        openVariantModal(prod);
      }else{
        addProductToCart(prod, 1);
      }
    });
  });
}


    function addToCart(prod, qty) {
      const id = String(prod.id);
      const existing = cart.get(id);
      const addQty = Number(qty || 1);

      if (prod.stock !== null && prod.stock !== undefined) {
        const max = Number(prod.stock);
        const nextQty = (existing ? existing.qty : 0) + addQty;
        if (nextQty > max) {
          showMsg('error', `Not enough stock for ${prod.name}. Available: ${max}`);
          return;
        }
      }

      if (existing) {
        existing.qty += addQty;
        cart.set(id, existing);
      } else {
        cart.set(id, {
          id: prod.id,
          name: prod.name,
          sku: prod.sku || '',
          barcode: prod.barcode || '',
          price: Number(prod.price || 0),
          stock: (prod.stock === null || prod.stock === undefined) ? null : Number(prod.stock),
          qty: addQty,
          image: prod.image || null
        });
      }
      renderCart();
    }

    // ---------- Cart render ----------
    function renderCart() {
      cartWrap.innerHTML = '';
      if (cart.size === 0) {
        cartWrap.innerHTML = `
        <div class="rounded-2xl border border-dashed border-slate-200 p-5 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
          Cart is empty.
        </div>
      `;
        calcTotals();
        return;
      }

      Array.from(cart.values()).forEach(item => {
        const line = item.qty * item.price;
        const stockHint = (item.stock !== null) ? `<div class="text-[11px] text-slate-500 dark:text-slate-400">Stock: ${item.stock}</div>` : '';

        cartWrap.insertAdjacentHTML('beforeend', `
        <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-800" data-cart-item="${item.id}">
          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <div class="font-semibold truncate">${escapeHtml(item.name)}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400 truncate">${escapeHtml(item.sku || '')} ${item.barcode ? ' • ' + escapeHtml(item.barcode) : ''}</div>
              ${stockHint}
            </div>
            <button type="button" data-remove="${item.id}" class="text-xs font-semibold text-rose-600">Remove</button>
          </div>

          <div class="mt-3 grid grid-cols-3 gap-2 items-end">
            <div>
              <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Price</label>
              <input type="number" step="0.01" min="0" value="${money(item.price)}" data-price="${item.id}"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800">
            </div>
            <div>
              <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-300">Qty</label>
              <input type="number" min="1" value="${item.qty}" data-qty="${item.id}"
                class="mt-1 w-full rounded-2xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800">
            </div>
            <div class="text-right">
              <div class="text-[11px] text-slate-500 dark:text-slate-400">Line</div>
              <div class="font-bold">৳${money(line)}</div>
            </div>
          </div>
        </div>
      `);
      });

      cartWrap.querySelectorAll('[data-remove]').forEach(btn => {
        btn.addEventListener('click', () => {
          cart.delete(String(btn.getAttribute('data-remove')));
          renderCart();
        });
      });

      cartWrap.querySelectorAll('[data-qty]').forEach(inp => {
        inp.addEventListener('input', () => {
          const id = String(inp.getAttribute('data-qty'));
          const item = cart.get(id);
          if (!item) return;
          let v = Number(inp.value || 1);
          if (v < 1) v = 1;

          if (item.stock !== null && v > item.stock) {
            inp.value = item.stock;
            v = item.stock;
            showMsg('error', `Qty exceeds stock for ${item.name}.`);
          }

          item.qty = v;
          cart.set(id, item);
          renderCart();
        });
      });

      cartWrap.querySelectorAll('[data-price]').forEach(inp => {
        inp.addEventListener('input', () => {
          const id = String(inp.getAttribute('data-price'));
          const item = cart.get(id);
          if (!item) return;
          let v = Number(inp.value || 0);
          if (v < 0) v = 0;
          item.price = v;
          cart.set(id, item);
          calcTotals();
          // Don't rerender full cart on price to preserve focus
          // But update summary only
        });
      });

      calcTotals();
    }

    // ---------- Totals ----------
    function calcTotals() {
      let subtotal = 0;
      cart.forEach(it => subtotal += it.qty * it.price);

      const ship = Number(shipping.value || 0);
      const opt = taxRate.options[taxRate.selectedIndex];
      const rate = Number(opt?.dataset.rate || 0);
      const mode = (opt?.dataset.mode || 'exclusive');

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
      const paid = Number(amountPaid.value || 0);
      const due = Math.max(0, total - paid);

      sumSubtotal.textContent = money(subtotal);
      sumTax.textContent = money(tax);
      sumTotal.textContent = money(total);
      sumDue.textContent = money(due);
    }

    // ---------- Customer auto fill addresses ----------
    customer.addEventListener('change', () => {
      const opt = customer.options[customer.selectedIndex];
      bill.value = opt?.dataset.billing || '';
      ship.value = opt?.dataset.shipping || '';
    });

    // ---------- Payment method tx toggle ----------
    function syncPay() {
      const m = payMethod.value;
      const needTrx = (m === 'bkash' || m === 'nagad' || m === 'rocket');
      trxWrap.classList.toggle('hidden', !needTrx);
    }
    payMethod.addEventListener('change', syncPay);
    syncPay();

    // ---------- Quick Pay ----------
    document.querySelectorAll('[data-quickpay]').forEach(btn => {
      btn.addEventListener('click', () => {
        const v = Number(btn.getAttribute('data-quickpay') || 0);
        amountPaid.value = v;
        calcTotals();
      });
    });

    document.getElementById('btnExact').addEventListener('click', () => {
      amountPaid.value = sumTotal.textContent;
      calcTotals();
    });

    // ---------- Search input ----------
    q.addEventListener('input', () => {
      clearTimeout(searchTimer);
      const term = q.value.trim();
      searchTimer = setTimeout(async () => {
        const items = await fetchProducts(term);
        renderResults(items);
      }, 250);
    });

    document.getElementById('btnClearSearch').addEventListener('click', () => {
      q.value = '';
      results.innerHTML = '';
      q.focus();
    });

    // ---------- Clear cart ----------
    document.getElementById('btnClearCart').addEventListener('click', () => {
      if (cart.size && !confirm('Clear cart?')) return;
      cart.clear();
      window.__POS_HOLD_ID__ = null;
      renderCart();
    });

    // ---------- Totals watchers ----------
    [shipping, taxRate, amountPaid].forEach(el => {
      el.addEventListener('input', calcTotals);
      el.addEventListener('change', calcTotals);
    });

    // ---------- Checkout ----------
    async function checkout() {
      if (cart.size === 0) {
        showMsg('error', 'Cart is empty.');
        return;
      }

      // build items payload
      const items = Array.from(cart.values()).map(it => ({
        product_id: it.id,
        qty: it.qty
      }));

      const payload = {
        customer_id: customer.value || null,
        status: status.value,
        coupon_code: coupon.value || null,
        tax_rate_id: taxRate.value || null,
        shipping: Number(shipping.value || 0),
        billing_address: bill.value || null,
        shipping_address: ship.value || null,
        note: note.value || null,

        items,

        payment: {
          method: payMethod.value,
          transaction_id: trxId.value || null,
          amount_paid: Number(amountPaid.value || 0),
        },

        hold_id: window.__POS_HOLD_ID__ || null
      };

      btnCheckout.disabled = true;
      btnCheckout.classList.add('opacity-60');
      showMsg('info', 'Processing checkout...');

      const res = await fetch(checkoutUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
      });

      const json = await res.json().catch(() => null);

      btnCheckout.disabled = false;
      btnCheckout.classList.remove('opacity-60');

      if (!res.ok) {
        showMsg('error', json?.message || 'Checkout failed.');
        return;
      }

      showMsg('success', `Order created: ${json.order_number}`);

      lastReceiptLinks = {
        a4: json.receipt_a4,
        r58: json.receipt_58,
        r80: json.receipt_80,
        redirect: json.redirect
      };

      // clear cart and hold flag
      cart.clear();
      window.__POS_HOLD_ID__ = null;
      renderCart();

      // ask print
      const printThermal = confirm('Print Thermal 80mm receipt now?');
      if (printThermal && lastReceiptLinks?.r80) {
        window.open(lastReceiptLinks.r80, '_blank');
      } else {
        const printA4 = confirm('Print A4 receipt now?');
        if (printA4 && lastReceiptLinks?.a4) {
          window.open(lastReceiptLinks.a4, '_blank');
        }
      }
    }

    btnCheckout.addEventListener('click', checkout);

    btnPrintLast.addEventListener('click', () => {
      if (!lastReceiptLinks) {
        alert('No recent receipt to print.');
        return;
      }
      const choice = prompt('Type: a4 / 58 / 80', '80');
      if (!choice) return;
      if (choice === 'a4') window.open(lastReceiptLinks.a4, '_blank');
      if (choice === '58') window.open(lastReceiptLinks.r58, '_blank');
      if (choice === '80') window.open(lastReceiptLinks.r80, '_blank');
    });

    // ---------- Hold Sale ----------
    btnHoldSale.addEventListener('click', async () => {
      if (cart.size === 0) {
        alert('Cart is empty.');
        return;
      }
      const title = prompt('Hold title (optional):', '') || null;

      const payload = {
        cart: Array.from(cart.values()).map(it => ({
          id: it.id,
          qty: it.qty
        })),
        form: {
          customer_id: customer.value || null,
          status: status.value,
          coupon_code: coupon.value || null,
          tax_rate_id: taxRate.value || null,
          shipping: Number(shipping.value || 0),
          payment_method: payMethod.value,
          trx: trxId.value || null,
          paid: Number(amountPaid.value || 0),
          bill: bill.value || null,
          ship: ship.value || null,
          note: note.value || null
        }
      };

      const subtotal = Number(sumSubtotal.textContent || 0);
      const total = Number(sumTotal.textContent || 0);

      const res = await fetch(holdsStoreUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({
          title,
          customer_id: payload.form.customer_id,
          payload,
          subtotal,
          total
        })
      });

      const json = await res.json().catch(() => null);
      if (!res.ok) {
        alert(json?.message || 'Failed to hold sale.');
        return;
      }

      cart.clear();
      window.__POS_HOLD_ID__ = null;
      renderCart();
      showMsg('success', 'Saved to hold: ' + json.hold.ref);
    });

    // Load holds from buttons
    document.querySelectorAll('[data-loadhold]').forEach(btn => {
      btn.addEventListener('click', async () => {
        const id = btn.getAttribute('data-loadhold');
        const res = await fetch(`/pos/holds/${id}`, {
          headers: {
            'Accept': 'application/json'
          }
        });
        const json = await res.json().catch(() => null);
        if (!res.ok || !json?.ok) {
          alert('Failed to load hold.');
          return;
        }

        const p = json.hold.payload || {};
        const form = p.form || {};

        // restore form
        customer.value = form.customer_id || '';
        customer.dispatchEvent(new Event('change'));
        status.value = form.status || 'processing';
        coupon.value = form.coupon_code || '';
        taxRate.value = form.tax_rate_id || '';
        shipping.value = form.shipping || 0;
        payMethod.value = form.payment_method || 'cod';
        trxId.value = form.trx || '';
        amountPaid.value = form.paid || 0;
        bill.value = form.bill || bill.value || '';
        ship.value = form.ship || ship.value || '';
        note.value = form.note || '';

        payMethod.dispatchEvent(new Event('change'));

        // restore cart
        cart.clear();

        // BEST: if you added /pos/product/{id} endpoint, set hasProductOneEndpoint=true and productOneUrlTpl accordingly.
        // Otherwise fallback: fetch by ID through search isn't guaranteed; we try anyway.
        for (const it of (p.cart || [])) {
          const pid = it.id;
          const qty = it.qty;

          let prod = null;

          if (hasProductOneEndpoint && productOneUrlTpl) {
            const oneUrl = productOneUrlTpl.replace('{id}', pid);
            const r = await fetch(oneUrl, {
              headers: {
                'Accept': 'application/json'
              }
            });
            if (r.ok) prod = await r.json();
          } else {
            const list = await fetchProducts(String(pid));
            prod = list.find(x => String(x.id) === String(pid)) || null;
          }

          if (prod) addToCart(prod, qty);
        }

        window.__POS_HOLD_ID__ = json.hold.id;
        showMsg('success', 'Hold loaded: ' + json.hold.ref);
      });
    });

    // ---------- Customer modal ----------
    function showCustErr(msg) {
      if (!msg) {
        custErr.classList.add('hidden');
        custErr.textContent = '';
        return;
      }
      custErr.classList.remove('hidden');
      custErr.textContent = msg;
    }

    btnAddCustomer.addEventListener('click', () => custModal.classList.remove('hidden'));
    btnCloseCust.addEventListener('click', () => custModal.classList.add('hidden'));

    btnSaveCust.addEventListener('click', async () => {
      showCustErr(null);
      const payload = {
        name: document.getElementById('cName').value.trim(),
        phone: document.getElementById('cPhone').value.trim(),
        email: document.getElementById('cEmail').value.trim(),
        billing_address: document.getElementById('cBill').value.trim(),
        shipping_address: document.getElementById('cShip').value.trim(),
      };

      if (!payload.name) {
        showCustErr('Name is required.');
        return;
      }

      const res = await fetch(customerStoreUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(payload)
      });

      const json = await res.json().catch(() => null);
      if (!res.ok) {
        showCustErr(json?.message || 'Failed to save customer.');
        return;
      }

      const c = json.customer;
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.dataset.billing = c.billing_address || '';
      opt.dataset.shipping = c.shipping_address || '';
      opt.textContent = `${c.name}${c.phone ? ' ('+c.phone+')' : ''}`;
      customer.appendChild(opt);
      customer.value = c.id;
      customer.dispatchEvent(new Event('change'));

      custModal.classList.add('hidden');
      showMsg('success', 'Customer added.');
    });

    // ---------- Scan (Quagga) ----------
    let quaggaRunning = false;

    function startScan() {
      scanModal.classList.remove('hidden');

      if (quaggaRunning) return;

      Quagga.init({
        inputStream: {
          name: "Live",
          type: "LiveStream",
          target: document.querySelector('#scanner'),
          constraints: {
            facingMode: "environment"
          }
        },
        decoder: {
          readers: [
            "ean_reader", "ean_8_reader", "code_128_reader", "code_39_reader", "upc_reader", "upc_e_reader"
          ]
        },
        locate: true
      }, function(err) {
        if (err) {
          showMsg('error', 'Scanner init failed: ' + err);
          return;
        }
        Quagga.start();
        quaggaRunning = true;
      });

      Quagga.onDetected(async function(data) {
        const code = data?.codeResult?.code;
        if (!code) return;

        // stop scan to avoid duplicates
        stopScan();

        // search by barcode
        q.value = code;
        const items = await fetchProducts(code);
        renderResults(items);

        const exact = items.find(x => String(x.barcode) === String(code)) || items[0];
        if (exact) {
          addToCart(exact, 1);
          showMsg('success', 'Scanned: ' + code);
        } else {
          showMsg('error', 'No product found for barcode: ' + code);
        }
      });
    }

    function stopScan() {
      scanModal.classList.add('hidden');
      if (quaggaRunning) {
        try {
          Quagga.stop();
        } catch (e) {}
        quaggaRunning = false;
      }
      // clean scanner dom
      const sc = document.getElementById('scanner');
      if (sc) sc.innerHTML = '';
    }

    btnScan.addEventListener('click', startScan);
    btnCloseScan.addEventListener('click', stopScan);
    btnStopScan.addEventListener('click', stopScan);

    // ---------- Keyboard shortcuts ----------
    document.addEventListener('keydown', (e) => {
      if (e.key === 'F2') {
        e.preventDefault();
        btnScan.click();
      }
      if (e.key === 'F4') {
        e.preventDefault();
        btnHoldSale.click();
      }
      if (e.key === 'F9') {
        e.preventDefault();
        btnCheckout.click();
      }
      if (e.key === 'Escape') {
        stopScan();
        custModal.classList.add('hidden');
      }
    });

    // ---------- Init ----------
    renderCart();
    calcTotals();

    // Auto load hold via query ?hold=ID (optional)
    const params = new URLSearchParams(window.location.search);
    const holdId = params.get('hold');
    if (holdId) {
      const btn = document.querySelector(`[data-loadhold="${holdId}"]`);
      if (btn) btn.click();
    }
  })();
</script>
@endsection