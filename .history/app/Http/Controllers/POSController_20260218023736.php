<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Coupon;
use App\Models\TaxRate;
use App\Models\PosHold;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class POSController extends Controller
{
    public function index()
    {
        $customers = Customer::orderBy('name')->get();
        $taxRates  = TaxRate::where('is_active', true)->orderBy('name')->get();
        $holds     = PosHold::with('customer')->latest()->limit(10)->get();

        return view('pages.pos.index', compact('customers', 'taxRates', 'holds'));
    }

    // GET /pos/products?q=...
    public function products(Request $request)
    {
        $q = $request->get('q');

        $products = Product::query()
            ->when($q, function ($qr) use ($q) {
                $qr->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%")
                    ->orWhere('barcode', 'like', "%$q%");
            })
            ->where('is_active', true)
            ->with(['variants'])
            ->limit(24)
            ->get();

        $items = $products->map(function ($p) {
            $basePrice = (float)($p->sale_price ?? $p->regular_price ?? 0);

            $out = [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'barcode' => $p->barcode,
                'type' => $p->product_type,
                'price' => $basePrice,
                'stock' => $p->stock,
                'image' => $p->featured_image ? asset('storage/' . $p->featured_image) : null,
            ];

            if ($p->product_type === 'variable') {
                $out['variants'] = $p->variants->map(function ($v) {
                    $attrs = is_array($v->attributes) ? $v->attributes : (json_decode($v->attributes, true) ?: []);
                    $label = collect($attrs)->map(fn($val, $key) => $key . ': ' . $val)->implode(', ');
                    $price = (float)($v->sale_price ?? $v->regular_price ?? 0);

                    return [
                        'id' => $v->id,
                        'label' => $label ?: ('Variant #' . $v->id),
                        'sku' => $v->sku,
                        'price' => $price,
                        'stock' => $v->stock,
                        'image' => $v->image_path ? asset('storage/' . $v->image_path) : null,
                    ];
                })->values();
            }

            return $out;
        })->values();

        return response()->json($items);
    }

    // POST /pos/customers (AJAX quick add)
    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'billing_address' => ['nullable', 'string', 'max:4000'],
            'shipping_address' => ['nullable', 'string', 'max:4000'],
        ]);

        $c = Customer::create($data);

        return response()->json([
            'ok' => true,
            'customer' => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'billing_address' => $c->billing_address,
                'shipping_address' => $c->shipping_address,
            ]
        ]);
    }

    // GET /pos/holds (page)
    public function holds(Request $request)
    {
        $q = $request->get('q');

        $holds = PosHold::with('customer')
            ->when($q, function ($qr) use ($q) {
                $qr->where('ref', 'like', "%$q%")->orWhere('title', 'like', "%$q%");
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('pages.pos.holds', compact('holds', 'q'));
    }

    // POST /pos/holds (AJAX save)
    public function storeHold(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:190'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'payload' => ['required', 'array'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
        ]);

        $hold = PosHold::create([
            'ref' => 'HOLD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'title' => $data['title'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'payload' => $data['payload'],
            'subtotal' => (float)($data['subtotal'] ?? 0),
            'total' => (float)($data['total'] ?? 0),
        ]);

        return response()->json([
            'ok' => true,
            'hold' => [
                'id' => $hold->id,
                'ref' => $hold->ref,
            ],
        ]);
    }

    // GET /pos/holds/{hold} (AJAX load)
    public function showHold(PosHold $hold)
    {
        $hold->load('customer');

        return response()->json([
            'ok' => true,
            'hold' => [
                'id' => $hold->id,
                'ref' => $hold->ref,
                'payload' => $hold->payload,
            ],
        ]);
    }

    public function deleteHold(PosHold $hold)
    {
        $hold->delete();
        return back()->with('success', 'Hold deleted.');
    }

    // POST /pos/checkout (AJAX)
    public function checkout(Request $request)
    {
        $data = $request->validate([
            'hold_id' => ['nullable', 'integer', 'exists:pos_holds,id'],

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
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],

            'payment.method' => ['required', Rule::in(['cod', 'bkash', 'nagad', 'rocket'])],
            'payment.transaction_id' => ['nullable', 'string', 'max:120'],
            'payment.amount_paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data) {
            $customer = $data['customer_id'] ? Customer::find($data['customer_id']) : null;

            // Build trusted items payload (supports variants)
            [$subtotal, $itemsPayload] = $this->buildItemsTrusted($data['items']);

            // Coupon
            [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
                $data['coupon_code'] ?? null,
                $subtotal
            );

            // Tax base = subtotal - discount + shipping
            $baseForTax = $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0);

            // Tax
            [$taxRateId, $taxAmount] = $this->applyTax(
                $data['tax_rate_id'] ?? null,
                $baseForTax
            );

            $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

            // Stock check + decrease (product or variant)
            $this->decreaseStockOrFail($itemsPayload);

            $order = Order::create([
                'order_number' => $this->makeOrderNumber(),
                'customer_id' => $customer?->id,
                'status' => $data['status'],

                'coupon_id' => $couponId,
                'coupon_code' => $couponCode,
                'coupon_discount' => $couponDiscount,

                'tax_rate_id' => $taxRateId,
                'tax_amount' => $taxAmount,

                'subtotal' => $subtotal,
                'shipping' => (float)($data['shipping'] ?? 0),
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

            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used_count');
            }

            if (!empty($data['hold_id'])) {
                PosHold::where('id', $data['hold_id'])->delete();
            }

            return response()->json([
                'ok' => true,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'redirect' => route('crm.orders.show', $order),
                'receipt_a4' => route('pos.receipt.a4', $order),
                'receipt_58' => route('pos.receipt.58', $order),
                'receipt_80' => route('pos.receipt.80', $order),
            ]);
        });
    }

    // =============== Helpers ===============

    // Builds order items and subtotal using DB-trusted product/variant prices
    private function buildItemsTrusted(array $items): array
    {
        $payload = [];
        $subtotal = 0;

        foreach ($items as $it) {
            $productId = (int)$it['product_id'];
            $variantId = isset($it['variant_id']) ? (int)$it['variant_id'] : null;
            $qty = (int)$it['qty'];

            $p = Product::findOrFail($productId);

            // If variant_id provided, validate it belongs to product
            if ($variantId) {
                $v = ProductVariant::where('id', $variantId)
                    ->where('product_id', $productId)
                    ->firstOrFail();

                $attrs = is_array($v->attributes) ? $v->attributes : (json_decode($v->attributes, true) ?: []);
                $variantLabel = collect($attrs)->map(fn($val, $key) => $key . ': ' . $val)->implode(', ');
                $price = (float)($v->sale_price ?? $v->regular_price ?? 0);

                $line = $qty * $price;
                $subtotal += $line;

                $payload[] = [
                    'product_id' => $p->id,
                    'variant_id' => $v->id,
                    'variant_label' => $variantLabel ?: ('Variant #' . $v->id),

                    'product_name' => $p->name,
                    'sku' => $v->sku ?: $p->sku,

                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $line,
                ];
            } else {
                // simple / downloadable
                $price = (float)($p->sale_price ?? $p->regular_price ?? 0);

                $line = $qty * $price;
                $subtotal += $line;

                $payload[] = [
                    'product_id' => $p->id,
                    'variant_id' => null,
                    'variant_label' => null,

                    'product_name' => $p->name,
                    'sku' => $p->sku,

                    'qty' => $qty,
                    'price' => $price,
                    'line_total' => $line,
                ];
            }
        }

        return [$subtotal, $payload];
    }

    // Stock check + decrease (variant stock if variant_id exists, otherwise product stock)
    private function decreaseStockOrFail(array $itemsPayload): void
    {
        foreach ($itemsPayload as $row) {
            $need = (int)($row['qty'] ?? 0);
            if ($need <= 0) continue;

            // Variant stock
            if (!empty($row['variant_id'])) {
                $v = ProductVariant::where('id', $row['variant_id'])->lockForUpdate()->first();
                if ($v && $v->stock !== null) {
                    $have = (int)$v->stock;
                    if ($need > $have) {
                        abort(422, "Not enough stock for {$row['product_name']} ({$row['variant_label']}). Available: {$have}");
                    }
                    $v->stock = $have - $need;
                    $v->save();
                }
                continue;
            }

            // Product stock
            $p = Product::where('id', $row['product_id'])->lockForUpdate()->first();
            if ($p && $p->stock !== null) {
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

    // Receipts
    public function receiptA4(Order $order)
    {
        $order->load(['items', 'customer', 'payment', 'taxRate']);
        return view('pages.pos.receipts.a4', compact('order'));
    }

    public function receipt58(Order $order)
    {
        $order->load(['items', 'customer', 'payment', 'taxRate']);
        return view('pages.pos.receipts.thermal58', compact('order'));
    }

    public function receipt80(Order $order)
    {
        $order->load(['items', 'customer', 'payment', 'taxRate']);
        return view('pages.pos.receipts.thermal80', compact('order'));
    }

    // Barcode labels
    public function barcodeLabels()
    {
        return view('pages.pos.barcode-labels');
    }

    public function barcodeProducts(Request $request)
    {
        $q = trim((string)$request->get('q', ''));

        $rows = Product::query()
            ->where('is_active', true)
            ->when($q, function ($qr) use ($q) {
                $qr->where('name', 'like', "%$q%")
                    ->orWhere('sku', 'like', "%$q%")
                    ->orWhere('barcode', 'like', "%$q%");
            })
            ->latest()
            ->limit(30)
            ->get()
            ->map(function ($p) {
                $price = (float)($p->sale_price ?? $p->regular_price ?? 0);
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'barcode' => $p->barcode,
                    'price' => $price,
                    'stock' => $p->stock,
                ];
            });

        return response()->json($rows);
    }

    public function barcodeLabelsPrint(Request $request)
    {
        $data = $request->validate([
            'size' => ['required', 'string'],
            'show_price' => ['nullable'],
            'show_sku' => ['nullable'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $sizes = [
            '38x25' => ['w' => 38, 'h' => 25, 'cols' => 3],
            '50x25' => ['w' => 50, 'h' => 25, 'cols' => 2],
            '70x30' => ['w' => 70, 'h' => 30, 'cols' => 2],
            '80x40' => ['w' => 80, 'h' => 40, 'cols' => 1],
        ];
        $meta = $sizes[$data['size']] ?? $sizes['50x25'];

        $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $labels = [];
        foreach ($data['items'] as $it) {
            $p = $products->get($it['product_id']);
            if (!$p) continue;

            $barcode = $p->barcode;
            if (!$barcode) {
                $barcode = 'SP' . str_pad((string)$p->id, 6, '0', STR_PAD_LEFT) . strtoupper(Str::random(4));
                if (Schema::hasColumn('products', 'barcode')) {
                    $p->barcode = $barcode;
                    $p->save();
                }
            }

            $price = (float)($p->sale_price ?? $p->regular_price ?? 0);
            $qty = (int)$it['qty'];

            for ($i = 0; $i < $qty; $i++) {
                $labels[] = [
                    'name' => $p->name,
                    'sku' => $p->sku,
                    'barcode' => $barcode,
                    'price' => $price,
                ];
            }
        }

        return view('pages.pos.barcode-print', [
            'labels' => $labels,
            'meta' => $meta,
            'size' => $data['size'],
            'show_price' => $request->has('show_price'),
            'show_sku' => $request->has('show_sku'),
        ]);
    }
}
