@extends('layouts.app')
@section('title','Customers')
@section('subtitle','CRM')
@section('pageTitle','Customers')
@section('pageDesc','Manage customers, addresses and activity status.')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- LEFT: Add Customer --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-start justify-between">
      <div>
        <div class="text-lg font-semibold">Add Customer</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">Create a new customer.</div>
      </div>
    </div>

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

    <form class="mt-4 space-y-4" method="POST" action="{{ route('customers.store') }}">
      @csrf

      <div>
        <label class="text-sm font-semibold">Name</label>
        <input name="name" value="{{ old('name') }}" required
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Email</label>
        <input name="email" value="{{ old('email') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Phone</label>
        <input name="phone" value="{{ old('phone') }}"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold">Billing Address</label>
        <textarea name="billing_address" rows="3"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('billing_address') }}</textarea>
      </div>

      <div>
        <label class="text-sm font-semibold">Shipping Address</label>
        <textarea name="shipping_address" rows="3"
          class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">{{ old('shipping_address') }}</textarea>
      </div>

      <div class="flex items-center gap-2">
        <input id="is_active" name="is_active" type="checkbox" class="h-4 w-4" checked>
        <label for="is_active" class="text-sm font-semibold">Active</label>
      </div>

      <button class="w-full rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Customer
      </button>
    </form>
  </div>

  {{-- RIGHT: List Customers --}}
  <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="text-lg font-semibold">Customer List</div>
        <div class="text-sm text-slate-500 dark:text-slate-400">Search, edit or delete customers.</div>
      </div>

      <form class="flex gap-2" method="GET">
        <input name="q" value="{{ $q ?? '' }}"
          class="w-full sm:w-72 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search name/email/phone..." />
        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Search
        </button>
      </form>
    </div>

    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 pr-3">Customer</th>
            <th class="py-3 pr-3">Contacts</th>
            <th class="py-3 pr-3">Status</th>
            <th class="py-3 pr-3">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($customers as $c)
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-3 pr-3">
                <div class="font-semibold">{{ $c->name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $c->id }}</div>
              </td>
              <td class="py-3 pr-3">
                <div>{{ $c->email ?? '—' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $c->phone ?? '—' }}</div>
              </td>
              <td class="py-3 pr-3">
                @php
                  $badge = $c->is_active
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200'
                    : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200';
                @endphp
                <span class="inline-flex rounded-xl border px-2 py-1 text-xs font-semibold {{ $badge }}">
                  {{ $c->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-3 pr-3">
                <div class="flex gap-3">
                  <button type="button"
                    class="text-xs font-semibold text-slate-700 dark:text-slate-200"
                    data-edit-btn
                    data-id="{{ $c->id }}"
                    data-name="{{ e($c->name) }}"
                    data-email="{{ e($c->email ?? '') }}"
                    data-phone="{{ e($c->phone ?? '') }}"
                    data-billing="{{ e($c->billing_address ?? '') }}"
                    data-shipping="{{ e($c->shipping_address ?? '') }}"
                    data-active="{{ $c->is_active ? '1':'0' }}">
                    Edit
                  </button>

                  <form method="POST" action="{{ route('customers.destroy',$c) }}" data-delete-form class="inline">
                    @csrf @method('DELETE')
                    <button class="text-xs font-semibold text-rose-600" type="submit">Delete</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="py-10 text-center text-slate-500 dark:text-slate-400">No customers found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
  </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="fixed inset-0 z-[60] hidden">
  <div class="absolute inset-0 bg-slate-900/40" data-modal-close></div>
  <div class="absolute left-1/2 top-1/2 w-[92%] max-w-2xl -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-center justify-between">
      <div class="text-lg font-semibold">Edit Customer</div>
      <button class="h-10 w-10 rounded-xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
        type="button" data-modal-close>✕</button>
    </div>

    <form id="editForm" class="mt-4 space-y-4" method="POST">
      @csrf @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Name</label>
          <input id="eName" name="name" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div class="flex items-center gap-2 pt-7">
          <input id="eActive" name="is_active" type="checkbox" class="h-4 w-4">
          <label for="eActive" class="text-sm font-semibold">Active</label>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Email</label>
          <input id="eEmail" name="email"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Phone</label>
          <input id="ePhone" name="phone"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Billing Address</label>
          <textarea id="eBilling" name="billing_address" rows="3"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
        </div>
        <div>
          <label class="text-sm font-semibold">Shipping Address</label>
          <textarea id="eShipping" name="shipping_address" rows="3"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" data-modal-close
          class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
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
  // delete confirm
  document.querySelectorAll('[data-delete-form]').forEach(f=>{
    f.addEventListener('submit', (e)=>{
      if(!confirm('Delete this customer?')) e.preventDefault();
    });
  });

  // modal
  const modal = document.getElementById('editModal');
  const editForm = document.getElementById('editForm');

  const eName = document.getElementById('eName');
  const eEmail = document.getElementById('eEmail');
  const ePhone = document.getElementById('ePhone');
  const eBilling = document.getElementById('eBilling');
  const eShipping = document.getElementById('eShipping');
  const eActive = document.getElementById('eActive');

  function openModal(){ modal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function closeModal(){ modal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

  document.querySelectorAll('[data-modal-close]').forEach(btn=>{
    btn.addEventListener('click', closeModal);
  });

  document.querySelectorAll('[data-edit-btn]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.dataset.id;

      eName.value = btn.dataset.name || '';
      eEmail.value = btn.dataset.email || '';
      ePhone.value = btn.dataset.phone || '';
      eBilling.value = btn.dataset.billing || '';
      eShipping.value = btn.dataset.shipping || '';
      eActive.checked = (btn.dataset.active === '1');

      editForm.action = "{{ url('/customers') }}/" + id;
      openModal();
    });
  });

  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeModal(); });
})();
</script>
@endsection
