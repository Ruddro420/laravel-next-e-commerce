<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\CRM\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));

        $orders = Order::query()
            ->with(['customer', 'payment'])
            ->when($q, function ($qr) use ($q) {
                $qr->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', function ($c) use ($q) {
                        $c->where('name', 'like', "%{$q}%")
                          ->orWhere('email', 'like', "%{$q}%")
                          ->orWhere('phone', 'like', "%{$q}%");
                    });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.crm.orders.index', compact('orders', 'q'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $taxRates = TaxRate::where('is_active', true)->orderBy('name')->get();

        // Keep it light; or paginate/search if you have many products
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('pages.crm.orders.create', compact('customers', 'taxRates', 'products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateOrderRequest($request);

        return DB::transaction(function () use ($data) {
            $customer = !empty($data['customer_id'])
                ? Customer::find($data['customer_id'])
                : null;

            // Build items (server trusted product + price)
            [$subtotal, $itemsPayload] = $this->buildItemsFromProducts($data['items']);

            // Coupon
            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            // Tax base: subtotal - discount + shipping
            $shipping = (float)($data['shipping'] ?? 0);

            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                max(0, $subtotal - $couponDiscount) + $shipping
            );

            $total = max(0, $subtotal - $couponDiscount + $shipping + $taxAmount);

            // Stock: decrease
            $this->decreaseStockOrFail($itemsPayload);

            $order = Order::create([
                'order_number' => $this->makeOrderNumber(),
                'customer_id' => $customer?->id,
                'status' => $data['status'],

                // optional coupon fields (add to your orders migration if not present)
                'coupon_id' => $couponId,
                'coupon_code' => $couponCode,
                'coupon_discount' => $couponDiscount,

                'tax_rate_id' => $taxRateId,
                'tax_amount' => $taxAmount,

                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,

                'billing_address' => $data['billing_address'] ?? $customer?->billing_address,
                'shipping_address' => $data['shipping_address'] ?? $customer?->shipping_address,
                'note' => $data['note'] ?? null,
            ]);

            $order->items()->createMany($itemsPayload);

            // Payment
            $pay = $data['payment'];
            $amountPaid = (float)($pay['amount_paid'] ?? 0);
            $amountDue = max(0, $total - $amountPaid);
            $paymentStatus = $this->calcPaymentStatus($pay['method'], $amountPaid, $amountDue);

            $order->payment()->create([
                'method' => $pay['method'],
                'transaction_id' => $pay['transaction_id'] ?? null,
                'amount_paid' => $amountPaid,
                'amount_due' => $amountDue,
                'status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
            ]);

            // increment coupon usage
            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used_count');
            }

            return redirect()
                ->route('crm.orders.show', $order)
                ->with('success', 'Order created successfully.');
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
        $taxRates = TaxRate::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('pages.crm.orders.edit', compact('order', 'customers', 'taxRates', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $this->validateOrderRequest($request, true);

        return DB::transaction(function () use ($data, $order) {

            // 1) restore stock from old items (IMPORTANT)
            $order->load('items');
            foreach ($order->items as $old) {
                if (!$old->product_id) continue;
                $p = Product::where('id', $old->product_id)->lockForUpdate()->first();
                if ($p && $p->stock !== null) {
                    $p->stock = (int)$p->stock + (int)$old->qty;
                    $p->save();
                }
            }

            // 2) rebuild new items from products
            [$subtotal, $itemsPayload] = $this->buildItemsFromProducts($data['items']);

            // 3) coupon/tax/total
            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            $shipping = (float)($data['shipping'] ?? 0);

            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                max(0, $subtotal - $couponDiscount) + $shipping
            );

            $total = max(0, $subtotal - $couponDiscount + $shipping + $taxAmount);

            // 4) decrease stock for new items
            $this->decreaseStockOrFail($itemsPayload);

            // 5) update order core fields
            $customer = !empty($data['customer_id'])
                ? Customer::find($data['customer_id'])
                : null;

            $order->update([
                'customer_id' => $customer?->id,
                'status' => $data['status'],

                'coupon_id' => $couponId,
                'coupon_code' => $couponCode,
                'coupon_discount' => $couponDiscount,

                'tax_rate_id' => $taxRateId,
                'tax_amount' => $taxAmount,

                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,

                'billing_address' => $data['billing_address'] ?? $customer?->billing_address,
                'shipping_address' => $data['shipping_address'] ?? $customer?->shipping_address,
                'note' => $data['note'] ?? null,
            ]);

            // 6) replace items (simple & safe)
            $order->items()->delete();
            $order->items()->createMany($itemsPayload);

            // 7) payment update or create
            $pay = $data['payment'];
            $amountPaid = (float)($pay['amount_paid'] ?? 0);
            $amountDue = max(0, $total - $amountPaid);
            $paymentStatus = $this->calcPaymentStatus($pay['method'], $amountPaid, $amountDue);

            if ($order->payment) {
                $order->payment->update([
                    'method' => $pay['method'],
                    'transaction_id' => $pay['transaction_id'] ?? null,
                    'amount_paid' => $amountPaid,
                    'amount_due' => $amountDue,
                    'status' => $paymentStatus,
                    'paid_at' => $paymentStatus === 'paid' ? now() : null,
                ]);
            } else {
                $order->payment()->create([
                    'method' => $pay['method'],
                    'transaction_id' => $pay['transaction_id'] ?? null,
                    'amount_paid' => $amountPaid,
                    'amount_due' => $amountDue,
                    'status' => $paymentStatus,
                    'paid_at' => $paymentStatus === 'paid' ? now() : null,
                ]);
            }

            return redirect()
                ->route('crm.orders.show', $order)
                ->with('success', 'Order updated successfully.');
        });
    }

    public function destroy(Order $order)
    {
        return DB::transaction(function () use ($order) {
            $order->load('items');

            // restore stock
            foreach ($order->items as $it) {
                if (!$it->product_id) continue;
                $p = Product::where('id', $it->product_id)->lockForUpdate()->first();
                if ($p && $p->stock !== null) {
                    $p->stock = (int)$p->stock + (int)$it->qty;
                    $p->save();
                }
            }

            $order->delete();

            return back()->with('success', 'Order deleted.');
        });
    }

    // -------------------- Validation --------------------

    private function validateOrderRequest(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'status' => ['required', Rule::in(['processing', 'complete', 'hold'])],

            'coupon_code' => ['nullable', 'string', 'max:50'],
            'tax_rate_id' => ['nullable', 'integer', 'exists:tax_rates,id'],
            'shipping' => ['nullable', 'numeric', 'min:0'],

            'billing_address' => ['nullable', 'string', 'max:4000'],
            'shipping_address' => ['nullable', 'string', 'max:4000'],
            'note' => ['nullable', 'string', 'max:4000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],

            'payment.method' => ['required', Rule::in(['cod', 'bkash', 'nagad', 'rocket'])],
            'payment.transaction_id' => ['nullable', 'string', 'max:120'],
            'payment.amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    // -------------------- Helpers --------------------

    private function buildItemsFromProducts(array $items): array
    {
        $payload = [];
        $subtotal = 0;

        foreach ($items as $it) {
            $p = Product::find($it['product_id']);
            $qty = (int)$it['qty'];

            // server trusted price (you can allow override if you want, but then validate carefully)
            $price = (float)($p->sale_price ?? $p->regular_price ?? 0);

            $line = $qty * $price;
            $subtotal += $line;

            $payload[] = [
                'product_id' => $p->id,
                'product_name' => $p->name,
                'sku' => $p->sku,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $line,
            ];
        }

        return [round($subtotal, 2), $payload];
    }

    private function decreaseStockOrFail(array $itemsPayload): void
    {
        foreach ($itemsPayload as $row) {
            if (empty($row['product_id'])) continue;

            $p = Product::where('id', $row['product_id'])->lockForUpdate()->first();

            if ($p && $p->stock !== null) {
                $need = (int)$row['qty'];
                $have = (int)$p->stock;

                if ($need > $have) {
                    abort(422, "Not enough stock for {$p->name}. Available: {$have}");
                }

                $p->stock = $have - $need;
                $p->save();
            }
        }
    }

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

    private function applyTax($taxRateId, float $base): array
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
        if ($method === 'cod') return 'pending';
        return ($due <= 0.00001 && $paid > 0) ? 'paid' : 'pending';
    }

    private function makeOrderNumber(): string
    {
        return 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }
}
