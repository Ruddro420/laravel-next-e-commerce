<?php 

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

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $orders = Order::with(['customer','payment'])
            ->when($q, function($qr) use ($q){
                $qr->where('order_number','like',"%$q%")
                   ->orWhereHas('customer', function($c) use ($q){
                       $c->where('name','like',"%$q%")->orWhere('email','like',"%$q%");
                   });
            })
            ->latest()->paginate(10)->withQueryString();

        return view('pages.crm.orders.index', compact('orders','q'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $taxRates = TaxRate::where('is_active',true)->orderBy('name')->get();

        // Needed for dropdown in UI (recommended)
        $products = Product::where('is_active',1)->orderBy('name')->get();

        return view('pages.crm.orders.create', compact('customers','taxRates','products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateOrder($request);

        $customer = Customer::find($data['customer_id']);
        $data['order_number'] = $this->makeOrderNumber();

        [$subtotal, $itemsPayload] = $this->buildItems($request);

        // coupon calc
        [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
            $request->input('coupon_code'),
            $subtotal
        );

        // tax calc
        [$taxRateId, $taxAmount] = $this->applyTax(
            $request->input('tax_rate_id'),
            $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
        );

        $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

        // Create Order
        $order = Order::create([
            'order_number' => $data['order_number'],
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

        // Create Items
        $order->items()->createMany($itemsPayload);

        // ✅ STOCK: Deduct stock for each item
        $this->deductStockForItems($order, $itemsPayload);

        // payment
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

        return redirect()->route('crm.orders')->with('success','Order created successfully!');
    }

    public function show(Order $order)
    {
        $order->load(['customer','items','payment','taxRate']);
        return view('pages.crm.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $order->load(['customer','items','payment']);
        $customers = Customer::orderBy('name')->get();
        $taxRates = TaxRate::where('is_active',true)->orderBy('name')->get();
        $products = Product::where('is_active',1)->orderBy('name')->get();

        return view('pages.crm.orders.edit', compact('order','customers','taxRates','products'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $this->validateOrder($request);

        $customer = Customer::find($data['customer_id']);

        // ✅ STOCK: restore previous items stock before replacing
        $oldItems = $order->items()->get()->map(function($i){
            return [
                'product_id' => $i->product_id,
                'qty' => (int)$i->qty,
                'product_name' => $i->product_name,
            ];
        })->toArray();

        $this->restoreStockFromOldItems($order, $oldItems);

        // Build new items
        [$subtotal, $itemsPayload] = $this->buildItems($request);

        [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
            $request->input('coupon_code'),
            $subtotal
        );

        [$taxRateId, $taxAmount] = $this->applyTax(
            $request->input('tax_rate_id'),
            $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0)
        );

        $total = max(0, $subtotal - $couponDiscount + (float)($data['shipping'] ?? 0) + $taxAmount);

        $order->update([
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

        // Replace items
        $order->items()->delete();
        $order->items()->createMany($itemsPayload);

        // ✅ STOCK: deduct stock for new items
        $this->deductStockForItems($order, $itemsPayload);

        // payment update
        $pay = $data['payment'];
        $amountPaid = (float)($pay['amount_paid'] ?? 0);
        $amountDue = max(0, $total - $amountPaid);
        $paymentStatus = $this->calcPaymentStatus($pay['method'], $amountPaid, $amountDue);

        $order->payment()->updateOrCreate(
            ['order_id' => $order->id],
            [
                'method' => $pay['method'],
                'transaction_id' => $pay['transaction_id'] ?? null,
                'amount_paid' => $amountPaid,
                'amount_due' => $amountDue,
                'status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
            ]
        );

        return back()->with('success','Order updated successfully!');
    }

    public function destroy(Order $order)
    {
        // ✅ STOCK: restore stock before delete (optional but recommended)
        $oldItems = $order->items()->get()->map(function($i){
            return [
                'product_id' => $i->product_id,
                'qty' => (int)$i->qty,
                'product_name' => $i->product_name,
            ];
        })->toArray();

        $this->restoreStockFromOldItems($order, $oldItems);

        $order->delete();
        return redirect()->route('crm.orders')->with('success','Order deleted successfully!');
    }

    private function validateOrder(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['nullable','integer','exists:customers,id'],
            'status' => ['required', Rule::in(['processing','complete','hold'])],

            'coupon_code' => ['nullable','string','max:50'],
            'tax_rate_id' => ['nullable','integer','exists:tax_rates,id'],

            'shipping' => ['nullable','numeric','min:0'],
            'billing_address' => ['nullable','string','max:4000'],
            'shipping_address' => ['nullable','string','max:4000'],
            'note' => ['nullable','string','max:4000'],

            'items' => ['required','array','min:1'],

            // ✅ ADD product_id
            'items.*.product_id' => ['nullable','integer','exists:products,id'],

            'items.*.product_name' => ['required','string','max:200'],
            'items.*.sku' => ['nullable','string','max:120'],
            'items.*.qty' => ['required','integer','min:1'],
            'items.*.price' => ['required','numeric','min:0'],

            'payment.method' => ['required', Rule::in(['cod','bkash','nagad','rocket'])],
            'payment.transaction_id' => ['nullable','string','max:120'],
            'payment.amount_paid' => ['nullable','numeric','min:0'],
        ], [
            'payment.method.required' => 'Payment method is required.',
        ]);
    }

    private function buildItems(Request $request): array
    {
        $items = $request->input('items', []);
        $payload = [];
        $subtotal = 0;

        foreach ($items as $it) {
            $qty = (int)$it['qty'];
            $price = (float)$it['price'];
            $line = $qty * $price;
            $subtotal += $line;

            $payload[] = [
                // ✅ keep product_id
                'product_id' => !empty($it['product_id']) ? (int)$it['product_id'] : null,

                'product_name' => $it['product_name'],
                'sku' => $it['sku'] ?? null,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $line,
            ];
        }

        return [$subtotal, $payload];
    }

    // ✅ STOCK helpers
    private function deductStockForItems(Order $order, array $itemsPayload): void
    {
        foreach ($itemsPayload as $it) {
            if (empty($it['product_id'])) continue;

            $p = Product::find($it['product_id']);
            if (!$p) continue;

            $qty = (int)$it['qty'];
            if ($qty <= 0) continue;

            StockService::move(
                $p,
                'out',
                $qty,
                $order->id,
                'Order placed/updated',
                $order->order_number
            );
        }
    }

    private function restoreStockFromOldItems(Order $order, array $oldItems): void
    {
        foreach ($oldItems as $it) {
            if (empty($it['product_id'])) continue;

            $p = Product::find($it['product_id']);
            if (!$p) continue;

            $qty = (int)$it['qty'];
            if ($qty <= 0) continue;

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

    // ---- Coupon/Tax/Payment helpers (same as yours) ----

    private function applyCoupon(?string $code, float $subtotal): array
    {
        $code = strtoupper(trim((string)$code));
        if (!$code) return [null, null, 0.0];

        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon) return [null, null, 0.0];
        if (!$coupon->is_active) return [null, null, 0.0];

        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) return [null, null, 0.0];
        if ($coupon->expires_at && $now->gt($coupon->expires_at)) return [null, null, 0.0];

        if ($subtotal < (float)$coupon->min_order_amount) return [null, null, 0.0];

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return [null, null, 0.0];
        }

        $discount = 0.0;
        if ($coupon->type === 'percent') {
            $discount = ($subtotal * (float)$coupon->value) / 100.0;
        } else {
            $discount = (float)$coupon->value;
        }

        $discount = min($discount, $subtotal);
        return [$coupon->id, $coupon->code, round($discount, 2)];
    }

    private function applyTax(?int $taxRateId, float $base): array
    {
        if (!$taxRateId) return [null, 0.0];

        $tax = TaxRate::where('id',$taxRateId)->where('is_active',true)->first();
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
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
    }
}
