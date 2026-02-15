@extends('layouts.app')
@section('title','POS Holds')
@section('subtitle','POS')
@section('pageTitle','Hold Sales')
@section('pageDesc','Saved carts for later checkout.')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  @if(session('success'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
    <div>
      <div class="font-semibold">Hold List</div>
      <div class="text-xs text-slate-500 dark:text-slate-400">Click “Open” to load cart into POS.</div>
    </div>

    <form class="flex gap-2" method="GET">
      <input name="q" value="{{ $q }}"
        class="w-64 rounded-2xl border border-slate-200 px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800"
        placeholder="Search holds..." />
      <button class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">Search</button>
    </form>
  </div>

  <div class="mt-4 overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-slate-500 dark:text-slate-400">
        <tr>
          <th class="py-2">Ref</th>
          <th class="py-2">Customer</th>
          <th class="py-2">Subtotal</th>
          <th class="py-2">Total</th>
          <th class="py-2">Created</th>
          <th class="py-2 text-right">Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($holds as $h)
          <tr class="border-t border-slate-100 dark:border-slate-800">
            <td class="py-3 font-semibold">{{ $h->ref }}</td>
            <td class="py-3">{{ $h->customer?->name ?? 'Walk-in' }}</td>
            <td class="py-3">৳{{ number_format($h->subtotal,2) }}</td>
            <td class="py-3 font-semibold">৳{{ number_format($h->total,2) }}</td>
            <td class="py-3 text-slate-500 dark:text-slate-400">{{ $h->created_at->format('Y-m-d H:i') }}</td>
            <td class="py-3 text-right space-x-2">
              <a class="rounded-2xl bg-indigo-600 px-3 py-2 text-xs font-semibold text-white"
                 href="{{ route('pos', ['hold'=>$h->id]) }}">
                Open
              </a>

              <form class="inline" method="POST" action="{{ route('pos.holds.delete',$h) }}"
                    onsubmit="return confirm('Delete this hold?')">
                @csrf @method('DELETE')
                <button class="rounded-2xl border border-slate-200 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800" type="submit">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-6 text-center text-slate-500 dark:text-slate-400">No holds found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">{{ $holds->links() }}</div>
</div>
@endsection
