@extends('layouts.app')
@section('title','Coupons')
@section('subtitle','CRM')
@section('pageTitle','Coupons')
@section('pageDesc','Create and manage coupon codes, limits and validity.')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- LEFT: Add Coupon --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="text-lg font-semibold">Add Coupon</div>
    <div class="text-sm text-slate-500 dark:text-slate-400">Fixed or percent discounts.</div>

    @if($errors->any())
      <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
        <div class="font-semibold mb-1">Fix these:</div>
        <ul class="list-disc pl-5 space-y-1">
          @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
        </ul>
      </div>
    @endif

    @if(session('success'))
      <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
        {{ session('success') }}
      </div>
    @endif

    <form class="mt-4 space-y-4" method="POST" action="{{ route('crm.coupons.store') }}">
      @csrf

      <div>
        <label class="text-sm font-semibold">Code</label>
        <input name="code" value="{{ old('code') }}" required placeholder="SAVE10"
          class="mt-2 w-full uppercase rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-semibold">Type</label>
          <select name="type"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="fixed">Fixed</option>
            <option value="percent">Percent</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-semibold">Value</label>
          <input name="value" type="number" step="0.01" min="0" value="{{ old('value',0) }}" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-semibold">Min Order</label>
          <input name="min_order_amount" type="number" step="0.01" min="0" value="{{ old('min_order_amount',0) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Usage Limit</label>
          <input name="usage_limit" type="number" min="1" value="{{ old('usage_limit') }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Optional" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
          <label class="text-sm font-semibold">Starts At</label>
          <input name="starts_at" type="datetime-local" value="{{ old('starts_at') }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Expires At</label>
          <input name="expires_at" type="datetime-local" value="{{ old('expires_at') }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input id="is_active" name="is_active" type="checkbox" class="h-4 w-4" checked>
        <label for="is_active" class="text-sm font-semibold">Active</label>
      </div>

      <button class="w-full rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Coupon
      </button>
    </form>
  </div>

  {{-- RIGHT: List --}}
  <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="text-lg font-semibold">Coupons</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">Edit coupon rules anytime.</div>
      </div>

      <form class="flex gap-2" method="GET">
        <input name="q" value="{{ $q ?? '' }}"
          class="w-full sm:w-72 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search by code..." />
        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Search
        </button>
      </form>
    </div>

    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 pr-3">Code</th>
            <th class="py-3 pr-3">Rule</th>
            <th class="py-3 pr-3">Validity</th>
            <th class="py-3 pr-3">Usage</th>
            <th class="py-3 pr-3">Status</th>
            <th class="py-3 pr-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($coupons as $c)
            @php
              $now = now();
              $expired = $c->expires_at && $now->gt($c->expires_at);
              $notStarted = $c->starts_at && $now->lt($c->starts_at);
              $remaining = $c->usage_limit ? max(0, $c->usage_limit - $c->used_count) : null;
            @endphp
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-3 pr-3">
                <div class="font-semibold">{{ $c->code }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Min: {{ number_format($c->min_order_amount,2) }}</div>
              </td>
              <td class="py-3 pr-3">
                <div class="font-semibold">
                  @if($c->type === 'percent')
                    {{ rtrim(rtrim(number_format($c->value,2), '0'), '.') }}%
                  @else
                    {{ number_format($c->value,2) }}
                  @endif
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ ucfirst($c->type) }}</div>
              </td>
              <td class="py-3 pr-3 text-xs text-slate-600 dark:text-slate-300">
                <div>{{ $c->starts_at ? $c->starts_at->format('Y-m-d H:i') : '—' }}</div>
                <div>{{ $c->expires_at ? $c->expires_at->format('Y-m-d H:i') : '—' }}</div>
              </td>
              <td class="py-3 pr-3 text-xs">
                <div>Used: <span class="font-semibold">{{ $c->used_count }}</span></div>
                <div>Limit: <span class="font-semibold">{{ $c->usage_limit ?? '∞' }}</span></div>
                @if(!is_null($remaining))
                  <div>Remaining: <span class="font-semibold">{{ $remaining }}</span></div>
                @endif
              </td>
              <td class="py-3 pr-3">
                @php
                  $badge = (!$c->is_active || $expired)
                    ? 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200'
                    : ($notStarted
                      ? 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200'
                      : 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200');
                  $label = (!$c->is_active || $expired) ? 'Inactive/Expired' : ($notStarted ? 'Scheduled' : 'Active');
                @endphp
                <span class="inline-flex rounded-xl border px-2 py-1 text-xs font-semibold {{ $badge }}">{{ $label }}</span>
              </td>
              <td class="py-3 pr-3">
                <div class="flex gap-3">
                  <button type="button"
                    class="text-xs font-semibold text-slate-700 dark:text-slate-200"
                    data-edit-btn
                    data-id="{{ $c->id }}"
                    data-code="{{ e($c->code) }}"
                    data-type="{{ $c->type }}"
                    data-value="{{ $c->value }}"
                    data-min="{{ $c->min_order_amount }}"
                    data-limit="{{ $c->usage_limit ?? '' }}"
                    data-starts="{{ $c->starts_at ? $c->starts_at->format('Y-m-d\TH:i') : '' }}"
                    data-expires="{{ $c->expires_at ? $c->expires_at->format('Y-m-d\TH:i') : '' }}"
                    data-active="{{ $c->is_active ? '1':'0' }}">
                    Edit
                  </button>

                  <form method="POST" action="{{ route('crm.coupons.destroy',$c) }}" data-delete-form class="inline">
                    @csrf @method('DELETE')
                    <button class="text-xs font-semibold text-rose-600" type="submit">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="py-10 text-center text-slate-500 dark:text-slate-400">No coupons found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $coupons->links() }}</div>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="fixed inset-0 z-[60] hidden">
  <div class="absolute inset-0 bg-slate-900/40" data-modal-close></div>
  <div class="absolute left-1/2 top-1/2 w-[92%] max-w-2xl -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-center justify-between">
      <div class="text-lg font-semibold">Edit Coupon</div>
      <button class="h-10 w-10 rounded-xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
        type="button" data-modal-close>✕</button>
    </div>

    <form id="editForm" class="mt-4 space-y-4" method="POST">
      @csrf @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Code</label>
          <input id="eCode" name="code" required class="mt-2 w-full uppercase rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div class="flex items-center gap-2 pt-7">
          <input id="eActive" name="is_active" type="checkbox" class="h-4 w-4">
          <label for="eActive" class="text-sm font-semibold">Active</label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Type</label>
          <select id="eType" name="type" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="fixed">Fixed</option>
            <option value="percent">Percent</option>
          </select>
        </div>
        <div>
          <label class="text-sm font-semibold">Value</label>
          <input id="eValue" name="value" type="number" step="0.01" min="0" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Min Order</label>
          <input id="eMin" name="min_order_amount" type="number" step="0.01" min="0"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Usage Limit</label>
          <input id="eLimit" name="usage_limit" type="number" min="1"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Starts At</label>
          <input id="eStarts" name="starts_at" type="datetime-local"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Expires At</label>
          <input id="eExpires" name="expires_at" type="datetime-local"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" data-modal-close class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Cancel
        </button>
        <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
          Update
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  document.querySelectorAll('[data-delete-form]').forEach(f=>{
    f.addEventListener('submit', (e)=>{
      if(!confirm('Delete this coupon?')) e.preventDefault();
    });
  });

  const modal = document.getElementById('editModal');
  const form = document.getElementById('editForm');

  const eCode = document.getElementById('eCode');
  const eType = document.getElementById('eType');
  const eValue = document.getElementById('eValue');
  const eMin = document.getElementById('eMin');
  const eLimit = document.getElementById('eLimit');
  const eStarts = document.getElementById('eStarts');
  const eExpires = document.getElementById('eExpires');
  const eActive = document.getElementById('eActive');

  function openModal(){ modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function closeModal(){ modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

  document.querySelectorAll('[data-modal-close]').forEach(btn=> btn.addEventListener('click', closeModal));
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });

  document.querySelectorAll('[data-edit-btn]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.dataset.id;
      eCode.value = btn.dataset.code || '';
      eType.value = btn.dataset.type || 'fixed';
      eValue.value = btn.dataset.value || 0;
      eMin.value = btn.dataset.min || 0;
      eLimit.value = btn.dataset.limit || '';
      eStarts.value = btn.dataset.starts || '';
      eExpires.value = btn.dataset.expires || '';
      eActive.checked = (btn.dataset.active === '1');

      form.action = "{{ url('/crm/coupons') }}/" + id;
      openModal();
    });
  });
})();
</script>
@endsection
