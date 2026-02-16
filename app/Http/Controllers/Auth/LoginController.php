<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('pages.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        // Only allow active users
        $remember = $request->boolean('remember');
        if (Auth::attempt(['email'=>$data['email'], 'password'=>$data['password'], 'is_active'=>1], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials or inactive account.'])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
