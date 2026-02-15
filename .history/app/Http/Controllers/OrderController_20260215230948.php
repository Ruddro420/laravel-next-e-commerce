<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Coupon;
use App\Models\TaxRate;
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
                   ->orWhereHas('customer', fn($c)=>$c->where('name','like',"%$q%")->orWhere('email','like',"%$q%"));
            })
            ->latest()->paginate(10)->withQueryString();

        return view('pages.crm.orders.index', compact('orders','q'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $taxRates = TaxRate::where('is_active',true)->orderBy('name')->get();
        return view('pages.crm.orders.create', compact('customers','taxRates'));
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
            $subtotal - $couponDiscount + (float)$data['shipping']
        );

        $total = max(0, $subtotal - $couponDiscount + (float)$data['shipping'] + $taxAmount);

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
            'shipping' => (float)$data['shipping'],
            'total' => $total,

            'billing_address' => $data['billing_address'] ?? $customer?->billing_address,
            'shipping_address' => $data['shipping_address'] ?? $customer?->shipping_address,

            'note' => $data['note'] ?? null,
        ]);

        $order->items()->createMany($itemsPayload);

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

        // increase coupon used_count if used
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
        return view('pages.crm.orders.edit', compact('order','customers','taxRates'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $this->validateOrder($request);

        $customer = Customer::find($data['customer_id']);

        [$subtotal, $itemsPayload] = $this->buildItems($request);

        // NOTE: we won't decrement old coupon used_count to keep it simple and safe.
        // You can implement coupon usage history later.

        [$couponId, $couponCode, $couponDiscount] = $this->applyCoupon(
            $request->input('coupon_code'),
            $subtotal
        );

        [$taxRateId, $taxAmount] = $this->applyTax(
            $request->input('tax_rate_id'),
            $subtotal - $couponDiscount + (float)$data['shipping']
        );

        $total = max(0, $subtotal - $couponDiscount + (float)$data['shipping'] + $taxAmount);

        $order->update([
            'customer_id' => $customer?->id,
            'status' => $data['status'],
            'coupon_id' => $couponId,
            'coupon_code' => $couponCode,
            'coupon_discount' => $couponDiscount,
            'tax_rate_id' => $taxRateId,
            'tax_amount' => $taxAmount,
            'subtotal' => $subtotal,
            'shipping' => (float)$data['shipping'],
            'total' => $total,
            'billing_address' => $data['billing_address'] ?? $customer?->billing_address,
            'shipping_address' => $data['shipping_address'] ?? $customer?->shipping_address,
            'note' => $data['note'] ?? null,
        ]);

        $order->items()->delete();
        $order->items()->createMany($itemsPayload);

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
                'product_name' => $it['product_name'],
                'sku' => $it['sku'] ?? null,
                'qty' => $qty,
                'price' => $price,
                'line_total' => $line,
            ];
        }
        return [$subtotal, $payload];
    }

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

        // Exclusive: add on top
        if ($tax->mode === 'exclusive') {
            $amount = ($base * (float)$tax->rate) / 100.0;
            return [$tax->id, round($amount, 2)];
        }

        // Inclusive: tax included inside base (we still store tax part)
        // taxPart = base - base/(1+rate)
        $rate = (float)$tax->rate;
        if ($rate <= 0) return [$tax->id, 0.0];

        $div = 1 + ($rate / 100.0);
        $taxPart = $base - ($base / $div);
        return [$tax->id, round($taxPart, 2)];
    }

    private function calcPaymentStatus(string $method, float $paid, float $due): string
    {
        // COD usually pending until delivered/paid
        if ($method === 'cod') return 'pending';

        // For mobile payments: if fully paid -> paid else pending
        return ($due <= 0.00001 && $paid > 0) ? 'paid' : 'pending';
    }

    private function makeOrderNumber(): string
    {
        return 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));
    }
}
