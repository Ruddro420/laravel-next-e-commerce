<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Receipt A4 - {{ $order->order_number }}</title>
  <style>
    body{ font-family: Arial, sans-serif; margin: 24px; }
    .wrap{ max-width: 820px; margin: 0 auto; }
    .row{ display:flex; justify-content:space-between; }
    table{ width:100%; border-collapse:collapse; margin-top: 12px; }
    th,td{ border:1px solid #ddd; padding:8px; font-size: 13px; }
    th{ background:#f6f6f6; text-align:left; }
    .right{ text-align:right; }
    @media print { button{ display:none; } }
  </style>
</head>
<body>
<div class="wrap">
  <button onclick="window.print()">Print</button>
  <h2>ShopPulse</h2>
  <div class="row">
    <div>
      <div><b>Order:</b> {{ $order->order_number }}</div>
      <div><b>Date:</b> {{ $order->created_at->format('Y-m-d H:i') }}</div>
      <div><b>Customer:</b> {{ $order->customer?->name ?? 'Walk-in' }}</div>
    </div>
    <div class="right">
      <div><b>Status:</b> {{ ucfirst($order->status) }}</div>
      <div><b>Payment:</b> {{ strtoupper($order->payment?->method ?? '-') }}</div>
      <div><b>TXN:</b> {{ $order->payment?->transaction_id ?? '-' }}</div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>Item</th>
        <th>SKU</th>
        <th class="right">Qty</th>
        <th class="right">Price</th>
        <th class="right">Line</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order->items as $i)
        <tr>
          <td>{{ $i->product_name }}</td>
          <td>{{ $i->sku }}</td>
          <td class="right">{{ $i->qty }}</td>
          <td class="right">৳{{ number_format($i->price,2) }}</td>
          <td class="right">৳{{ number_format($i->line_total,2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table>
    <tr><th class="right">Subtotal</th><td class="right">৳{{ number_format($order->subtotal,2) }}</td></tr>
    <tr><th class="right">Discount</th><td class="right">৳{{ number_format($order->coupon_discount,2) }}</td></tr>
    <tr><th class="right">Shipping</th><td class="right">৳{{ number_format($order->shipping,2) }}</td></tr>
    <tr><th class="right">Tax</th><td class="right">৳{{ number_format($order->tax_amount,2) }}</td></tr>
    <tr><th class="right">Total</th><td class="right"><b>৳{{ number_format($order->total,2) }}</b></td></tr>
  </table>

  <p><b>Billing:</b> {{ $order->billing_address }}</p>
  <p><b>Shipping:</b> {{ $order->shipping_address }}</p>
</div>
</body>
</html>
