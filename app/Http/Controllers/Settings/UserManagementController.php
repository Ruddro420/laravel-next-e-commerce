<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $users = User::with('roles')
            ->when($q, fn($qr)=>$qr->where('name','like',"%$q%")->orWhere('email','like',"%$q%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('pages.settings.users', compact('users','roles','q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:160'],
            'email' => ['required','email','max:190','unique:users,email'],
            'password' => ['required','string','min:6','max:60'],
            'roles' => ['required','array','min:1'],
            'roles.*' => ['integer','exists:roles,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->roles()->sync($data['roles']);
        return back()->with('success','User created successfully!');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required','string','max:160'],
            'email' => ['required','email','max:190','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:6','max:60'],
            'roles' => ['required','array','min:1'],
            'roles.*' => ['integer','exists:roles,id'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => $request->has('is_active'),
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        $user->roles()->sync($data['roles']);

        return back()->with('success','User updated successfully!');
    }

    public function destroy(User $user)
    {
        // if (auth()->id() === $user->id) {
        //     return back()->with('error','You cannot delete your own account.');
        // }
        $user->roles()->detach();
        $user->delete();
        return back()->with('success','User deleted successfully!');
    }
}
