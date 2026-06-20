@extends('layouts.mail')

@section('content')
  <tbody>
  <tr style="font-weight:300">
    <td style="width:3.2%;max-width:30px;"></td>
    <td style="max-width:480px;text-align:left;">
      <h1 style="font-size: 20px; line-height: 36px; margin: 0px 0px 22px;">
        {{ __('front/mail.return_update') }}
      </h1>
      <p style="font-size:14px;color:#333; line-height:24px; margin:0;">
        {{ __('front/mail.customer_name', ['name' => $order_return->customer_name]) }}
      </p>
      <p style="font-size:14px;color:#333; line-height:24px; margin:10px 0;">
        {{ __('front/mail.return_update_status', ['number' => $order_return->number]) }}
        <strong>{{ $order_return->status_format }}</strong>
      </p>

      <table
          style="width:100%;font-weight:300;margin-top:10px; margin-bottom:10px;border-collapse:collapse; background-color:#f8f9fa">
        <thead>
        <tr>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/return.number') }}</td>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/return.order_number') }}</td>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/return.quantity') }}</td>
          <td style="font-size:13px;padding: 7px 6px">{{ __('front/return.status') }}</td>
        </tr>
        </thead>
        <tbody>
        <tr>
          <td style="padding:7px;font-size:13px;">{{ $order_return->number }}</td>
          <td style="padding:7px;font-size:13px;">{{ $order_return->order_number }}</td>
          <td style="padding:7px;font-size:13px;">{{ $order_return->quantity }}</td>
          <td style="padding:7px;font-size:13px;">{{ $order_return->status_format }}</td>
        </tr>
        </tbody>
      </table>

      <table style="width:100%;font-weight:300;margin-top:10px; margin-bottom:10px;border-collapse:collapse;">
        <tbody>
        <tr>
          <td style="font-size:13px;border: 1px solid #eee; background-color: #f8f9fa;padding: 7px;width: 80px;text-align:center">{{ __('front/return.product_name') }}</td>
          <td style="font-size:13px;border: 1px solid #eee;padding: 7px;">{{ $order_return->product_name }}</td>
        </tr>
        @if ($order_return->comment)
          <tr>
            <td style="font-size:13px;border: 1px solid #eee; background-color: #f8f9fa;padding: 7px;text-align:center">{{ __('front/return.comment') }}</td>
            <td style="font-size:13px;border: 1px solid #eee;padding: 7px;">{{ $order_return->comment }}</td>
          </tr>
        @endif
        </tbody>
      </table>

      <dl style="font-size: 14px; color: rgb(51, 51, 51); line-height: 18px;">
        <dd style="margin: 0px 0px 6px; padding: 0px; font-size: 12px; line-height: 22px;">
          <p style="font-size: 14px; line-height: 26px; word-wrap: break-word; word-break: break-all; margin-top: 32px;">
            <br>
            <strong>{{ config('app.name') }}</strong>
          </p>
        </dd>
      </dl>

    </td>
    <td style="width:3.2%;max-width:30px;"></td>
  </tr>
  </tbody>
@endsection
