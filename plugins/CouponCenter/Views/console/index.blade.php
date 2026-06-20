@extends('console::layouts.app')

@section('title', __('CouponCenter::common.title'))

@section('content')
  @unless($hasCoupons)
    <div class="alert alert-warning">{{ __('CouponCenter::common.no_coupon_plugin') }}</div>
  @endunless

  <div class="card mb-3"><div class="card-body text-center">
    <div class="text-muted small">{{ __('CouponCenter::common.total_claims') }}</div>
    <div class="fs-3 fw-bold">{{ number_format($totalClaims) }}</div>
  </div></div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card"><div class="card-header">{{ __('CouponCenter::common.coupon') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead><tr>
              <th>{{ __('CouponCenter::common.coupon') }}</th><th>{{ __('CouponCenter::common.code') }}</th><th>{{ __('CouponCenter::common.claims') }}</th>
            </tr></thead>
            <tbody>
            @forelse($stats as $s)
              <tr><td>{{ $s['name'] }}</td><td><code>{{ $s['code'] }}</code></td><td>{{ $s['claims'] }}</td></tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-4">{{ __('CouponCenter::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card"><div class="card-header">{{ __('CouponCenter::common.api_title') }}</div>
        <div class="card-body small">
          <pre class="bg-light p-2 mb-0" style="white-space:pre-wrap">GET  /coupon-center/list
POST /coupon-center/claim   {coupon_id}
GET  /coupon-center/mine</pre>
        </div>
      </div>
    </div>
  </div>
@endsection
