<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $roles = Role::with('permissions')
            ->when($q, function($qr) use ($q){
                $qr->where('name','like',"%{$q}%")
                   ->orWhere('display_name','like',"%{$q}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // IMPORTANT: group permissions by "group" column
        $permissions = Permission::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy(function($p){
                return $p->group ?: 'general';
            });

        return view('pages.settings.roles', compact('roles','permissions','q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80','unique:roles,name'],
            'display_name' => ['required','string','max:120'],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => strtolower(trim($data['name'])),
            'display_name' => trim($data['display_name']),
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('success','Role created successfully!');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80', Rule::unique('roles','name')->ignore($role->id)],
            'display_name' => ['required','string','max:120'],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ]);

        $role->update([
            'name' => strtolower(trim($data['name'])),
            'display_name' => trim($data['display_name']),
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('success','Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        // protect admin role optionally
        if ($role->name === 'admin') {
            return back()->with('error','Admin role cannot be deleted.');
        }

        $role->delete();
        return back()->with('success','Role deleted successfully!');
    }
}
