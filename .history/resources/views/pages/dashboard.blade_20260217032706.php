@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle','Overview')
@section('pageTitle','Dashboard')
@section('pageDesc','Sales, orders, customers and stock summary.')

@section('content')
<div class="space-y-5">

  {{-- Top Row: KPI --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    {{-- Revenue --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Revenue (This Month)</div>
          <div class="mt-1 text-2xl font-extrabold">৳{{ number_format($kpi['revenue'],2) }}</div>
          <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">From {{ $from->format('M d') }} to {{ now()->format('M d') }}</div>
        </div>
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 1v22" />
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
          </svg>
        </div>
      </div>
    </div>

    {{-- Orders --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Orders (This Month)</div>
          <div class="mt-1 text-2xl font-extrabold">{{ $kpi['orders'] }}</div>
          <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Avg order value: ৳{{ number_format($kpi['aov'],2) }}</div>
        </div>
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 6h15l-1.5 9h-13z" />
            <path d="M6 6 5 3H2" />
            <path d="M9 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
            <path d="M18 22a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" />
          </svg>
        </div>
      </div>
    </div>

    {{-- Customers --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs text-slate-500 dark:text-slate-400">New Customers (This Month)</div>
          <div class="mt-1 text-2xl font-extrabold">{{ $newCustomers }}</div>
          <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Growing your customer base</div>
        </div>
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
          </svg>
        </div>
      </div>
    </div>

    {{-- Payments --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-xs text-slate-500 dark:text-slate-400"> Payments</div>
          <div class="mt-1 text-2xl font-extrabold">{{ $pendingPayments }}</div>
          <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">COD & mobile payments</div>
        </div>
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 1v22" />
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6" />
          </svg>
        </div>
      </div>
    </div>
  </div>

  {{-- Main Grid --}}
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
    {{-- Chart --}}
    <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-start justify-between gap-3">
        <div>
          <div class="font-semibold">Revenue & Orders Trend</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Daily trend (current month)</div>
        </div>

        <div class="flex gap-2">
          <a href="{{ route('crm.orders') }}"
            class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
            View Orders
          </a>
          <a href="{{ route('analytics') }}"
            class="rounded-2xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
            Open Analytics
          </a>
        </div>
      </div>

      <div class="mt-3">
        <canvas id="trendChart" height="110"></canvas>
      </div>

      <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Tax</div>
          <div class="font-semibold">৳{{ number_format($kpi['tax'],2) }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Discount</div>
          <div class="font-semibold">৳{{ number_format($kpi['discount'],2) }}</div>
        </div>
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">AOV</div>
          <div class="font-semibold">৳{{ number_format($kpi['aov'],2) }}</div>
        </div>
      </div>
    </div>

    {{-- Quick Actions + Low Stock --}}
    <div class="space-y-4">
      {{-- Quick Actions --}}
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
        <div class="font-semibold">Quick Actions</div>
        <div class="mt-3 grid grid-cols-1 gap-2">
          <a href="{{ route('crm.orders.create') }}"
             class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-slate-900 text-center">
            + Create Order
          </a>
          <a href="{{ route('products.create') }}"
             class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800 text-center">
            + Add Product
          </a>
          <a href="{{ route('products.categories') }}"
             class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800 text-center">
            Manage Categories
          </a>
          <a href="{{ route('pos') }}"
             class="rounded-2xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200 text-center">
            Open POS
          </a>
        </div>
      </div>

      {{-- Low Stock --}}
      <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
        <div class="flex items-center justify-between">
          <div class="font-semibold">Low Stock</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">≤ {{ $lowStockThreshold }}</div>
        </div>

        <div class="mt-3 space-y-2 text-sm">
          @forelse($lowStock as $p)
            <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-800">
              <div>
                <div class="font-semibold">{{ $p->name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">SKU: {{ $p->sku ?? '—' }}</div>
              </div>
              <div class="text-right">
                <div class="font-bold {{ ((int)$p->stock<=0)?'text-rose-600':'' }}">{{ (int)$p->stock }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">units</div>
              </div>
            </div>
          @empty
            <div class="text-sm text-slate-500 dark:text-slate-400">No low stock products 🎉</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- Latest Orders --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-center justify-between gap-3">
      <div>
        <div class="font-semibold">Latest Orders</div>
        <div class="text-xs text-slate-500 dark:text-slate-400">Recent orders overview</div>
      </div>
      <a href="{{ route('crm.orders') }}"
         class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        View all
      </a>
    </div>

    <div class="mt-3 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs text-slate-500 dark:text-slate-400">
            <th class="pb-2">Order</th>
            <th class="pb-2">Customer</th>
            <th class="pb-2">Status</th>
            <th class="pb-2">Payment</th>
            <th class="pb-2 text-right">Total</th>
          </tr>
        </thead>
        <tbody>
          @forelse($latestOrders as $o)
            @php
              $badge = match($o->status){
                'complete' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-200',
                'hold' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-200',
                default => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-200'
              };
              $pm = strtoupper($o->payment?->method ?? '—');
            @endphp
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-3 pr-3">
                <div class="font-semibold">{{ $o->order_number }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ optional($o->created_at)->format('M d, Y h:i A') }}</div>
              </td>
              <td class="py-3 pr-3">
                <div class="font-semibold">{{ $o->customer?->name ?? 'Guest' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $o->customer?->phone ?? '—' }}</div>
              </td>
              <td class="py-3 pr-3">
                <span class="inline-flex rounded-xl px-3 py-1 text-xs font-semibold {{ $badge }}">
                  {{ ucfirst($o->status) }}
                </span>
              </td>
              <td class="py-3 pr-3">
                <div class="font-semibold">{{ $pm }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                  Paid: ৳{{ number_format((float)($o->payment?->amount_paid ?? 0),2) }}
                </div>
              </td>
              <td class="py-3 text-right font-bold">৳{{ number_format((float)$o->total,2) }}</td>
            </tr>
          @empty
            <tr>
              <td class="py-4 text-slate-500 dark:text-slate-400" colspan="5">No recent orders.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const labels = @json($labels);
  const ordersSeries = @json($ordersSeries);
  const revenueSeries = @json($revenueSeries);

  const ctx = document.getElementById('trendChart');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        { label: 'Revenue', data: revenueSeries, yAxisID: 'y1', tension: 0.25 },
        { label: 'Orders', data: ordersSeries, yAxisID: 'y', tension: 0.25 },
      ]
    },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      scales: {
        y: { beginAtZero: true, title: { display: true, text: 'Orders' } },
        y1: { beginAtZero: true, position: 'right', title: { display: true, text: 'Revenue' }, grid: { drawOnChartArea: false } }
      }
    }
  });
})();
</script>
@endsection
