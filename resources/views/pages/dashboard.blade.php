@extends('layouts.app')

@section('title','Dashboard')
@section('subtitle','Dashboard')
@section('pageTitle','Overview')
@section('pageDesc','Real-time store performance and quick actions.')

@section('pageActions')
  <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold shadow-sm hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
    Export
  </button>
  <button class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-soft hover:bg-indigo-700">
    Create
  </button>
@endsection

@section('content')
  <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div class="text-sm text-slate-500 dark:text-slate-400">Revenue</div>
        <span class="rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">+12%</span>
      </div>
      <div class="mt-2 text-2xl font-bold">$48,290</div>
      <div class="mt-3 h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800">
        <div class="h-2 w-[72%] rounded-full bg-indigo-600"></div>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div class="text-sm text-slate-500 dark:text-slate-400">Orders</div>
        <span class="rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">+8%</span>
      </div>
      <div class="mt-2 text-2xl font-bold">1,284</div>
      <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">Avg. fulfillment 1.8 days</div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div class="text-sm text-slate-500 dark:text-slate-400">Conversion</div>
        <span class="rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">2.9%</span>
      </div>
      <div class="mt-2 text-2xl font-bold">3.42%</div>
      <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">Cart abandonment 61%</div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div class="text-sm text-slate-500 dark:text-slate-400">Customers</div>
        <span class="rounded-full bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">+21%</span>
      </div>
      <div class="mt-2 text-2xl font-bold">9,502</div>
      <div class="mt-3 text-xs text-slate-500 dark:text-slate-400">New this week 312</div>
    </div>
  </section>

  <section class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-lg font-semibold">Recent Orders</div>
      <div class="mt-3 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <tr>
              <th class="py-3 pr-3">Order</th>
              <th class="py-3 pr-3">Customer</th>
              <th class="py-3 pr-3">Amount</th>
              <th class="py-3 pr-3">Status</th>
              <th class="py-3 pr-3">Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
              <td class="py-3 pr-3 font-semibold">SP-10482</td>
              <td class="py-3 pr-3">Amina Rahman</td>
              <td class="py-3 pr-3 font-semibold">$249.99</td>
              <td class="py-3 pr-3"><span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">Paid</span></td>
              <td class="py-3 pr-3 text-slate-600 dark:text-slate-300">2026-02-12</td>
            </tr>
            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
              <td class="py-3 pr-3 font-semibold">SP-10481</td>
              <td class="py-3 pr-3">M. Hasan</td>
              <td class="py-3 pr-3 font-semibold">$89.49</td>
              <td class="py-3 pr-3"><span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">Pending</span></td>
              <td class="py-3 pr-3 text-slate-600 dark:text-slate-300">2026-02-12</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-lg font-semibold">Quick Insights</div>
      <div class="mt-4 space-y-3">
        <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/40">
          <div class="font-semibold text-sm">Low Stock</div>
          <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">4 SKUs below threshold.</div>
        </div>
        <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/40">
          <div class="font-semibold text-sm">Shipping Delays</div>
          <div class="text-sm text-slate-500 dark:text-slate-400 mt-1">6 orders flagged by carrier.</div>
        </div>
      </div>
    </div>
  </section>
@endsection
