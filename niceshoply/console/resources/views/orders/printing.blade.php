<!DOCTYPE html>
<html dir="{{ console_locale_direction() }}" lang="{{ console_locale_code() }}">

<head>
  <meta charset="UTF-8" />
  <title>{{ config('app.name') }} {{ __('console/order.ship_list') }}</title>
  <link href="{{ build_asset('console/css/bootstrap.css') }}" rel="stylesheet">
  <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('vendor/jsbarcode/JsBarcode.all.min.js') }}"></script>

  <style media="print">
    .printer {
      display: none;
    }
    .btn {
      display: none;
    }
  </style>
</head>

<body>
  <div class="container">
    <div id="print-button">
      <p style="text-align: right;">
        <button class="btn btn-success right" type="button" onclick="window.print()"
          class="printer">{{ __('console/order.print') }}</button>
      </p>
    </div>

    <div style="page-break-after: always;">
      <h1 style="text-align: center;">{{ config('app.name') }} {{ __('console/order.ship_list') }}</h1>
      <table class="table">
        <tbody>
          <tr>
            <td>
              <b>{{ __('console/order.shipping_customer_name') }}: </b> {{ $order['shipping_customer_name'] }}<br />
              <b>{{ __('console/order.telephone') }}: </b> {{ $order['shipping_telephone'] }}<br />
              <b>{{ __('console/order.email') }}: </b> {{ $order['email'] }}<br />
              <b>{{ __('console/order.shipping_address') }}:
              </b>
              {{ $order['shipping_customer_name'] . '(' . $order['shipping_telephone'] . ')' . ' ' . $order['shipping_address_1'] . ' ' . $order['shipping_address_2'] . ' ' . $order['shipping_city'] . ' ' . $order['shipping_zone'] . ' ' . $order['shipping_country'] }}
              <br />
            </td>
            <td style="width: 50%;">
              <b>{{ __('console/order.number') }}: {{ $order['number'] }} </b> <br>
              <b>{{ __('console/order.created_at') }}: </b> {{ $order['created_at'] }}<br>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="table table-bordered">
        <thead>
          <tr>
            <td><b>{{ __('console/order.index') }}</b></td>
            <td><b>{{ __('console/order.image') }}</b></td>
            <td><b>{{ __('console/order.product') }}</b></td>
            <td><b>{{ __('console/order.sku_code') }}</b></td>
            <td class="text-right"><b>{{ __('console/order.quantity') }}</b></td>
            <td class="text-right"><b>{{ __('console/order.unit_price') }}</b></td>
            <td class="text-right"><b>{{ __('console/order.total') }}</b></td>
          </tr>
        </thead>
        <tbody>
          @if ($order->items)
            @foreach ($order->items as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td><img class="img-thumbnail" src="{{ $item['image'] }}" style="width: 30px;"></td>
                <td>{{ $item['name'] }}
                  @if ($item->productSku->variantLabel ?? '')
                    <br /><span class="small fst-italic">{{ $item->productSku->variantLabel }}</span>
                  @endif
                </td>
                <td>{{ $item['product_sku'] }}</td>
                <td class="text-right">{{ $item['quantity'] }}</td>
                <td class="text-right">{{ currency_format($item['price'], $order['currency_code'], $order['currency_value']) }}</td>
                <td class="text-right">{{ currency_format($item['subtotal'], $order['currency_code'], $order['currency_value']) }}</td>
              </tr>
            @endforeach
          @endif
        </tbody>
      </table>
      <table class="table d-flex justify-content-around">
        <tbody>
          <tr>
            <td colspan="3">
              <div> <b>{{ __('console/order.telephone') }}: </b> {{ $order['shipping_telephone'] }}</div>
              <div>{{ __('console/order.email') }}: </b> {{ $order['email'] }}</div>
              <div>{{ __('console/order.website') }}: </b> <a
                  href="{{ config('app.url') }}">{{ config('app.url') }}</a></div>
            </td>
          </tr>
        </tbody>

        <thead style="border-top: 1px solid #ddd;">
          @foreach ($order->fees as $total)
            <tr>
              <td><b>{{ $total->title }}</b>: {{ $total->value_format }}</td>
            </tr>
          @endforeach
          <tr>
            <td><b>{{ __('console/order.total') }}</b>: {{ currency_format($order['total'], $order['currency_code'], $order['currency_value']) }}</td>
          </tr>
        </thead>
      </table>
      <div class="d-flex justify-content-center">
        <img id="barcode"/>
      </div>
    </div>
  </div>
</body>
</html>

<script>
  $(document).ready(function() {
    let orderNumber = '{{ $order['number'] }}';
    JsBarcode("#barcode", orderNumber, {
      format: "CODE128",
      displayValue: true,
      fontSize: 16,
      lineColor: "#000",
      width: 2,
      height: 100,
    });
  });
</script>
