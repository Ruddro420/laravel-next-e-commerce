@extends('layouts.app')
@section('title','Roles & Permissions')
@section('subtitle','Settings')
@section('pageTitle','Roles & Permissions')
@section('pageDesc','Enable/disable menus by role.')

@section('content')
<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

  @if(session('success'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      {{ session('error') }}
    </div>
  @endif

  @if($errors->any())
    <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    <form class="flex-1" method="GET" action="{{ route('settings.roles') }}">
      <div class="relative max-w-md">
        <input name="q" value="{{ $q ?? '' }}"
          class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 pr-24 text-sm shadow-sm outline-none placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
          placeholder="Search role..." />
        <button class="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
          Search
        </button>
      </div>
    </form>

    <button type="button" id="btnOpenCreate"
      class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
      + Add Role
    </button>
  </div>

  <div class="mt-5 overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-slate-500 dark:text-slate-400">
          <th class="py-3 pr-3">Role</th>
          <th class="py-3 pr-3">Permissions</th>
          <th class="py-3 pr-3 text-right">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($roles as $r)
          @php
            // IMPORTANT: array, not collection
            $permIds = $r->permissions->pluck('id')->toArray();

            $editPayload = [
              'id' => $r->id,
              'name' => $r->name,
              'display_name' => $r->display_name,
              'permIds' => $permIds,
            ];
          @endphp

          <tr class="border-t border-slate-100 dark:border-slate-800">
            <td class="py-3 pr-3">
              <div class="font-semibold">{{ $r->display_name }}</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">{{ $r->name }}</div>
            </td>

            <td class="py-3 pr-3">
              <div class="flex flex-wrap gap-2">
                @foreach($r->permissions->take(8) as $p)
                  <span class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    {{ $p->name }}
                  </span>
                @endforeach

                @if($r->permissions->count() > 8)
                  <span class="text-xs text-slate-500">+{{ $r->permissions->count() - 8 }} more</span>
                @endif
              </div>
            </td>

            <td class="py-3 pr-3 text-right">
              <button type="button"
                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
                data-edit='@json($editPayload, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT)'>
                Edit
              </button>

              <form method="POST" action="{{ route('settings.roles.destroy',$r) }}" class="inline"
                onsubmit="return confirm('Delete this role? Users will lose access.')">
                @csrf
                @method('DELETE')
                <button class="ml-2 rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="py-6 text-center text-slate-500">No roles found.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-5">{{ $roles->links() }}</div>
</div>

{{-- Create Modal --}}
<div id="modalCreate" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/40" data-close></div>
  <div class="absolute left-1/2 top-1/2 w-[94%] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-soft dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div class="font-semibold">Add Role</div>
      <button type="button" class="h-9 w-9 rounded-xl border border-slate-200 dark:border-slate-800" data-close>✕</button>
    </div>

    <form method="POST" action="{{ route('settings.roles.store') }}" class="mt-4 space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Role Key (unique)</label>
          <input name="name" required placeholder="cashier"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
          <div class="mt-1 text-xs text-slate-500">Example: admin, manager, cashier</div>
        </div>

        <div>
          <label class="text-sm font-semibold">Display Name</label>
          <input name="display_name" required placeholder="Cashier"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
        </div>
      </div>

      <div>
        <div class="font-semibold">Menu Access (Permissions)</div>
        <div class="mt-3 space-y-4">
          @foreach($permissions as $group => $perms)
            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
              <div class="mb-2 font-semibold capitalize">{{ $group }}</div>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($perms as $p)
                  <label class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 text-sm dark:border-slate-800">
                    <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="h-4 w-4">
                    <span class="font-semibold">{{ $p->name }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold dark:border-slate-800" data-close>Cancel</button>
        <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create Role</button>
      </div>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div id="modalEdit" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/40" data-close></div>
  <div class="absolute left-1/2 top-1/2 w-[94%] max-w-3xl -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-soft dark:bg-slate-900">
    <div class="flex items-center justify-between">
      <div class="font-semibold">Edit Role</div>
      <button type="button" class="h-9 w-9 rounded-xl border border-slate-200 dark:border-slate-800" data-close>✕</button>
    </div>

    <form method="POST" id="editForm" action="#" class="mt-4 space-y-4">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Role Key</label>
          <input id="editName" name="name" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
        </div>

        <div>
          <label class="text-sm font-semibold">Display Name</label>
          <input id="editDisplay" name="display_name" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
        </div>
      </div>

      <div>
        <div class="font-semibold">Menu Access (Permissions)</div>
        <div class="mt-3 space-y-4">
          @foreach($permissions as $group => $perms)
            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
              <div class="mb-2 font-semibold capitalize">{{ $group }}</div>
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach($perms as $p)
                  <label class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 text-sm dark:border-slate-800">
                    <input type="checkbox" name="permissions[]" value="{{ $p->id }}" class="h-4 w-4 editPerm">
                    <span class="font-semibold">{{ $p->name }}</span>
                  </label>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold dark:border-slate-800" data-close>Cancel</button>
        <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Update Role</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const modalCreate = document.getElementById('modalCreate');
  const modalEdit = document.getElementById('modalEdit');
  const btnOpenCreate = document.getElementById('btnOpenCreate');

  function openModal(m){
    m.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }

  function closeAll(){
    modalCreate.classList.add('hidden');
    modalEdit.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
  }

  btnOpenCreate?.addEventListener('click', ()=> openModal(modalCreate));

  document.querySelectorAll('[data-close]').forEach(el=>{
    el.addEventListener('click', closeAll);
  });

  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape') closeAll();
  });

  const editForm = document.getElementById('editForm');
  const editName = document.getElementById('editName');
  const editDisplay = document.getElementById('editDisplay');

  document.querySelectorAll('[data-edit]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const data = JSON.parse(btn.getAttribute('data-edit'));

      editForm.action = "{{ url('/settings/roles') }}/" + data.id;
      editName.value = data.name || '';
      editDisplay.value = data.display_name || '';

      const ids = (data.permIds || []).map(n => parseInt(n, 10));

      document.querySelectorAll('.editPerm').forEach(ch=>{
        const v = parseInt(ch.value, 10);
        ch.checked = ids.includes(v);
      });

      openModal(modalEdit);
    });
  });
})();
</script>
@endsection
