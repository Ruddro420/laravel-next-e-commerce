@extends('layouts.app')
@section('title','Stock Details')
@section('subtitle','Products')
@section('pageTitle','Stock Details')
@section('pageDesc',$product->name)

@section('content')
<div class="space-y-6">

  @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- Header --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <div class="text-lg font-semibold">{{ $product->name }}</div>
        <div class="text-xs text-slate-500 dark:text-slate-400">
          SKU: {{ $product->sku ?? '—' }} • Type: {{ ucfirst($product->product_type) }} • ID: {{ $product->id }}
        </div>
      </div>

      <div class="flex items-center gap-3">
        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-800">
          <div class="text-xs text-slate-500 dark:text-slate-400">Current Stock</div>
          <div class="text-xl font-bold">{{ (int)($product->stock ?? 0) }}</div>
        </div>

        <a href="{{ route('stock') }}"
          class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Back
        </a>
      </div>
    </div>

    {{-- Adjust form --}}
    <form method="POST" action="{{ route('stock.adjust',$product) }}" class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-4">
      @csrf

      <div>
        <label class="text-sm font-semibold">Action</label>
        <select name="type"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="in">Stock IN (+)</option>
          <option value="out">Stock OUT (-)</option>
          <option value="adjust">Set Exact Stock</option>
        </select>
      </div>

      <div>
        <label class="text-sm font-semibold">Quantity</label>
        <input name="qty" type="number" min="0" required
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
          For “Set Exact Stock”, enter final value.
        </div>
      </div>

      <div>
        <label class="text-sm font-semibold">Reason</label>
        <input name="reason"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="Purchase / Damage / Manual..." />
      </div>

      <div class="flex items-end">
        <button
          class="w-full rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-white dark:text-slate-900">
          Update Stock
        </button>
      </div>

      <div class="md:col-span-4">
        <label class="text-sm font-semibold">Note</label>
        <textarea name="note" rows="3"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
      </div>
    </form>
  </div>

  {{-- History --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-soft dark:bg-slate-900 dark:border-slate-800 overflow-hidden">
    <div class="p-4 font-semibold">Stock History</div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-950/40">
          <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Type</th>
            <th class="py-3 px-4">Qty</th>
            <th class="py-3 px-4">Before</th>
            <th class="py-3 px-4">After</th>
            <th class="py-3 px-4">Reason</th>
            <th class="py-3 px-4">Order</th>
          </tr>
        </thead>

        <tbody>
          @forelse($movements as $m)
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-3 px-4">{{ $m->created_at->format('d M Y, h:i A') }}</td>

              <td class="py-3 px-4">
                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold
                  {{ $m->type==='in' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200'
                    : ($m->type==='out' ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200'
                    : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200') }}">
                  {{ strtoupper($m->type) }}
                </span>
              </td>

              <td class="py-3 px-4 font-semibold">{{ $m->qty }}</td>
              <td class="py-3 px-4">{{ $m->before_stock }}</td>
              <td class="py-3 px-4 font-semibold">{{ $m->after_stock }}</td>

              <td class="py-3 px-4">
                <div class="font-semibold">{{ $m->reason ?? '—' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                  {{ $m->note ? \Illuminate\Support\Str::limit($m->note, 80) : '' }}
                </div>
              </td>

              <td class="py-3 px-4">
                {{ $m->order_id ? '#'.$m->order_id : '—' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="py-10 text-center text-slate-500 dark:text-slate-400">
                No movements yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
      {{ $movements->links() }}
    </div>
  </div>

</div>
@endsection
