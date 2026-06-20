@extends('console::layouts.app')

@section('title', __('SmartRecommend::common.title'))

@section('content')
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card text-center"><div class="card-body">
        <div class="text-muted small">{{ __('SmartRecommend::common.total_views') }}</div>
        <div class="fs-3 fw-bold">{{ number_format($totalViews) }}</div>
      </div></div>
    </div>
    <div class="col-md-4">
      <div class="card text-center"><div class="card-body">
        <div class="text-muted small">{{ __('SmartRecommend::common.total_visitors') }}</div>
        <div class="fs-3 fw-bold">{{ number_format($totalVisitors) }}</div>
      </div></div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card"><div class="card-header">{{ __('SmartRecommend::common.top_viewed') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead><tr><th>#</th><th>{{ __('SmartRecommend::common.product') }}</th><th>{{ __('SmartRecommend::common.views') }}</th></tr></thead>
            <tbody>
            @forelse($topViewed as $i => $row)
              <tr><td>{{ $i + 1 }}</td><td>{{ $row['name'] }}</td><td>{{ $row['views'] }}</td></tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-4">{{ __('SmartRecommend::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card"><div class="card-header">{{ __('SmartRecommend::common.api_title') }}</div>
        <div class="card-body small">
          <pre class="bg-light p-2 mb-0" style="white-space:pre-wrap">POST /recommend/view                 {product_id, visitor_id?}
GET  /recommend/recently-viewed      ?visitor_id=&exclude_id=
GET  /recommend/for-you              ?visitor_id=
GET  /recommend/hot
GET  /recommend/viewed-also-viewed/{productId}
GET  /recommend/bought-together/{productId}</pre>
          <p class="text-muted mt-2 mb-0">未登录访客请在前端生成并持久化 <code>visitor_id</code>（如 localStorage UUID）随请求传入；已登录会员自动按会员维度归集。</p>
        </div>
      </div>
    </div>
  </div>
@endsection
