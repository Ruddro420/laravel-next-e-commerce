<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $roles = Role::with(['permissions'])
            ->when($q, fn($qr)=>$qr->where('name','like',"%$q%")->orWhere('display_name','like',"%$q%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // group permissions by group column for a clean UI
        $permissions = Permission::orderBy('group')->orderBy('name')->get()
            ->groupBy(fn($p)=>$p->group ?: 'other');

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
            'display_name' => $data['display_name'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('success','Role created successfully!');
    }

    public function update(Request $request, Role $role)
    {
        // protect admin role from being deleted/renamed accidentally
        if ($role->name === 'admin') {
            // allow permission updates but block rename
            $data = $request->validate([
                'display_name' => ['required','string','max:120'],
                'permissions' => ['nullable','array'],
                'permissions.*' => ['integer','exists:permissions,id'],
            ]);

            $role->update(['display_name'=>$data['display_name']]);
            $role->permissions()->sync($data['permissions'] ?? []);

            return back()->with('success','Admin role updated!');
        }

        $data = $request->validate([
            'name' => ['required','string','max:80','unique:roles,name,'.$role->id],
            'display_name' => ['required','string','max:120'],
            'permissions' => ['nullable','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ]);

        $role->update([
            'name' => strtolower(trim($data['name'])),
            'display_name' => $data['display_name'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('success','Role updated successfully!');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return back()->with('error','Admin role cannot be deleted.');
        }

        // detach pivot
        $role->permissions()->detach();
        $role->users()->detach();
        $role->delete();

        return back()->with('success','Role deleted successfully!');
    }
}
