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

    public function applyCoupon(Request $request)
    {
        $code = strtoupper(trim((string) $request->query('code')));
        $amount = (float) $request->query('amount', 0);

        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon code is required'
            ], 400);
        }

        if ($amount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Valid amount is required'
            ], 400);
        }

        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code'
            ], 404);
        }

        // If you have is_active column
        if (isset($coupon->is_active) && !$coupon->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon is inactive'
            ], 400);
        }

        $now = Carbon::now();

        if ($coupon->starts_at && $now->lt(Carbon::parse($coupon->starts_at))) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not started yet'
            ], 400);
        }

        if ($coupon->expires_at && $now->gt(Carbon::parse($coupon->expires_at))) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon expired'
            ], 400);
        }

        $minOrder = (float) ($coupon->min_order_amount ?? 0);
        if ($minOrder > 0 && $amount < $minOrder) {
            return response()->json([
                'success' => false,
                'message' => "Minimum order amount is {$minOrder}"
            ], 400);
        }

        // ✅ Usage limit check (only if you have a usage_count column)
        // If you DO NOT have usage_count, ignore this part or tell me your usage system.
        if (!is_null($coupon->usage_limit) && isset($coupon->usage_count)) {
            if ((int) $coupon->usage_count >= (int) $coupon->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon usage limit reached'
                ], 400);
            }
        }

        // ✅ Calculate discount
        $discount = 0;

        if ($coupon->type === 'percent') {
            $discount = ($amount * (float) $coupon->value) / 100;
            // optional: don’t allow discount more than amount
            $discount = min($discount, $amount);
        } elseif ($coupon->type === 'fixed') {
            $discount = min((float) $coupon->value, $amount);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon type'
            ], 400);
        }

        $finalPayable = max(0, $amount - $discount);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully',
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'min_order_amount' => $minOrder,
                'amount' => $amount,
                'discount' => round($discount, 2),
                'final_payable' => round($finalPayable, 2),
                'starts_at' => $coupon->starts_at,
                'expires_at' => $coupon->expires_at,
            ],
        ]);
    }
}
