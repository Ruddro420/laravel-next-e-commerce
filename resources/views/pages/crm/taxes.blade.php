@extends('layouts.app')
@section('title','Taxes')
@section('subtitle','CRM')
@section('pageTitle','Taxes')
@section('pageDesc','Manage VAT/GST tax rates for orders.')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- LEFT: Add Tax --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="text-lg font-semibold">Add Tax Rate</div>
    <div class="text-sm text-slate-500 dark:text-slate-400">Exclusive or inclusive tax mode.</div>

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

    <form class="mt-4 space-y-4" method="POST" action="{{ route('crm.taxes.store') }}">
      @csrf

      <div>
        <label class="text-sm font-semibold">Name</label>
        <input name="name" required placeholder="VAT"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Rate (%)</label>
        <input name="rate" type="number" step="0.01" min="0" max="99.99" value="0" required
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Mode</label>
        <select name="mode"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <option value="exclusive">Exclusive (adds on top)</option>
          <option value="inclusive">Inclusive (inside price)</option>
        </select>
      </div>

      <button class="w-full rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Tax Rate
      </button>
    </form>
  </div>

  {{-- RIGHT: List --}}
  <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="text-lg font-semibold">Tax Rates</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">Used on order creation.</div>
      </div>

      <form class="flex gap-2" method="GET">
        <input name="q" value="{{ $q ?? '' }}"
          class="w-full sm:w-72 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search by name..." />
        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Search
        </button>
      </form>
    </div>

    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 pr-3">Name</th>
            <th class="py-3 pr-3">Rate</th>
            <th class="py-3 pr-3">Mode</th>
            <th class="py-3 pr-3">Status</th>
            <th class="py-3 pr-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($taxRates as $t)
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-3 pr-3 font-semibold">{{ $t->name }}</td>
              <td class="py-3 pr-3">{{ number_format($t->rate,2) }}%</td>
              <td class="py-3 pr-3 capitalize">{{ $t->mode }}</td>
              <td class="py-3 pr-3">
                @php
                  $badge = $t->is_active
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200'
                    : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200';
                @endphp
                <span class="inline-flex rounded-xl border px-2 py-1 text-xs font-semibold {{ $badge }}">
                  {{ $t->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3 pr-3">
                <div class="flex gap-3">
                  <button type="button"
                    class="text-xs font-semibold text-slate-700 dark:text-slate-200"
                    data-edit-btn
                    data-id="{{ $t->id }}"
                    data-name="{{ e($t->name) }}"
                    data-rate="{{ $t->rate }}"
                    data-mode="{{ $t->mode }}"
                    data-active="{{ $t->is_active ? '1':'0' }}">
                    Edit
                  </button>

                  <form method="POST" action="{{ route('crm.taxes.destroy',$t) }}" data-delete-form class="inline">
                    @csrf @method('DELETE')
                    <button class="text-xs font-semibold text-rose-600" type="submit">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">No tax rates found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $taxRates->links() }}</div>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="fixed inset-0 z-[60] hidden">
  <div class="absolute inset-0 bg-slate-900/40" data-modal-close></div>
  <div class="absolute left-1/2 top-1/2 w-[92%] max-w-xl -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-center justify-between">
      <div class="text-lg font-semibold">Edit Tax Rate</div>
      <button class="h-10 w-10 rounded-xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
        type="button" data-modal-close>✕</button>
    </div>

    <form id="editForm" class="mt-4 space-y-4" method="POST">
      @csrf @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Name</label>
          <input id="eName" name="name" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div class="flex items-center gap-2 pt-7">
          <input id="eActive" name="is_active" type="checkbox" class="h-4 w-4">
          <label for="eActive" class="text-sm font-semibold">Active</label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Rate (%)</label>
          <input id="eRate" name="rate" type="number" step="0.01" min="0" max="99.99" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Mode</label>
          <select id="eMode" name="mode" class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="exclusive">Exclusive</option>
            <option value="inclusive">Inclusive</option>
          </select>
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
      if(!confirm('Delete this tax rate?')) e.preventDefault();
    });
  });

  const modal = document.getElementById('editModal');
  const form = document.getElementById('editForm');

  const eName = document.getElementById('eName');
  const eRate = document.getElementById('eRate');
  const eMode = document.getElementById('eMode');
  const eActive = document.getElementById('eActive');

  function openModal(){ modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function closeModal(){ modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

  document.querySelectorAll('[data-modal-close]').forEach(btn=> btn.addEventListener('click', closeModal));
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });

  document.querySelectorAll('[data-edit-btn]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.dataset.id;
      eName.value = btn.dataset.name || '';
      eRate.value = btn.dataset.rate || 0;
      eMode.value = btn.dataset.mode || 'exclusive';
      eActive.checked = (btn.dataset.active === '1');

      form.action = "{{ url('/crm/taxes') }}/" + id;
      openModal();
    });
  });
})();
</script>
@endsection
