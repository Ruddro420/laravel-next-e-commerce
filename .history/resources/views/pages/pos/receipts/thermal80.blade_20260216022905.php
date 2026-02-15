<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Receipt - {{ $order->order_number }}</title>
  <style>
    body{ font-family: monospace; margin:0; padding:10px; }
    .paper{ width: 58mm; }
    .row{ display:flex; justify-content:space-between; }
    .hr{ border-top:1px dashed #000; margin:8px 0; }
    .small{ font-size: 12px; }
    @media print { button{ display:none; } }
  </style>
</head>
<body>
<div class="paper">
  <button onclick="window.print()">Print</button>
  <div style="text-align:center;">
    <div><b>ShopPulse</b></div>
    <div class="small">POS Receipt</div>
  </div>

  <div class="hr"></div>
  <div class="small">Order: {{ $order->order_number }}</div>
  <div class="small">Date: {{ $order->created_at->format('Y-m-d H:i') }}</div>
  <div class="small">Customer: {{ $order->customer?->name ?? 'Walk-in' }}</div>

  <div class="hr"></div>
  @foreach($order->items as $i)
    <div class="small">{{ $i->product_name }}</div>
    <div class="row small">
      <div>{{ $i->qty }} x {{ number_format($i->price,2) }}</div>
      <div>{{ number_format($i->line_total,2) }}</div>
    </div>
  @endforeach

  <div class="hr"></div>
  <div class="row small"><div>Subtotal</div><div>{{ number_format($order->subtotal,2) }}</div></div>
  <div class="row small"><div>Discount</div><div>{{ number_format($order->coupon_discount,2) }}</div></div>
  <div class="row small"><div>Shipping</div><div>{{ number_format($order->shipping,2) }}</div></div>
  <div class="row small"><div>Tax</div><div>{{ number_format($order->tax_amount,2) }}</div></div>
  <div class="hr"></div>
  <div class="row"><div><b>Total</b></div><div><b>{{ number_format($order->total,2) }}</b></div></div>

  <div class="hr"></div>
  <div class="small">Payment: {{ strtoupper($order->payment?->method ?? '-') }}</div>
  <div class="small">TXN: {{ $order->payment?->transaction_id ?? '-' }}</div>

  <div class="hr"></div>
  <div class="small" style="text-align:center;">Thank you!</div>
</div>
</body>
</html>
