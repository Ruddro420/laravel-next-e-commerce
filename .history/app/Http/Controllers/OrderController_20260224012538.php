<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Customer;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\TaxRate;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    // ---------------- CRM ----------------

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

        // ✅ Load products
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

            // ✅ Build items (maps product_id / id safely)
            [$subtotal, $itemsPayload] = $this->buildItemsSafe($data['items']);

            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
            );

            $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

            // stock
            $this->deductStockOrFail($itemsPayload);

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

            // discount/coupon fields (safe)
            if (Schema::hasColumn('orders', 'discount')) $orderAttrs['discount'] = $couponDiscount;
            if (Schema::hasColumn('orders', 'coupon_id')) $orderAttrs['coupon_id'] = $couponId;
            if (Schema::hasColumn('orders', 'coupon_code')) $orderAttrs['coupon_code'] = $couponCode;
            if (Schema::hasColumn('orders', 'coupon_discount')) $orderAttrs['coupon_discount'] = $couponDiscount;

            // tax fields (safe)
            if (Schema::hasColumn('orders', 'tax_rate_id')) $orderAttrs['tax_rate_id'] = $taxRateId;
            if (Schema::hasColumn('orders', 'tax_amount')) $orderAttrs['tax_amount'] = $taxAmount;

            $order = Order::create($orderAttrs);

            // ✅ items
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

            // sync orders payment columns if exist
            $updates = [];
            if (Schema::hasColumn('orders', 'payment_method')) $updates['payment_method'] = $pay['method'];
            if (Schema::hasColumn('orders', 'payment_status')) $updates['payment_status'] = ($paymentStatus === 'paid') ? 'paid' : 'unpaid';
            if (!empty($updates)) $order->update($updates);

            if ($couponId) Coupon::where('id', $couponId)->increment('used_count');

            return redirect()->route('crm.orders')->with('success', 'Order created successfully!');
        });
    }

    public function show(Order $order)
    {
        $order->load(['customer', 'items.product', 'payment', 'items.variant']);
        // dd($order->items->map(fn($item) => $item->variant));
        return view('pages.crm.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['customer', 'items.product', 'payment']);
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

            // restore old stock
            $oldItems = $order->items()->get()->map(fn($i) => [
                'product_id' => $i->product_id,
                'qty'        => (int)$i->qty,
            ])->toArray();

            $this->restoreStock($order, $oldItems);

            // new items
            [$subtotal, $itemsPayload] = $this->buildItemsSafe($data['items']);

            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
            );

            $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

            $this->deductStockOrFail($itemsPayload);

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

            // replace items
            $order->items()->delete();
            $order->items()->createMany($itemsPayload);

            // payment update
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

            // ✅ allow both keys for API: product_id OR id
            'items.*.product_id'    => ['nullable', 'integer', 'exists:products,id'],
            'items.*.id'            => ['nullable', 'integer', 'exists:products,id'],

            'items.*.product_name'  => ['required', 'string', 'max:200'],
            'items.*.sku'           => ['nullable', 'string', 'max:120'],
            'items.*.qty'           => ['required', 'integer', 'min:1'],
            'items.*.price'         => ['required', 'numeric', 'min:0'],

            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            // OPTIONAL: if sometimes camelCase comes from frontend
            'items.*.variantId'  => ['nullable', 'integer', 'exists:product_variants,id'],

            'payment'                       => ['required', 'array'],
            'payment.method' => ['required', Rule::in(['cod', 'cash_received', 'bkash', 'nagad', 'rocket'])],
            'payment.amount_paid'           => ['nullable', 'numeric', 'min:0'],
            'payment.transaction_id'        => [
                'nullable',
                'string',
                'max:120',
                Rule::requiredIf(fn() => in_array($request->input('payment.method'), ['bkash', 'nagad', 'rocket'])),
            ],
        ]);
    }

    // ---------------- ITEMS ----------------

    private function buildItemsSafe(array $items): array
    {
        $payload = [];
        $subtotal = 0.0;

        // Handle both id and product_id
        $items = array_map(function ($it) {
            if (empty($it['product_id']) && !empty($it['id'])) {
                $it['product_id'] = $it['id'];
            }

            // ✅ normalize variantId (camelCase) -> variant_id (snake_case)
            if (!isset($it['variant_id']) && isset($it['variantId'])) {
                $it['variant_id'] = $it['variantId'];
            }

            return $it;
        }, $items);

        $productIds = collect($items)->pluck('product_id')->filter()->unique()->values();
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Get variant IDs from items
        $variantIds = collect($items)->pluck('variant_id')->filter()->unique()->values();
        $variants   = ProductVariant::whereIn('id', $variantIds)->get()->keyBy('id');

        foreach ($items as $it) {
            $pid = !empty($it['product_id']) ? (int)$it['product_id'] : null;

            // IMPORTANT: Get variant_id directly from the item
            $vid = isset($it['variant_id']) && $it['variant_id'] !== null
                ? (int)$it['variant_id']
                : null;

            $p = $pid ? $products->get($pid) : null;

            // If product missing, set pid to null
            if ($pid && !$p) {
                $pid = null;
            }

            // Check if variant exists and belongs to product
            $v = $vid ? $variants->get($vid) : null;
            if ($vid && (!$v || ($pid && (int)$v->product_id !== (int)$pid))) {
                // Variant invalid - set to null
                $vid = null;
                $v = null;
            }

            $qty   = max(1, (int)($it['qty'] ?? 1));
            $price = (float)($it['price'] ?? 0);

            // Get name and sku
            $name = $p?->name ?? ($it['product_name'] ?? '');
            $sku  = $it['sku'] ?? ($p?->sku ?? null);

            if ($v) {
                if (!empty($v->sku)) $sku = $v->sku;
                if (!empty($v->name)) {
                    $name = $name . ' - ' . $v->name;
                } elseif (!empty($v->value)) {
                    $name = $name . ' - ' . $v->value;
                }
            }

            $line = $qty * $price;
            $subtotal += $line;

            // Build the row
            $row = [
                'product_id'   => $pid,
                'product_name' => $name,
                'sku'          => $sku,
                'qty'          => $qty,
                'price'        => $price,
                'line_total'   => $line,
            ];

            // ✅ ALWAYS add variant_id if it exists in the order_items table
            if (Schema::hasColumn('order_items', 'variant_id')) {
                $row['variant_id'] = $vid;  // This will be null if no variant
            }

            $payload[] = $row;
        }

        return [round($subtotal, 2), $payload];
    }

    // ---------------- STOCK ----------------

    private function deductStockOrFail(array $itemsPayload): void
    {
        foreach ($itemsPayload as $row) {
            $pid = $row['product_id'] ?? null;
            if (!$pid) continue;

            $p = Product::where('id', $pid)->lockForUpdate()->first();
            if (!$p) continue;

            $need = (int)($row['qty'] ?? 0);
            if ($need <= 0) continue;

            if ($p->stock !== null && $need > (int)$p->stock) {
                abort(422, "Not enough stock for {$p->name}. Available: {$p->stock}");
            }

            if (class_exists(StockService::class) && method_exists(StockService::class, 'move')) {
                StockService::move($p, 'out', $need, null, 'Order stock deducted', null);
            } else {
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

    // ---------------- API ----------------

    public function apiCheckout(Request $request)
    {
        $data = $this->validateOrder($request);

        $authCustomer = auth('customer')->user();
        if (!$authCustomer) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to place an order.',
            ], 401);
        }

        // override customer id
        $data['customer_id'] = $authCustomer->id;

        return DB::transaction(function () use ($data) {

            $customer = Customer::find($data['customer_id']);
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found.',
                ], 404);
            }

            [$subtotal, $itemsPayload] = $this->buildItemsSafe($data['items']);

            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
            );

            $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

            $this->deductStockOrFail($itemsPayload);

            $orderAttrs = [
                'order_number'     => $this->makeOrderNumber(),
                'customer_id'      => $customer->id,
                'status'           => $data['status'],
                'subtotal'         => $subtotal,
                'shipping'         => (float)($data['shipping'] ?? 0),
                'total'            => $total,
                'billing_address'  => $data['billing_address'] ?? $customer->billing_address,
                'shipping_address' => $data['shipping_address'] ?? $customer->shipping_address,
                'note'             => $data['note'] ?? null,
            ];

            if (Schema::hasColumn('orders', 'coupon_id')) $orderAttrs['coupon_id'] = $couponId;
            if (Schema::hasColumn('orders', 'coupon_code')) $orderAttrs['coupon_code'] = $couponCode;
            if (Schema::hasColumn('orders', 'coupon_discount')) $orderAttrs['coupon_discount'] = $couponDiscount;
            if (Schema::hasColumn('orders', 'discount')) $orderAttrs['discount'] = $couponDiscount;

            if (Schema::hasColumn('orders', 'tax_rate_id')) $orderAttrs['tax_rate_id'] = $taxRateId;
            if (Schema::hasColumn('orders', 'tax_amount')) $orderAttrs['tax_amount'] = $taxAmount;

            $order = Order::create($orderAttrs);
            $order->items()->createMany($itemsPayload);

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

            $updates = [];
            if (Schema::hasColumn('orders', 'payment_method')) $updates['payment_method'] = $pay['method'];
            if (Schema::hasColumn('orders', 'payment_status')) $updates['payment_status'] = ($paymentStatus === 'paid') ? 'paid' : 'unpaid';
            if (!empty($updates)) $order->update($updates);

            if ($couponId) Coupon::where('id', $couponId)->increment('used_count');

            $order->load([
                'customer',
                'payment',
                'items.product',
                'items.variant',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order'   => $this->transformOrderForApi($order),
            ], 201);
        });
    }

    public function apiGetOrderById($id)
    {
        $order = Order::with([
            'customer',
            'payment',
            'items.product',
            'items.variant',
        ])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_id' => $order->customer_id,
                'status' => $order->status,
                'subtotal' => (float)$order->subtotal,
                'shipping' => (float)($order->shipping ?? 0),
                'tax_amount' => (float)($order->tax_amount ?? 0),
                'discount' => (float)($order->discount ?? 0),
                'total' => (float)$order->total,
                'billing_address' => $order->billing_address,
                'shipping_address' => $order->shipping_address,
                'note' => $order->note ?? null,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,

                'items' => $order->items->map(function ($it) {
                    return [
                        'id' => $it->id,
                        'product_id' => $it->product_id,
                        'variant_id' => Schema::hasColumn('order_items', 'variant_id') ? ($it->variant_id ?? null) : null,
                        'product_name' => $it->product_name,
                        'sku' => $it->sku,
                        'qty' => (int)$it->qty,
                        'price' => (float)$it->price,
                        'line_total' => (float)$it->line_total,

                        // ✅ include variant object
                        'variant' => $it->variant ? [
                            'id' => $it->variant->id,
                            'product_id' => $it->variant->product_id,
                            'sku' => $it->variant->sku ?? null,
                            'regular_price' => isset($it->variant->regular_price) ? (float)$it->variant->regular_price : null,
                            'sale_price' => isset($it->variant->sale_price) ? (float)$it->variant->sale_price : null,
                            'stock' => isset($it->variant->stock) ? (int)$it->variant->stock : null,
                            'attributes' => $it->variant->attributes ?? null,

                            // ✅ add a human readable label
                            'label' => is_array($it->variant->attributes)
                                ? collect($it->variant->attributes)->map(fn($v, $k) => "{$k}: {$v}")->implode(', ')
                                : null,
                        ] : null,

                        // ✅ include product object (optional)
                        'product' => $it->product ? [
                            'id' => $it->product->id,
                            'name' => $it->product->name,
                            'sku' => $it->product->sku,
                        ] : null,
                    ];
                })->values(),

                'payment' => $order->payment ? [
                    'id' => $order->payment->id,
                    'method' => $order->payment->method,
                    'status' => $order->payment->status,
                    'transaction_id' => $order->payment->transaction_id,
                    'amount_paid' => (float)$order->payment->amount_paid,
                    'amount_due' => (float)$order->payment->amount_due,
                ] : null,
            ]
        ]);
    }

    public function apiCustomerOrders(Request $request)
    {
        try {
            $customer = Auth::guard('customer')->user();
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $perPage = max(1, min(50, (int) $request->get('per_page', 10)));

            $search = $request->get('q');
            $status = $request->get('status');
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');

            $orders = Order::query()
                ->where('customer_id', $customer->id)
                ->with(['items', 'payment'])
                ->when($search, fn($query) => $query->where('order_number', 'LIKE', "%{$search}%"))
                ->when($status, fn($query) => $query->where('status', $status))
                ->when($fromDate, fn($query) => $query->whereDate('created_at', '>=', $fromDate))
                ->when($toDate, fn($query) => $query->whereDate('created_at', '<=', $toDate))
                ->latest()
                ->paginate($perPage)
                ->withQueryString();

            $orders->getCollection()->transform(fn($o) => $this->transformOrderForApi($o));

            return response()->json([
                'success' => true,
                'orders'  => $orders,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function apiCustomerOrderStats(Request $request)
    {
        try {
            $customer = Auth::guard('customer')->user();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $stats = [
                'total_orders'       => Order::where('customer_id', $customer->id)->count(),
                'total_spent'        => (float) Order::where('customer_id', $customer->id)->sum('total'),
                'pending_orders'     => Order::where('customer_id', $customer->id)->where('status', 'pending')->count(),
                'processing_orders'  => Order::where('customer_id', $customer->id)->where('status', 'processing')->count(),
                'completed_orders'   => Order::where('customer_id', $customer->id)->where('status', 'delivered')->count(),
                'cancelled_orders'   => Order::where('customer_id', $customer->id)->where('status', 'cancelled')->count(),
                'last_order'         => Order::where('customer_id', $customer->id)->latest()->first(),
            ];

            return response()->json([
                'success' => true,
                'stats'   => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order statistics',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ---------------- API Transformer ----------------

    private function transformOrderForApi($order): array
    {
        return [
            'id'              => $order->id,
            'order_number'    => $order->order_number,
            'customer_id'     => $order->customer_id,
            'status'          => $order->status,
            'subtotal'        => (float) $order->subtotal,
            'tax_amount'      => (float) ($order->tax_amount ?? 0),
            'shipping'        => (float) ($order->shipping ?? 0),
            'discount'        => (float) ($order->discount ?? 0),
            'total'           => (float) $order->total,
            'payment_method'  => $order->payment_method ?? null,
            'payment_status'  => $order->payment_status ?? null,
            'billing_address' => $order->billing_address,
            'shipping_address' => $order->shipping_address,
            'note'            => $order->note ?? null,
            'created_at'      => $order->created_at,
            'updated_at'      => $order->updated_at,

            'items' => $order->items->map(function ($item) {
                return [
                    'id'           => $item->id,
                    'product_id'   => $item->product_id,
                    'variant_id'   => Schema::hasColumn('order_items', 'variant_id') ? ($item->variant_id ?? null) : null,
                    'product_name' => $item->product_name,
                    'sku'          => $item->sku,
                    'qty'          => (int) $item->qty,
                    'price'        => (float) $item->price,
                    'line_total'   => (float) $item->line_total,
                ];
            })->values(),

            'payment' => $order->payment ? [
                'id'             => $order->payment->id,
                'method'         => $order->payment->method,
                'status'         => $order->payment->status,
                'transaction_id' => $order->payment->transaction_id,
                'amount_paid'    => (float) $order->payment->amount_paid,
                'amount_due'     => (float) $order->payment->amount_due,
            ] : null,
        ];
    }
}
