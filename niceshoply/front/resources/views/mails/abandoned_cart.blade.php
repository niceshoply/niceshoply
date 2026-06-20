{{--
  弃购召回邮件模板
--}}
<p>{{ __('front/abandoned_cart.greeting') }}</p>

<p>{{ __('front/abandoned_cart.body_intro', ['amount' => currency_format($abandonedCart->cart_total, $abandonedCart->currency_code)]) }}</p>

@if(!empty($abandonedCart->cart_snapshot))
<ul>
  @foreach($abandonedCart->cart_snapshot as $item)
  <li>{{ $item['product_name'] ?? '' }} × {{ $item['quantity'] ?? 1 }}</li>
  @endforeach
</ul>
@endif

@if($abandonedCart->coupon_code)
<p><strong>{{ __('front/abandoned_cart.coupon_hint', ['code' => $abandonedCart->coupon_code]) }}</strong></p>
@endif

<p><a href="{{ $cartUrl }}">{{ __('front/abandoned_cart.checkout_cta') }}</a></p>

<p>{{ __('front/abandoned_cart.footer') }}</p>
