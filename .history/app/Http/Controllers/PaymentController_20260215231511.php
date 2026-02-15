<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function edit(Order $order)
    {
        $order->load('payment');
        return view('pages.crm.orders.payment', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'method' => ['required', Rule::in(['cod','bkash','nagad','rocket'])],
            'transaction_id' => ['nullable','string','max:120'],
            'amount_paid' => ['required','numeric','min:0'],
        ]);

        $total = (float)$order->total;
        $paid = (float)$data['amount_paid'];
        $due = max(0, $total - $paid);

        $status = 'pending';
        if ($data['method'] !== 'cod' && $due <= 0.00001 && $paid > 0) $status = 'paid';

        $order->payment()->updateOrCreate(
            ['order_id'=>$order->id],
            [
                'method'=>$data['method'],
                'transaction_id'=>$data['transaction_id'] ?? null,
                'amount_paid'=>$paid,
                'amount_due'=>$due,
                'status'=>$status,
                'paid_at'=>$status==='paid' ? now() : null,
            ]
        );

        return back()->with('success','Payment updated successfully!');
    }
}
