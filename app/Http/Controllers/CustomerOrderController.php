<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class CustomerOrderController extends Controller
{
    // GET /api/customer/orders
    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = max(1, min(50, $perPage));

        $q = $request->get('q');          // search order_number
        $status = $request->get('status'); // filter status

        $orders = Order::query()
            ->where('customer_id', $customer->id)
            ->with(['items', 'payment']) // keep lightweight for list
            ->when($q, fn($qr) => $qr->where('order_number', 'like', "%{$q}%"))
            ->when($status, fn($qr) => $qr->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }

    // GET /api/customer/orders/{id}
    public function show($id)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $order = Order::with(['customer', 'items', 'payment', 'taxRate'])
            ->where('id', $id)
            ->where('customer_id', $customer->id) // ✅ ownership check
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }
}