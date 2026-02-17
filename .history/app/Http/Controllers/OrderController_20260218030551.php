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
                $qr->where('order_number', 'like', "%$q%")
                    ->orWhereHas('customer', function ($c) use ($q) {
                        $c->where('name', 'like', "%$q%")
                          ->orWhere('email', 'like', "%$q%");
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

        return DB::transaction(function () use ($data, $request) {

            $customer = !empty($data['customer_id'])
                ? Customer::find($data['customer_id'])
                : null;

            // Build items from server-trusted products
            [$subtotal, $itemsPayload] = $this->buildItemsFromProducts($data['items']);

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

            // Stock check + deduct (safe)
            $this->decreaseStockOrFail($itemsPayload);

            // Build order attributes respecting your schema
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

            // If your orders table has coupon/tax columns, set them. Otherwise map coupon discount to `discount`.
            if (Schema::hasColumn('orders', 'coupon_id')) {
                $orderAttrs['coupon_id'] = $couponId;
            }
            if (Schema::hasColumn('orders', 'coupon_code')) {
                $orderAttrs['coupon_code'] = $couponCode;
            }
            if (Schema::hasColumn('orders', 'coupon_discount')) {
                $orderAttrs['coupon_discount'] = $couponDiscount;
            }
            if (Schema::hasColumn('orders', 'discount')) {
                $orderAttrs['discount'] = $couponDiscount;
            }

            if (Schema::hasColumn('orders', 'tax_rate_id')) {
                $orderAttrs['tax_rate_id'] = $taxRateId;
            }
            if (Schema::hasColumn('orders', 'tax_amount')) {
                $orderAttrs['tax_amount'] = $taxAmount;
            }

            $order = Order::create($orderAttrs);

            // Items
            $order->items()->createMany($itemsPayload);

            // Payment calc
            $pay = $data['payment'];
            $amountPaid = (float)($pay['amount_paid'] ?? 0);
            $amountDue  = max(0, $total - $amountPaid);
            $paymentStatus = $this->calcPaymentStatus($pay['method'], $amountPaid, $amountDue);

            // Save payment in relation if exists
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

            // Also store payment_method/payment_status in orders table if columns exist (your migration has these)
            $updates = [];
            if (Schema::hasColumn('orders', 'payment_method')) {
                $updates['payment_method'] = $pay['method'];
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $updates['payment_status'] = ($paymentStatus === 'paid') ? 'paid' : 'unpaid';
            }
            if (!empty($updates)) {
                $order->update($updates);
            }

            // increment coupon usage
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

            // Restore previous stock first (based on product_id + qty)
            $oldItems = $order->items()->get()->map(function ($i) {
                return [
                    'product_id' => $i->product_id,
                    'qty'        => (int)$i->qty,
                ];
            })->toArray();

            $this->restoreStockFromOldItems($order, $oldItems);

            // Build new items from products
            [$subtotal, $itemsPayload] = $this->buildItemsFromProducts($data['items']);

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

            // Stock check + deduct for new items
            $this->decreaseStockOrFail($itemsPayload);

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

            // coupon/tax fields (if exist)
            if (Schema::hasColumn('orders', 'coupon_id')) $orderAttrs['coupon_id'] = $couponId;
            if (Schema::hasColumn('orders', 'coupon_code')) $orderAttrs['coupon_code'] = $couponCode;
            if (Schema::hasColumn('orders', 'coupon_discount')) $orderAttrs['coupon_discount'] = $couponDiscount;
            if (Schema::hasColumn('orders', 'discount')) $orderAttrs['discount'] = $couponDiscount;

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

            // Also keep orders table payment fields in sync if columns exist
            $updates = [];
            if (Schema::hasColumn('orders', 'payment_method')) {
                $updates['payment_method'] = $pay['method'];
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $updates['payment_status'] = ($paymentStatus === 'paid') ? 'paid' : 'unpaid';
            }
            if (!empty($updates)) {
                $order->update($updates);
            }

            return back()->with('success', 'Order updated successfully!');
        });
    }

    public function destroy(Order $order)
    {
        return DB::transaction(function () use ($order) {

            // Restore stock (recommended)
            $oldItems = $order->items()->get()->map(function ($i) {
                return [
                    'product_id' => $i->product_id,
                    'qty'        => (int)$i->qty,
                ];
            })->toArray();

            $this->restoreStockFromOldItems($order, $oldItems);

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

            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty'      => ['required', 'integer', 'min:1'],
            'items.*.price'    => ['required', 'numeric', 'min:0'],

            'payment.method'   => ['required', Rule::in(['cod', 'bkash', 'nagad', 'rocket'])],
            'payment.transaction_id' => ['nullable', 'string', 'max:120'],
            'payment.amount_paid'    => ['nullable', 'numeric', 'min:0'],
        ], [
            'items.*.product_id.required' => 'Product is required for each item.',
            'payment.method.required'     => 'Payment method is required.',
        ]);
    }

    // ---------------- ITEMS (SERVER TRUSTED) ----------------

    /**
     * Build items from DB products. This ensures product_id is saved correctly,
     * and product_name/sku are always valid.
     */
    private function buildItemsFromProducts(array $items): array
    {
        $payload = [];
        $subtotal = 0.0;

        $productIds = collect($items)->pluck('product_id')->unique()->values();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $it) {
            $pid = (int)$it['product_id'];
            $qty = (int)$it['qty'];
            $price = (float)$it['price'];

            $p = $products->get($pid);
            if (!$p) {
                abort(422, "Invalid product selected.");
            }

            $line = $qty * $price;
            $subtotal += $line;

            $payload[] = [
                'product_id'   => $p->id,
                'product_name' => $p->name,
                'sku'          => $p->sku,
                'qty'          => $qty,
                'price'        => $price,
                'line_total'   => $line,
            ];
        }

        return [round($subtotal, 2), $payload];
    }

    // ---------------- STOCK ----------------

    private function decreaseStockOrFail(array $itemsPayload): void
    {
        foreach ($itemsPayload as $row) {
            if (empty($row['product_id'])) continue;

            $p = Product::where('id', $row['product_id'])->lockForUpdate()->first();
            if (!$p) continue;

            if ($p->stock !== null) {
                $need = (int)$row['qty'];
                $have = (int)$p->stock;

                if ($need > $have) {
                    abort(422, "Not enough stock for {$p->name}. Available: {$have}");
                }

                // If you want direct subtraction (simple):
                $p->stock = $have - $need;
                $p->save();
            }

            // If you use StockService ledger, keep it too:
            if (class_exists(StockService::class) && method_exists(StockService::class, 'move')) {
                StockService::move(
                    $p,
                    'out',
                    (int)$row['qty'],
                    null,
                    'Order stock deducted',
                    null
                );
            }
        }
    }

    private function restoreStockFromOldItems(Order $order, array $oldItems): void
    {
        foreach ($oldItems as $it) {
            if (empty($it['product_id'])) continue;

            $p = Product::where('id', $it['product_id'])->lockForUpdate()->first();
            if (!$p) continue;

            $qty = (int)$it['qty'];
            if ($qty <= 0) continue;

            if ($p->stock !== null) {
                $p->stock = ((int)$p->stock) + $qty;
                $p->save();
            }

            if (class_exists(StockService::class) && method_exists(StockService::class, 'move')) {
                StockService::move(
                    $p,
                    'in',
                    $qty,
                    $order->id,
                    'Order edited/deleted (stock restored)',
                    $order->order_number
                );
            }
        }
    }

    // ---------------- COUPON / TAX / PAYMENT HELPERS ----------------

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

        if ($tax->mode === 'exclusive') {
            $amount = ($base * (float)$tax->rate) / 100.0;
            return [$tax->id, round($amount, 2)];
        }

        $rate = (float)$tax->rate;
        if ($rate <= 0) return [$tax->id, 0.0];

        $div = 1 + ($rate / 100.0);
        $taxPart = $base - ($base / $div);

        return [$tax->id, round($taxPart, 2)];
    }

    private function calcPaymentStatus(string $method, float $paid, float $due): string
    {
        // COD => pending until delivered/collected
        if ($method === 'cod') return 'pending';

        // Digital => paid only if fully covered and >0
        return ($due <= 0.00001 && $paid > 0) ? 'paid' : 'pending';
    }

    private function makeOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }
}
