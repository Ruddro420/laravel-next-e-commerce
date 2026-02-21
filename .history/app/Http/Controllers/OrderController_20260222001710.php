<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Coupon;
use App\Models\TaxRate;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $orders = Order::with(['customer', 'payment'])
            ->when($q, function ($qr) use ($q) {
                $qr->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%");
                    });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.crm.orders.index', compact('orders', 'q'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $taxRates  = TaxRate::where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('is_active', 1)->orderBy('name')->get();

        return view('pages.crm.orders.create', compact('customers', 'taxRates', 'products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateOrder($request);

        return DB::transaction(function () use ($data) {

            $customer = !empty($data['customer_id'])
                ? Customer::find($data['customer_id'])
                : null;

            // ✅ Build items safely (product_id optional)
            [$subtotal, $itemsPayload] = $this->buildItemsSafe($data['items']);

            // Coupon
            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            // Tax
            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
            );

            $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

            // ✅ Stock deduct only for rows with product_id
            $this->deductStockOrFail($itemsPayload);

            // Build order attrs
            $orderAttrs = [
                'order_number'     => $this->makeOrderNumber(),
                'customer_id'      => $customer?->id,
                'status'           => $data['status'],

                'subtotal'         => $subtotal,
                'shipping'         => (float)($data['shipping'] ?? 0),
                'total'            => $total,

                'billing_address'  => $data['billing_address'] ?? $customer?->billing_address,
                'shipping_address' => $data['shipping_address'] ?? $customer?->shipping_address,
                'note'             => $data['note'] ?? null,
            ];

            // discount fields
            if (Schema::hasColumn('orders', 'discount')) {
                $orderAttrs['discount'] = $couponDiscount;
            }
            if (Schema::hasColumn('orders', 'coupon_id')) $orderAttrs['coupon_id'] = $couponId;
            if (Schema::hasColumn('orders', 'coupon_code')) $orderAttrs['coupon_code'] = $couponCode;
            if (Schema::hasColumn('orders', 'coupon_discount')) $orderAttrs['coupon_discount'] = $couponDiscount;

            // tax fields
            if (Schema::hasColumn('orders', 'tax_rate_id')) $orderAttrs['tax_rate_id'] = $taxRateId;
            if (Schema::hasColumn('orders', 'tax_amount')) $orderAttrs['tax_amount'] = $taxAmount;

            $order = Order::create($orderAttrs);

            // items
            $order->items()->createMany($itemsPayload);

            // payment
            $pay = $data['payment'];
            $amountPaid = (float)($pay['amount_paid'] ?? 0);
            $amountDue  = max(0, $total - $amountPaid);

            $paymentStatus = $this->calcPaymentStatus($pay['method'], $amountPaid, $amountDue);

            // Save to order_payments relation (if exists)
            if (method_exists($order, 'payment')) {
                $order->payment()->create([
                    'method'         => $pay['method'],
                    'transaction_id' => $pay['transaction_id'] ?? null,
                    'amount_paid'    => $amountPaid,
                    'amount_due'     => $amountDue,
                    'status'         => $paymentStatus,
                    'paid_at'        => $paymentStatus === 'paid' ? now() : null,
                ]);
            }

            // Also sync orders.payment_method/payment_status columns (your migration has these)
            $updates = [];
            if (Schema::hasColumn('orders', 'payment_method')) {
                $updates['payment_method'] = $pay['method'];
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                // map to your orders table values
                $updates['payment_status'] = ($paymentStatus === 'paid') ? 'paid' : 'unpaid';
            }
            if (!empty($updates)) {
                $order->update($updates);
            }

            // coupon usage
            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used_count');
            }

            return redirect()->route('crm.orders')->with('success', 'Order created successfully!');
        });
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items', 'payment', 'taxRate']);
        return view('pages.crm.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['customer', 'items', 'payment']);
        $customers = Customer::orderBy('name')->get();
        $taxRates  = TaxRate::where('is_active', true)->orderBy('name')->get();
        $products  = Product::where('is_active', 1)->orderBy('name')->get();

        return view('pages.crm.orders.edit', compact('order', 'customers', 'taxRates', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $this->validateOrder($request);

        return DB::transaction(function () use ($data, $order) {

            $customer = !empty($data['customer_id'])
                ? Customer::find($data['customer_id'])
                : null;

            // ✅ Restore old stock first
            $oldItems = $order->items()->get()->map(fn($i) => [
                'product_id' => $i->product_id,
                'qty'        => (int)$i->qty,
            ])->toArray();

            $this->restoreStock($order, $oldItems);

            // ✅ Build new items safely
            [$subtotal, $itemsPayload] = $this->buildItemsSafe($data['items']);

            // Coupon
            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            // Tax
            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
            );

            $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

            // ✅ Deduct stock for new items
            $this->deductStockOrFail($itemsPayload);

            // Update order
            $orderAttrs = [
                'customer_id'      => $customer?->id,
                'status'           => $data['status'],

                'subtotal'         => $subtotal,
                'shipping'         => (float)($data['shipping'] ?? 0),
                'total'            => $total,

                'billing_address'  => $data['billing_address'] ?? $customer?->billing_address,
                'shipping_address' => $data['shipping_address'] ?? $customer?->shipping_address,
                'note'             => $data['note'] ?? null,
            ];

            if (Schema::hasColumn('orders', 'discount')) $orderAttrs['discount'] = $couponDiscount;
            if (Schema::hasColumn('orders', 'coupon_id')) $orderAttrs['coupon_id'] = $couponId;
            if (Schema::hasColumn('orders', 'coupon_code')) $orderAttrs['coupon_code'] = $couponCode;
            if (Schema::hasColumn('orders', 'coupon_discount')) $orderAttrs['coupon_discount'] = $couponDiscount;

            if (Schema::hasColumn('orders', 'tax_rate_id')) $orderAttrs['tax_rate_id'] = $taxRateId;
            if (Schema::hasColumn('orders', 'tax_amount')) $orderAttrs['tax_amount'] = $taxAmount;

            $order->update($orderAttrs);

            // Replace items
            $order->items()->delete();
            $order->items()->createMany($itemsPayload);

            // Payment update
            $pay = $data['payment'];
            $amountPaid = (float)($pay['amount_paid'] ?? 0);
            $amountDue  = max(0, $total - $amountPaid);
            $paymentStatus = $this->calcPaymentStatus($pay['method'], $amountPaid, $amountDue);

            if (method_exists($order, 'payment')) {
                $order->payment()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'method'         => $pay['method'],
                        'transaction_id' => $pay['transaction_id'] ?? null,
                        'amount_paid'    => $amountPaid,
                        'amount_due'     => $amountDue,
                        'status'         => $paymentStatus,
                        'paid_at'        => $paymentStatus === 'paid' ? now() : null,
                    ]
                );
            }

            // sync orders.payment fields
            $updates = [];
            if (Schema::hasColumn('orders', 'payment_method')) $updates['payment_method'] = $pay['method'];
            if (Schema::hasColumn('orders', 'payment_status')) $updates['payment_status'] = ($paymentStatus === 'paid') ? 'paid' : 'unpaid';
            if (!empty($updates)) $order->update($updates);

            return back()->with('success', 'Order updated successfully!');
        });
    }

    public function destroy(Order $order)
    {
        return DB::transaction(function () use ($order) {

            // Restore stock
            $oldItems = $order->items()->get()->map(fn($i) => [
                'product_id' => $i->product_id,
                'qty'        => (int)$i->qty,
            ])->toArray();

            $this->restoreStock($order, $oldItems);

            $order->delete();

            return redirect()->route('crm.orders')->with('success', 'Order deleted successfully!');
        });
    }

    // ---------------- VALIDATION ----------------

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'customer_id'      => ['nullable', 'integer', 'exists:customers,id'],
            'status'           => ['required', Rule::in(['processing', 'complete', 'hold'])],

            'coupon_code'      => ['nullable', 'string', 'max:50'],
            'tax_rate_id'      => ['nullable', 'integer', 'exists:tax_rates,id'],
            'shipping'         => ['nullable', 'numeric', 'min:0'],

            'billing_address'  => ['nullable', 'string', 'max:4000'],
            'shipping_address' => ['nullable', 'string', 'max:4000'],
            'note'             => ['nullable', 'string', 'max:4000'],

            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['nullable', 'integer', 'exists:products,id'],
            'items.*.product_name'  => ['required', 'string', 'max:200'],
            'items.*.sku'           => ['nullable', 'string', 'max:120'],
            'items.*.qty'           => ['required', 'integer', 'min:1'],
            'items.*.price'         => ['required', 'numeric', 'min:0'],

            'payment'                       => ['required', 'array'],
            'payment.method'                => ['required', Rule::in(['cod', 'bkash', 'nagad', 'rocket'])],
            'payment.amount_paid'           => ['nullable', 'numeric', 'min:0'],
            'payment.transaction_id'        => [
                'nullable',
                'string',
                'max:120',
                Rule::requiredIf(fn() => in_array($request->input('payment.method'), ['bkash', 'nagad', 'rocket'])),
            ],
        ], [
            'items.required'                  => 'At least one item is required.',
            'items.*.product_name.required'   => 'Product name is required for each item.',
            'payment.transaction_id.required' => 'Transaction ID is required for bKash/Nagad/Rocket.',
        ]);
    }

    // ---------------- ITEMS (SAFE) ----------------

    /**
     * ✅ SAFE builder:
     * - If product_id exists and product exists: fill name/sku from DB
     * - If product_id missing or product deleted: keep manual name/sku
     * - NEVER abort(422) for missing product
     */
    private function buildItemsSafe(array $items): array
    {
        $payload = [];
        $subtotal = 0.0;

        $productIds = collect($items)
            ->pluck('product_id')
            ->filter()
            ->unique()
            ->values();

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $it) {
            $pid = !empty($it['product_id']) ? (int)$it['product_id'] : null;
            $p = $pid ? $products->get($pid) : null;

            // If product not found, treat as manual item
            if ($pid && !$p) {
                $pid = null;
            }

            $qty   = max(1, (int)($it['qty'] ?? 1));
            $price = (float)($it['price'] ?? 0);

            $name = $p?->name ?? ($it['product_name'] ?? '');
            $sku  = $p?->sku  ?? ($it['sku'] ?? null);

            $line = $qty * $price;
            $subtotal += $line;

            $payload[] = [
                'product_id'   => $pid,
                'product_name' => $name,
                'sku'          => $sku,
                'qty'          => $qty,
                'price'        => $price,
                'line_total'   => $line,
            ];
        }

        return [round($subtotal, 2), $payload];
    }

    // ---------------- STOCK (NO DOUBLE DEDUCT) ----------------

    private function deductStockOrFail(array $itemsPayload): void
    {
        foreach ($itemsPayload as $row) {
            $pid = $row['product_id'] ?? null;
            if (!$pid) continue;

            $p = Product::where('id', $pid)->lockForUpdate()->first();
            if (!$p) continue;

            $need = (int)($row['qty'] ?? 0);
            if ($need <= 0) continue;

            // check stock only if finite
            if ($p->stock !== null && $need > (int)$p->stock) {
                abort(422, "Not enough stock for {$p->name}. Available: {$p->stock}");
            }

            // ✅ Choose ONE method to change stock:
            // If you use StockService ledger, let it control stock.
            if (class_exists(StockService::class) && method_exists(StockService::class, 'move')) {
                StockService::move($p, 'out', $need, null, 'Order stock deducted', null);
            } else {
                // direct subtraction
                if ($p->stock !== null) {
                    $p->stock = (int)$p->stock - $need;
                    $p->save();
                }
            }
        }
    }

    private function restoreStock(Order $order, array $oldItems): void
    {
        foreach ($oldItems as $it) {
            $pid = $it['product_id'] ?? null;
            if (!$pid) continue;

            $p = Product::where('id', $pid)->lockForUpdate()->first();
            if (!$p) continue;

            $qty = (int)($it['qty'] ?? 0);
            if ($qty <= 0) continue;

            if (class_exists(StockService::class) && method_exists(StockService::class, 'move')) {
                StockService::move($p, 'in', $qty, $order->id, 'Order stock restored', $order->order_number);
            } else {
                if ($p->stock !== null) {
                    $p->stock = (int)$p->stock + $qty;
                    $p->save();
                }
            }
        }
    }

    // ---------------- COUPON / TAX / PAYMENT ----------------

    private function applyCoupon(?string $code, float $subtotal): array
    {
        $code = strtoupper(trim((string)$code));
        if (!$code) return [null, null, 0.0];

        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon || !$coupon->is_active) return [null, null, 0.0];

        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) return [null, null, 0.0];
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) return [null, null, 0.0];
        if ($subtotal < (float)$coupon->min_order_amount) return [null, null, 0.0];

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return [null, null, 0.0];
        }

        $discount = ($coupon->type === 'percent')
            ? ($subtotal * (float)$coupon->value) / 100.0
            : (float)$coupon->value;

        $discount = min($discount, $subtotal);

        return [$coupon->id, $coupon->code, round($discount, 2)];
    }

    private function applyTax(?int $taxRateId, float $base): array
    {
        if (!$taxRateId) return [null, 0.0];

        $tax = TaxRate::where('id', $taxRateId)->where('is_active', true)->first();
        if (!$tax) return [null, 0.0];

        $rate = (float)$tax->rate;

        if ($tax->mode === 'exclusive') {
            return [$tax->id, round(($base * $rate) / 100.0, 2)];
        }

        if ($rate <= 0) return [$tax->id, 0.0];

        $div = 1 + ($rate / 100.0);
        $taxPart = $base - ($base / $div);

        return [$tax->id, round($taxPart, 2)];
    }

    /**
     * ✅ Better payment status:
     * - cod => pending
     * - digital => paid/partial/unpaid based on due/paid
     */
    private function calcPaymentStatus(string $method, float $paid, float $due): string
    {
        if ($method === 'cod') return 'pending';
        if ($paid <= 0 && $due > 0) return 'unpaid';
        if ($due <= 0.00001 && $paid > 0) return 'paid';
        return 'partial';
    }

    private function makeOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }
    // Order api
    public function apiCheckout(Request $request)
    {
        $data = $this->validateOrder($request); // uses your existing validation

        return DB::transaction(function () use ($data) {

            $customer = !empty($data['customer_id'])
                ? Customer::find($data['customer_id'])
                : null;

            // Build items
            [$subtotal, $itemsPayload] = $this->buildItemsSafe($data['items']);

            // Coupon
            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            // Tax
            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
            );

            $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

            // Stock deduct
            $this->deductStockOrFail($itemsPayload);

            // Order attrs
            $orderAttrs = [
                'order_number'     => $this->makeOrderNumber(),
                'customer_id'      => $customer?->id,
                'status'           => $data['status'],

                'subtotal'         => $subtotal,
                'shipping'         => (float)($data['shipping'] ?? 0),
                'total'            => $total,

                'billing_address'  => $data['billing_address'] ?? $customer?->billing_address,
                'shipping_address' => $data['shipping_address'] ?? $customer?->shipping_address,
                'note'             => $data['note'] ?? null,

                // coupon/tax fields (your DB has these)
                'coupon_id'        => $couponId,
                'coupon_code'      => $couponCode,
                'coupon_discount'  => $couponDiscount,
                'tax_rate_id'      => $taxRateId,
                'tax_amount'       => $taxAmount,
                'discount'         => $couponDiscount,
            ];

            $order = Order::create($orderAttrs);

            // items
            $order->items()->createMany($itemsPayload);

            // payment
            $pay = $data['payment'];
            $amountPaid = (float)($pay['amount_paid'] ?? 0);
            $amountDue  = max(0, $total - $amountPaid);

            $paymentStatus = $this->calcPaymentStatus($pay['method'], $amountPaid, $amountDue);

            if (method_exists($order, 'payment')) {
                $order->payment()->create([
                    'method'         => $pay['method'],
                    'transaction_id' => $pay['transaction_id'] ?? null,
                    'amount_paid'    => $amountPaid,
                    'amount_due'     => $amountDue,
                    'status'         => $paymentStatus,
                    'paid_at'        => $paymentStatus === 'paid' ? now() : null,
                ]);
            }

            // also set orders payment fields if exist
            $order->update([
                'payment_method' => $pay['method'],
                'payment_status' => ($paymentStatus === 'paid') ? 'paid' : 'unpaid',
            ]);

            // coupon used count
            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used_count');
            }

            // return json
            $order->load(['customer', 'items', 'payment', 'taxRate']);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order'   => $order,
            ], 201);
        });
    }
}
