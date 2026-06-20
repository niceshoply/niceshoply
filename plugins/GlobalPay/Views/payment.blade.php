@php($redirect = $redirect_url ?? '')
@php($error = $error ?? '')
<div class="card w-max-560 m-auto"><div class="card-body text-center">
  <div class="fs-5 mb-3">{{ __('GlobalPay::common.title') }}</div>
  @if($error)<div class="alert alert-danger">{{ $error }}</div>
  @elseif($redirect)
    <p class="text-muted">{{ __('GlobalPay::common.redirect_tip') }}</p>
    <a href="{{ $redirect }}" class="btn btn-primary">{{ __('GlobalPay::common.continue_pay') }}</a>
    <script>setTimeout(function(){location.href=@json($redirect);},800);</script>
  @else<div class="alert alert-warning">{{ __('GlobalPay::common.no_url') }}</div>@endif
</div></div>
