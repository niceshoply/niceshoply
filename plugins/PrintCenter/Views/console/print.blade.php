<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <title>{{ $type === 'picking' ? __('PrintCenter::common.picking_title') : __('PrintCenter::common.packing_title') }}</title>
  <style>
    body{font-family:sans-serif;font-size:12px;margin:20px;}
    .sheet{page-break-after:always;margin-bottom:40px;border:1px solid #ddd;padding:16px;}
    h2{margin:0 0 8px;font-size:16px;}
    table{width:100%;border-collapse:collapse;margin-top:12px;}
    th,td{border:1px solid #ccc;padding:6px;text-align:left;}
    @media print{.no-print{display:none;} .sheet{border:none;}}
  </style>
</head>
<body>
  <p class="no-print"><button onclick="window.print()">Print</button></p>
  @forelse($orders as $order)
    <div class="sheet">
      <h2>{{ $shop }} — {{ $type === 'picking' ? __('PrintCenter::common.picking_title') : __('PrintCenter::common.packing_title') }}</h2>
      <div>{{ __('PrintCenter::common.order_no') }}：{{ $order->number }} · {{ $order->created_at }}</div>
      @if($address)<div class="small text-muted">{{ $address }}</div>@endif
      @if($type === 'packing')
        <div style="margin-top:8px"><strong>{{ __('PrintCenter::common.ship_to') }}：</strong>
          {{ $order->shipping_customer_name ?? $order->customer_name ?? '' }} {{ $order->shipping_telephone ?? '' }}<br>
          {{ $order->shipping_address_1 ?? '' }} {{ $order->shipping_city ?? '' }} {{ $order->shipping_state ?? '' }} {{ $order->shipping_zipcode ?? '' }}
        </div>
      @endif
      <table>
        <thead><tr><th>{{ __('PrintCenter::common.product') }}</th><th>{{ __('PrintCenter::common.sku') }}</th><th>{{ __('PrintCenter::common.qty') }}</th></tr></thead>
        <tbody>
        @foreach($order->items ?? [] as $item)
          <tr><td>{{ $item->name }}</td><td>{{ $item->sku_code ?? '' }}</td><td>{{ $item->quantity }}</td></tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @empty
    <p>{{ __('PrintCenter::common.no_orders') }}</p>
  @endforelse
</body>
</html>
