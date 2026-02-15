<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {

        // dd('CustomerController@index is running');
        $q = $request->get('q');

        $customers = Customer::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.crm.customers', compact('customers', 'q'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'billing_address' => ['nullable', 'string', 'max:4000'],
            'shipping_address' => ['nullable', 'string', 'max:4000'],
        ]);

        Customer::create($data);
        return back()->with('success', 'Customer added successfully!');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'billing_address' => ['nullable', 'string', 'max:4000'],
            'shipping_address' => ['nullable', 'string', 'max:4000'],
            'is_active' => ['nullable'],
        ]);

        $data['is_active'] = $request->has('is_active');
        $customer->update($data);

        return back()->with('success', 'Customer updated successfully!');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('success', 'Customer deleted successfully!');
    }
}
