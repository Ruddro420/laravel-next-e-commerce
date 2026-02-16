@extends('layouts.app')
@section('title','Analytics')
@section('subtitle','Reports')
@section('pageTitle','Analytics & Reports')
@section('pageDesc','Daily, weekly, monthly, yearly and custom date range.')

@section('content')
<div class="space-y-5">

  {{-- Filter --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <form method="GET" action="{{ route('analytics') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
      <div class="md:col-span-3">
        <label class="text-sm font-semibold">Period</label>
        <select name="period" id="period"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="daily" {{ $period==='daily'?'selected':'' }}>Daily</option>
          <option value="weekly" {{ $period==='weekly'?'selected':'' }}>Weekly</option>
          <option value="monthly" {{ $period==='monthly'?'selected':'' }}>Monthly</option>
          <option value="yearly" {{ $period==='yearly'?'selected':'' }}>Yearly</option>
          <option value="range" {{ $period==='range'?'selected':'' }}>Custom Range</option>
        </select>
      </div>

      <div class="md:col-span-3" id="dailyWrap">
        <label class="text-sm font-semibold">Date</label>
        <input type="date" name="date" value="{{ request('date',$from) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div class="md:col-span-3" id="monthWrap">
        <label class="text-sm font-semibold">Month</label>
        <input type="month" name="month" value="{{ request('month', substr($from,0,7)) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div class="md:col-span-3" id="yearWrap">
        <label class="text-sm font-semibold">Year</label>
        <input type="number" name="year" min="2000" max="2100" value="{{ request('year', substr($from,0,4)) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div class="md:col-span-3" id="rangeFromWrap">
        <label class="text-sm font-semibold">From</label>
        <input type="date" name="from" value="{{ request('from',$from) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div class="md:col-span-3" id="rangeToWrap">
        <label class="text-sm font-semibold">To</label>
        <input type="date" name="to" value="{{ request('to',$to) }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div class="md:col-span-3 flex gap-2">
        <button
          class="w-full rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
          Apply
        </button>
        <a target="_blank" href="{{ route('analytics.export.csv', request()->query()) }}"
          class="w-full text-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Export CSV
        </a>
      </div>

      <div class="md:col-span-12 text-xs text-slate-500 dark:text-slate-400">
        Showing: <b>{{ $from }}</b> to <b>{{ $to }}</b>
      </div>
    </form>
  </div>

  {{-- KPI --}}
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">Revenue</div>
      <div class="mt-1 text-2xl font-bold">৳{{ number_format($salesTotal,2) }}</div>
      <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Subtotal ৳{{ number_format($salesSubtotal,2) }}</div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">Orders</div>
      <div class="mt-1 text-2xl font-bold">{{ $ordersCount }}</div>
      <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">AOV ৳{{ number_format($aov,2) }}</div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">Tax + Shipping</div>
      <div class="mt-1 text-2xl font-bold">৳{{ number_format($taxTotal + $shippingTotal,2) }}</div>
      <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Tax ৳{{ number_format($taxTotal,2) }} • Ship ৳{{ number_format($shippingTotal,2) }}</div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-xs text-slate-500 dark:text-slate-400">Discount + New Customers</div>
      <div class="mt-1 text-2xl font-bold">৳{{ number_format($couponTotal,2) }}</div>
      <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">New customers: {{ $newCustomers }}</div>
    </div>
  </div>

  {{-- Charts --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div>
          <div class="font-semibold">Revenue & Orders Trend</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">Auto-filled series for better chart readability.</div>
        </div>
      </div>
      <div class="mt-3">
        <canvas id="trendChart" height="110"></canvas>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Orders by Status</div>
      <div class="mt-3 space-y-2 text-sm">
        @forelse($statusBreakdown as $s)
          <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-800">
            <div class="font-semibold capitalize">{{ $s->status }}</div>
            <div class="text-right">
              <div class="font-semibold">{{ (int)$s->c }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">৳{{ number_format((float)$s->s,2) }}</div>
            </div>
          </div>
        @empty
          <div class="text-sm text-slate-500 dark:text-slate-400">No data.</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Tables --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Top Products</div>
      <div class="mt-3 overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-slate-500 dark:text-slate-400">
              <th class="pb-2">Product</th>
              <th class="pb-2">Qty</th>
              <th class="pb-2 text-right">Sales</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topProducts as $p)
              <tr class="border-t border-slate-100 dark:border-slate-800">
                <td class="py-2 pr-3 font-semibold">{{ $p->product_name }}</td>
                <td class="py-2 pr-3">{{ (int)$p->qty_sum }}</td>
                <td class="py-2 text-right font-semibold">৳{{ number_format((float)$p->sales_sum,2) }}</td>
              </tr>
            @empty
              <tr><td class="py-3 text-slate-500 dark:text-slate-400" colspan="3">No data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Top Customers</div>
      <div class="mt-3 overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs text-slate-500 dark:text-slate-400">
              <th class="pb-2">Customer</th>
              <th class="pb-2">Orders</th>
              <th class="pb-2 text-right">Spent</th>
            </tr>
          </thead>
          <tbody>
            @forelse($topCustomers as $c)
              <tr class="border-t border-slate-100 dark:border-slate-800">
                <td class="py-2 pr-3 font-semibold">{{ $c->name }}</td>
                <td class="py-2 pr-3">{{ (int)$c->orders_count }}</td>
                <td class="py-2 text-right font-semibold">৳{{ number_format((float)$c->spent,2) }}</td>
              </tr>
            @empty
              <tr><td class="py-3 text-slate-500 dark:text-slate-400" colspan="3">No data.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Payments Summary</div>
      <div class="mt-3 space-y-2 text-sm">
        @forelse($paymentsBreakdown as $p)
          <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-800">
            <div class="font-semibold uppercase">{{ $p->method }}</div>
            <div class="text-right">
              <div class="font-semibold">৳{{ number_format((float)$p->paid,2) }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ (int)$p->c }} payments</div>
            </div>
          </div>
        @empty
          <div class="text-sm text-slate-500 dark:text-slate-400">No data.</div>
        @endforelse
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Low Stock (≤ {{ $lowStockThreshold }})</div>
      <div class="mt-3 space-y-2 text-sm">
        @forelse($lowStock as $p)
          <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2 dark:border-slate-800">
            <div class="font-semibold">{{ $p->name }}</div>
            <div class="text-right">
              <div class="font-semibold">{{ (int)$p->stock }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">SKU: {{ $p->sku ?? '—' }}</div>
            </div>
          </div>
        @empty
          <div class="text-sm text-slate-500 dark:text-slate-400">No low-stock items.</div>
        @endforelse
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  const period = document.getElementById('period');

  const dailyWrap = document.getElementById('dailyWrap');
  const monthWrap = document.getElementById('monthWrap');
  const yearWrap = document.getElementById('yearWrap');
  const rangeFromWrap = document.getElementById('rangeFromWrap');
  const rangeToWrap = document.getElementById('rangeToWrap');

  function syncFilters(){
    const p = period.value;

    dailyWrap.style.display = (p === 'daily') ? 'block' : 'none';
    monthWrap.style.display = (p === 'monthly') ? 'block' : 'none';
    yearWrap.style.display = (p === 'yearly') ? 'block' : 'none';

    const showRange = (p === 'range' || p === 'weekly');
    rangeFromWrap.style.display = showRange ? 'block' : 'none';
    rangeToWrap.style.display = showRange ? 'block' : 'none';

    // weekly uses from/to too (you can still tweak)
    if(p === 'weekly'){
      monthWrap.style.display = 'none';
      yearWrap.style.display = 'none';
      dailyWrap.style.display = 'none';
    }
  }
  period.addEventListener('change', syncFilters);
  syncFilters();

  // Chart
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
