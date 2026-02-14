@extends('layouts.app')

@section('title','Orders')
@section('subtitle','Orders')
@section('pageTitle','Orders')
@section('pageDesc','Track, filter and manage customer orders.')

@section('pageActions')
  <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">Export</button>
  <button class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-soft hover:bg-indigo-700">Create Order</button>
@endsection

@section('content')
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="text-lg font-semibold">Order List</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">Client-side demo filtering (raw JS).</div>
      </div>

      <div class="flex flex-wrap gap-2">
        <input id="orderSearch" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm shadow-sm dark:bg-slate-900 dark:border-slate-800"
               placeholder="Search order/customer...">
        <select id="orderStatus" class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm shadow-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="all">All</option>
          <option value="Paid">Paid</option>
          <option value="Pending">Pending</option>
          <option value="Shipped">Shipped</option>
          <option value="Refunded">Refunded</option>
        </select>
      </div>
    </div>

    <div class="mt-4 overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
          <tr>
            <th class="py-3 pr-3">Order</th>
            <th class="py-3 pr-3">Customer</th>
            <th class="py-3 pr-3">Amount</th>
            <th class="py-3 pr-3">Status</th>
            <th class="py-3 pr-3">Date</th>
            <th class="py-3 text-right">Action</th>
          </tr>
        </thead>
        <tbody id="ordersBody" class="divide-y divide-slate-100 dark:divide-slate-800"></tbody>
      </table>
    </div>
  </div>

  <script>
    // page-only JS (raw)
    const orders = [
      { id:'SP-10482', customer:'Amina Rahman', amount:249.99, status:'Paid',     date:'2026-02-12' },
      { id:'SP-10481', customer:'M. Hasan',     amount:89.49,  status:'Pending',  date:'2026-02-12' },
      { id:'SP-10480', customer:'Sara Khan',    amount:129.00, status:'Shipped',  date:'2026-02-11' },
      { id:'SP-10479', customer:'T. Ahmed',     amount:39.99,  status:'Refunded', date:'2026-02-11' },
    ];

    const $ = (id) => document.getElementById(id);
    const fmt = (n) => new Intl.NumberFormat('en-US',{style:'currency',currency:'USD'}).format(n);

    const badge = (s) => ({
      Paid:     'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
      Pending:  'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
      Shipped:  'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
      Refunded: 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-200'
    }[s] || 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200');

    function render() {
      const q = ($('orderSearch').value || '').toLowerCase().trim();
      const st = $('orderStatus').value;

      let rows = orders.filter(o => {
        const okStatus = st === 'all' || o.status === st;
        const okQuery = !q || o.id.toLowerCase().includes(q) || o.customer.toLowerCase().includes(q);
        return okStatus && okQuery;
      });

      $('ordersBody').innerHTML = rows.map(o => `
        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
          <td class="py-3 pr-3 font-semibold">${o.id}</td>
          <td class="py-3 pr-3">${o.customer}</td>
          <td class="py-3 pr-3 font-semibold">${fmt(o.amount)}</td>
          <td class="py-3 pr-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold ${badge(o.status)}">${o.status}</span></td>
          <td class="py-3 pr-3 text-slate-600 dark:text-slate-300">${o.date}</td>
          <td class="py-3 text-right">
            <button class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
                    onclick="alert('Open: ${o.id}')">View</button>
          </td>
        </tr>
      `).join('');
    }

    $('orderSearch').addEventListener('input', render);
    $('orderStatus').addEventListener('change', render);
    render();
  </script>
@endsection
