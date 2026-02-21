<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class CouponController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $coupons = Coupon::query()
            ->when($q, fn($qr) => $qr->where('code', 'like', "%$q%"))
            ->latest()->paginate(10)->withQueryString();

        return view('pages.crm.coupons', compact('coupons', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:coupons,code'],
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;

        Coupon::create($data);
        return back()->with('success', 'Coupon created successfully!');
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'type' => ['required', Rule::in(['fixed', 'percent'])],
            'value' => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable'],
        ]);

        $data['code'] = strtoupper(trim($data['code']));
        $data['min_order_amount'] = $data['min_order_amount'] ?? 0;
        $data['is_active'] = $request->has('is_active');

        $coupon->update($data);
        return back()->with('success', 'Coupon updated successfully!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon deleted successfully!');
    }
    // get coupon by code for API
    public function getCouponByCode(Request $request)
    {
        $code = strtoupper(trim($request->query('code')));

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon code is required'
            ], 400);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }

        // Check active
        if (!$coupon->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon is inactive'
            ], 400);
        }

        $now = Carbon::now();

        // Check start date
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not started yet'
            ], 400);
        }

        // Check expiry
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon expired'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'coupon' => $coupon
        ]);
    }
}
