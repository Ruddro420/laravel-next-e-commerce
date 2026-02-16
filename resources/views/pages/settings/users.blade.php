@extends('layouts.app')
@section('title', 'User Management')
@section('subtitle', 'Settings')
@section('pageTitle', 'User Management')
@section('pageDesc', 'Create users, assign roles and manage access.')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

        @if (session('success'))
            <div
                class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div
                class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div
                class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                <div class="font-semibold mb-1">Fix these errors:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <form class="flex-1" method="GET" action="{{ route('settings.users') }}">
                <div class="relative max-w-md">
                    <input name="q" value="{{ $q ?? '' }}"
                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 pr-24 text-sm shadow-sm outline-none placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
                        placeholder="Search user name/email..." />
                    <button
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white dark:bg-white dark:text-slate-900">
                        Search
                    </button>
                </div>
            </form>

            <button type="button" id="btnOpenCreate"
                class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                + Add User
            </button>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 dark:text-slate-400">
                        <th class="py-3 pr-3">User</th>
                        <th class="py-3 pr-3">Email</th>
                        <th class="py-3 pr-3">Roles</th>
                        <th class="py-3 pr-3">Status</th>
                        <th class="py-3 pr-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        @php
                            $roleNames = $u->roles->pluck('display_name')->filter()->values()->all();
                            if (empty($roleNames)) {
                                $roleNames = $u->roles->pluck('name')->values()->all();
                            }
                        @endphp
                        <tr class="border-t border-slate-100 dark:border-slate-800">
                            <td class="py-3 pr-3">
                                <div class="font-semibold">{{ $u->name }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ $u->id }}</div>
                            </td>
                            <td class="py-3 pr-3">{{ $u->email }}</td>
                            <td class="py-3 pr-3">
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($roleNames as $rn)
                                        <span
                                            class="rounded-xl bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                            {{ $rn }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3 pr-3">
                                @if ($u->is_active)
                                    <span
                                        class="rounded-xl bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">Active</span>
                                @else
                                    <span
                                        class="rounded-xl bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-200">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3 pr-3 text-right">
                                @php
                                    $editPayload = [
                                        'id' => $u->id,
                                        'name' => $u->name,
                                        'email' => $u->email,
                                        'is_active' => (bool) $u->is_active,
                                        'roles' => $u->roles->pluck('id')->map(fn($x) => (int) $x)->values()->toArray(),
                                    ];
                                @endphp

                                <button type="button"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
                                    data-edit='@json($editPayload)'>
                                    Edit
                                </button>


                                <form method="POST" action="{{ route('settings.users.destroy', $u) }}" class="inline"
                                    onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button
                                        class="ml-2 rounded-xl bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Create Modal --}}
    <div id="modalCreate" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/40" data-close></div>
        <div
            class="absolute left-1/2 top-1/2 w-[94%] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-soft dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Add User</div>
                <button type="button" class="h-9 w-9 rounded-xl border border-slate-200 dark:border-slate-800"
                    data-close>✕</button>
            </div>

            <form method="POST" action="{{ route('settings.users.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold">Name</label>
                    <input name="name" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                </div>

                <div>
                    <label class="text-sm font-semibold">Email</label>
                    <input name="email" type="email" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                </div>

                <div>
                    <label class="text-sm font-semibold">Password</label>
                    <input name="password" type="password" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                </div>

                <div>
                    <label class="text-sm font-semibold">Roles</label>
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        @foreach ($roles as $r)
                            <label
                                class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 text-sm dark:border-slate-800">
                                <input type="checkbox" name="roles[]" value="{{ $r->id }}" class="h-4 w-4">
                                <span class="font-semibold">{{ $r->display_name ?? $r->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold dark:border-slate-800"
                        data-close>Cancel</button>
                    <button
                        class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="modalEdit" class="hidden fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/40" data-close></div>
        <div
            class="absolute left-1/2 top-1/2 w-[94%] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-soft dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <div class="font-semibold">Edit User</div>
                <button type="button" class="h-9 w-9 rounded-xl border border-slate-200 dark:border-slate-800"
                    data-close>✕</button>
            </div>

            <form method="POST" id="editForm" action="#" class="mt-4 space-y-4">
                @csrf @method('PUT')

                <div>
                    <label class="text-sm font-semibold">Name</label>
                    <input id="editName" name="name" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                </div>

                <div>
                    <label class="text-sm font-semibold">Email</label>
                    <input id="editEmail" name="email" type="email" required
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                </div>

                <div>
                    <label class="text-sm font-semibold">New Password (optional)</label>
                    <input name="password" type="password"
                        class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
                </div>

                <div>
                    <label class="text-sm font-semibold">Roles</label>
                    <div class="mt-2 grid grid-cols-2 gap-2" id="editRolesWrap">
                        @foreach ($roles as $r)
                            <label
                                class="flex items-center gap-2 rounded-2xl border border-slate-200 p-3 text-sm dark:border-slate-800">
                                <input type="checkbox" name="roles[]" value="{{ $r->id }}"
                                    class="h-4 w-4 editRole">
                                <span class="font-semibold">{{ $r->display_name ?? $r->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <input id="editActive" name="is_active" type="checkbox" class="h-4 w-4">
                    <label class="text-sm font-semibold">Active</label>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button"
                        class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-semibold dark:border-slate-800"
                        data-close>Cancel</button>
                    <button
                        class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            const modalCreate = document.getElementById('modalCreate');
            const modalEdit = document.getElementById('modalEdit');
            const btnOpenCreate = document.getElementById('btnOpenCreate');

            function openModal(m) {
                m.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal(m) {
                m.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            btnOpenCreate?.addEventListener('click', () => openModal(modalCreate));

            document.querySelectorAll('[data-close]').forEach(el => {
                el.addEventListener('click', () => {
                    closeModal(modalCreate);
                    closeModal(modalEdit);
                });
            });

            // Edit
            const editForm = document.getElementById('editForm');
            const editName = document.getElementById('editName');
            const editEmail = document.getElementById('editEmail');
            const editActive = document.getElementById('editActive');

            document.querySelectorAll('[data-edit]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const data = JSON.parse(btn.getAttribute('data-edit'));

                    editForm.action = "{{ url('/settings/users') }}/" + data.id;
                    editName.value = data.name;
                    editEmail.value = data.email;
                    editActive.checked = !!data.is_active;

                    // role checkboxes
                    document.querySelectorAll('.editRole').forEach(ch => {
                        ch.checked = data.roles.includes(parseInt(ch.value));
                    });

                    openModal(modalEdit);
                });
            });
        })();
    </script>
@endsection
