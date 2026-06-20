@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.reviews'))

@section('page-title-right')
  <a href="{{ console_route('reviews.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-square"></i> {{__('console/common.create') }}
  </a>
  @hookinsert('console.reviews.list.buttons')
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      @if(($pendingCount ?? 0) > 0)
      <div class="alert alert-warning">{{ __('console/review.pending_count', ['count' => $pendingCount]) }}</div>
      @endif

      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('reviews.index')"/>

      @if ($reviews->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('console/review.id') }}</td>
              <td>{{ __('console/review.customer') }}</td>
              <td>{{ __('console/review.product') }}</td>
              <td>{{ __('console/review.rating') }}</td>
              <td>{{ __('console/review.review_content') }}</td>
              <td>{{ __('console/review.status') }}</td>
              <td>{{ __('console/common.date') }}</td>
              <td>{{ __('console/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($reviews as $review)
              <tr>
                <td>{{ $review->id }}</td>
                <td>{{ $review->customer->name ?? '-' }}</td>
                @if($review->product)
                  <td data-title="product" data-bs-toggle="tooltip" data-bs-placement="bottom"
                      title="{{ $review->product->fallbackName() }}">
                    <a href="{{ $review->product->url ?? '' }}" target="_blank" class="text-decoration-none">
                      <img src="{{ image_resize($review->product->image ?? '') }}"
                           alt="{{ $review->product->name ?? '' }}"
                           class="img-fluid wh-30">
                      {{ sub_string($review->product->fallbackName(), 24) }}
                    </a>
                  </td>
                @else
                  <td>-</td>
                @endif
                <td>
                  <x-front-review :rating="$review['rating']"/>
                </td>
                <td class="btn-link-review_content" data-bs-toggle="tooltip" data-bs-placement="bottom"
                    title="{{ $review->content }}">
                  {{ sub_string($review->content)}}
                  @if($review->hasImages())
                    <span class="badge bg-info">{{ __('console/review.has_images') }}</span>
                  @endif
                  @if($review->reply)
                    <div class="small text-muted mt-1">{{ __('console/review.reply') }}: {{ sub_string($review->reply, 40) }}</div>
                  @endif
                </td>
                <td>
                  <span class="badge bg-{{ $review->status === 'approved' ? 'success' : ($review->status === 'pending' ? 'warning' : 'secondary') }}">
                    {{ __('console/review.status_'.$review->status) }}
                  </span>
                </td>
                <td>{{ $review->created_at->format('Y-m-d') }}</td>
                <td>
                  <div class="d-flex flex-wrap gap-1">
                    @if($review->status === 'pending')
                      <button type="button" class="btn btn-sm btn-success btn-approve" data-url="{{ console_route('reviews.approve', $review->id) }}">{{ __('console/review.approve') }}</button>
                      <button type="button" class="btn btn-sm btn-secondary btn-reject" data-url="{{ console_route('reviews.reject', $review->id) }}">{{ __('console/review.reject') }}</button>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-primary btn-reply" data-id="{{ $review->id }}" data-reply="{{ $review->reply }}">{{ __('console/review.reply') }}</button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-review" data-url="{{ console_route('reviews.destroy', $review->id) }}">{{ __('front/common.delete') }}</button>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $reviews->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>

  <div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ __('console/review.reply') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <textarea class="form-control" id="replyContent" rows="4"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" id="submitReply">{{ __('console/common.save') }}</button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('footer')
  <script>
    let replyUrl = '';

    $('.delete-review').on('click', function () {
      const url = $(this).data('url');
      layer.confirm('{{ __('front/common.delete_confirm') }}', {
        btn: ['{{ __('front/common.confirm') }}', '{{ __('front/common.cancel') }}']
      }, function () {
        axios.delete(url).then(function (res) {
          if (res.success) {
            layer.msg(res.message, {icon: 1, time: 1000}, function () {
              window.location.reload()
            });
          }
        })
      });
    });

    $('.btn-approve, .btn-reject').on('click', function () {
      axios.post($(this).data('url')).then(function (res) {
        if (res.success) {
          window.location.reload();
        }
      });
    });

    $('.btn-reply').on('click', function () {
      replyUrl = urls.console_base + '/reviews/' + $(this).data('id') + '/reply';
      $('#replyContent').val($(this).data('reply') || '');
      new bootstrap.Modal('#replyModal').show();
    });

    $('#submitReply').on('click', function () {
      axios.post(replyUrl, { reply: $('#replyContent').val() }).then(function (res) {
        if (res.success) {
          window.location.reload();
        }
      });
    });
  </script>
@endpush
