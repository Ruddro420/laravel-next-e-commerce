@extends('layouts.app')
@section('title','Barcode Labels')
@section('subtitle','POS')
@section('pageTitle','Barcode Label Generator')
@section('pageDesc','Search products, choose label size, set quantity, and print.')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  <div class="flex flex-col lg:flex-row gap-4 lg:items-end lg:justify-between">
    <div>
      <div class="font-semibold text-lg">Barcode Labels</div>
      <div class="text-sm text-slate-500 dark:text-slate-400">Generate printable barcode stickers for products.</div>
    </div>

    <form id="printForm" method="POST" action="{{ route('pos.barcode.labels.print') }}" target="_blank" class="flex flex-col sm:flex-row gap-2">
      @csrf
      <select name="size"
        class="rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800">
        <option value="38x25">38×25mm (3 columns)</option>
        <option value="50x25" selected>50×25mm (2 columns)</option>
        <option value="70x30">70×30mm (2 columns)</option>
        <option value="80x40">80×40mm (1 column)</option>
      </select>

      <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-800">
        <input type="checkbox" name="show_sku" class="rounded"> Show SKU
      </label>

      <label class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:border-slate-800">
        <input type="checkbox" name="show_price" class="rounded" checked> Show Price
      </label>

      <button type="submit"
        class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
        Print Labels
      </button>
    </form>
  </div>

  <div class="mt-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
    {{-- Search --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="font-semibold">Find Products</div>
      <div class="mt-2 flex gap-2">
        <input id="search"
          class="w-full rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search by name, SKU, barcode..." />
        <button id="btnSearch" type="button"
          class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800">
          Search
        </button>
      </div>

      <div id="results" class="mt-4 space-y-2"></div>
      <div id="empty" class="mt-4 text-sm text-slate-500 dark:text-slate-400 hidden">No products found.</div>
    </div>

    {{-- Selected --}}
    <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Selected for Print</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Set quantity per product.</div>
        </div>
        <button id="btnClear" type="button"
          class="text-xs font-semibold text-rose-600 hover:underline">
          Clear
        </button>
      </div>

      <div id="selected" class="mt-4 space-y-3"></div>

      <div class="mt-4 text-xs text-slate-500 dark:text-slate-400">
        Tip: You can open the print page in a new tab and press <b>Ctrl+P</b>.
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  const search = document.getElementById('search');
  const btnSearch = document.getElementById('btnSearch');
  const results = document.getElementById('results');
  const empty = document.getElementById('empty');

  const selectedWrap = document.getElementById('selected');
  const btnClear = document.getElementById('btnClear');
  const printForm = document.getElementById('printForm');

  // selected map: id -> {product, qty}
  const selected = new Map();

  function esc(s){
    return String(s ?? '')
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  }

  function renderResults(items){
    results.innerHTML = '';
    empty.classList.toggle('hidden', items.length !== 0);

    items.forEach(p => {
      const price = Number(p.price || 0).toFixed(2);
      const badge = (p.barcode ? 'Has barcode' : 'No barcode (auto-gen on print)');
      const inSel = selected.has(p.id);

      results.insertAdjacentHTML('beforeend', `
        <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-semibold">${esc(p.name)}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">
                SKU: ${esc(p.sku || '—')} • Barcode: ${esc(p.barcode || '—')} • Stock: ${esc(p.stock ?? '—')} • Price: ৳${price}
              </div>
              <div class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">${esc(badge)}</div>
            </div>
            <button type="button" data-add="${p.id}"
              class="shrink-0 rounded-2xl px-3 py-2 text-xs font-semibold ${inSel ? 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-200' : 'bg-slate-900 text-white dark:bg-white dark:text-slate-900'}">
              ${inSel ? 'Added' : '+ Add'}
            </button>
          </div>
        </div>
      `);

      // store product json on element? easiest: use map cache
      results.querySelector(`[data-add="${p.id}"]`)._product = p;
    });
  }

  function renderSelected(){
    selectedWrap.innerHTML = '';

    if(selected.size === 0){
      selectedWrap.innerHTML = `
        <div class="text-sm text-slate-500 dark:text-slate-400">
          No products selected yet.
        </div>
      `;
      syncForm();
      return;
    }

    [...selected.values()].forEach(({product, qty}) => {
      const price = Number(product.price || 0).toFixed(2);

      selectedWrap.insertAdjacentHTML('beforeend', `
        <div class="rounded-2xl border border-slate-200 p-3 dark:border-slate-800" data-sel="${product.id}">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="font-semibold">${esc(product.name)}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">
                SKU: ${esc(product.sku || '—')} • Barcode: ${esc(product.barcode || '—')} • Price: ৳${price}
              </div>
            </div>

            <button type="button" data-remove="${product.id}"
              class="text-xs font-semibold text-rose-600 hover:underline">
              Remove
            </button>
          </div>

          <div class="mt-3 flex items-center gap-2">
            <label class="text-xs font-semibold text-slate-600 dark:text-slate-300">Qty</label>
            <input type="number" min="1" max="500" value="${qty}" data-qty="${product.id}"
              class="w-24 rounded-2xl border border-slate-200 px-3 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" />
          </div>
        </div>
      `);
    });

    syncForm();
  }

  function syncForm(){
    // remove previous hidden inputs
    printForm.querySelectorAll('input[name^="items["]').forEach(el => el.remove());

    let i = 0;
    selected.forEach(({product, qty}) => {
      printForm.insertAdjacentHTML('beforeend', `
        <input type="hidden" name="items[${i}][product_id]" value="${product.id}">
        <input type="hidden" name="items[${i}][qty]" value="${qty}">
      `);
      i++;
    });
  }

  async function doSearch(){
    const q = search.value.trim();
    const url = new URL("{{ route('pos.barcode.products') }}", window.location.origin);
    if(q) url.searchParams.set('q', q);

    const res = await fetch(url.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    const data = await res.json();
    renderResults(data);
  }

  btnSearch.addEventListener('click', doSearch);
  search.addEventListener('keydown', (e)=>{
    if(e.key === 'Enter'){
      e.preventDefault();
      doSearch();
    }
  });

  results.addEventListener('click', (e)=>{
    const btn = e.target.closest('[data-add]');
    if(!btn) return;
    const id = Number(btn.getAttribute('data-add'));
    const p = btn._product;
    if(!p) return;

    if(!selected.has(id)){
      selected.set(id, { product: p, qty: 1 });
      renderSelected();
      doSearch(); // refresh button state
    }
  });

  selectedWrap.addEventListener('click', (e)=>{
    const rm = e.target.closest('[data-remove]');
    if(!rm) return;
    const id = Number(rm.getAttribute('data-remove'));
    selected.delete(id);
    renderSelected();
    doSearch();
  });

  selectedWrap.addEventListener('input', (e)=>{
    const q = e.target.closest('[data-qty]');
    if(!q) return;
    const id = Number(q.getAttribute('data-qty'));
    const v = Math.max(1, Math.min(500, parseInt(q.value || '1', 10)));
    const row = selected.get(id);
    if(!row) return;
    row.qty = v;
    selected.set(id, row);
    syncForm();
  });

  btnClear.addEventListener('click', ()=>{
    selected.clear();
    renderSelected();
    doSearch();
  });

  printForm.addEventListener('submit', (e)=>{
    if(selected.size === 0){
      e.preventDefault();
      alert('Select at least 1 product to print labels.');
    }
  });

  // initial load
  renderSelected();
  doSearch();
})();
</script>
@endsection
