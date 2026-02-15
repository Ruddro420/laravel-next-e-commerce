<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Print Barcodes</title>
  <style>
    @page { margin: 6mm; }

    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    .toolbar {
      padding: 10px;
      border-bottom: 1px solid #ddd;
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .paper {
      padding: 8mm;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat({{ $meta['cols'] }}, {{ $meta['w'] }}mm);
      gap: 3mm;
      align-items: start;
    }

    .label {
      width: {{ $meta['w'] }}mm;
      height: {{ $meta['h'] }}mm;
      border: 1px dashed rgba(0,0,0,.2);
      padding: 2mm;
      box-sizing: border-box;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .name {
      font-size: 10px;
      font-weight: 700;
      line-height: 1.15;
      max-height: 12mm;
      overflow: hidden;
    }

    .meta {
      font-size: 9px;
      color: #333;
      display: flex;
      justify-content: space-between;
      gap: 6px;
      margin-top: 1mm;
    }

    .barcode-wrap {
      width: 100%;
      display: flex;
      justify-content: center;
      margin-top: 1mm;
    }

    svg.barcode {
      width: 100%;
      height: 12mm;
    }

    .code {
      text-align: center;
      font-size: 9px;
      margin-top: 0.5mm;
      letter-spacing: 0.4px;
    }

    @media print {
      .toolbar { display: none; }
      .label { border: none; }
      .paper { padding: 0; }
      @page { margin: 6mm; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <button onclick="window.print()">Print</button>
    <div style="font-size:12px;color:#555;">
      Size: <b>{{ $size }}</b> • Labels: <b>{{ count($labels) }}</b>
      • SKU: <b>{{ $show_sku ? 'ON' : 'OFF' }}</b> • Price: <b>{{ $show_price ? 'ON' : 'OFF' }}</b>
    </div>
  </div>

  <div class="paper">
    <div class="grid">
      @foreach($labels as $i => $l)
        <div class="label">
          <div>
            <div class="name">{{ $l['name'] }}</div>
            <div class="meta">
              <div>
                @if($show_sku)
                  SKU: {{ $l['sku'] ?? '—' }}
                @endif
              </div>
              <div>
                @if($show_price)
                  ৳{{ number_format($l['price'],2) }}
                @endif
              </div>
            </div>
          </div>

          <div>
            <div class="barcode-wrap">
              <svg class="barcode" id="bc{{ $i }}"></svg>
            </div>
            <div class="code">{{ $l['barcode'] }}</div>
          </div>
        </div>
      @endforeach
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
  <script>
    (function(){
      const labels = @json($labels);

      labels.forEach((l, i) => {
        const el = document.getElementById('bc' + i);
        if(!el) return;

        // CODE128 works with alphanumeric like SP000001ABCD
        JsBarcode(el, String(l.barcode), {
          format: "CODE128",
          displayValue: false,
          margin: 0,
          height: 40
        });
      });
    })();
  </script>
</body>
</html>
