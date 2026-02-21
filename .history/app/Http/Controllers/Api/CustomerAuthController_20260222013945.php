<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerAuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'password' => ['required', 'string', 'min:8', 'max:72'],
        ]);

        // Ensure phone/email uniqueness in customers table (since that's the source of truth)
        $existsPhone = Customer::where('phone', $data['phone'])->exists();
        if ($existsPhone) {
            throw ValidationException::withMessages([
                'phone' => ['Phone already exists.'],
            ]);
        }

        if (!empty($data['email'])) {
            $existsEmail = Customer::where('email', $data['email'])->exists();
            if ($existsEmail) {
                throw ValidationException::withMessages([
                    'email' => ['Email already exists.'],
                ]);
            }
        }

        // Create Customer (your table)
        $customer = Customer::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'is_active' => true,
        ]);

        // Create CustomerAuth (password stored here)
        CustomerAuth::create([
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $token = $customer->createToken('customer_access')->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'token' => $token,
            'customer' => $customer,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'], // phone or email
            'password' => ['required', 'string'],
        ]);

        // Find customer by phone/email from customers table
        $customer = Customer::query()
            ->where('phone', $data['identifier'])
            ->orWhere('email', $data['identifier'])
            ->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'identifier' => ['Invalid credentials.'],
            ]);
        }

        $auth = CustomerAuth::where('customer_id', $customer->id)->first();

        if (!$auth || !$auth->is_active || !Hash::check($data['password'], $auth->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Invalid credentials.'],
            ]);
        }

        if (!$customer->is_active) {
            throw ValidationException::withMessages([
                'identifier' => ['Account is inactive.'],
            ]);
        }

        // Optional single-session:
        // $customer->tokens()->delete();

        $token = $customer->createToken('customer_access')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'token' => $token,
            'customer' => $customer,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'customer' => $request->user('customer'),
        ]);
    }

    public function logout(Request $request)
    {
        $customer = $request->user('customer');
        $customer?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out.',
        ]);
    }
}