@extends('layouts.app')
@section('body-class', 'page-account-privacy')

@section('title', __('front/privacy.title'))

@section('content')
<div class="container py-4">
  <div class="row">
    <div class="col-md-3">
      @include('shared.account-sidebar')
    </div>
    <div class="col-md-9">
      <div class="card mb-4">
        <div class="card-header">{{ __('front/privacy.title') }}</div>
        <div class="card-body">
          @if(!empty($pendingConsents))
          <div class="alert alert-warning">
            <div class="fw-semibold mb-2">{{ __('front/privacy.consent_required') }}</div>
            @foreach($pendingConsents as $doc)
            <button type="button" class="btn btn-sm btn-primary me-2 consent-btn" data-type="{{ $doc->type }}">
              {{ __('front/privacy.agree') }} ({{ $doc->version }})
            </button>
            @endforeach
          </div>
          @endif

          <p class="text-muted">{{ __('front/privacy.intro') }}</p>

          <div class="d-flex gap-2 mb-4">
            <button type="button" class="btn btn-outline-primary" id="btn-export">{{ __('front/privacy.request_export') }}</button>
            <button type="button" class="btn btn-outline-danger" id="btn-delete">{{ __('front/privacy.request_delete') }}</button>
          </div>

          @if($gdprRequests->count())
          <h6>{{ __('front/privacy.request_history') }}</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <td>{{ __('front/privacy.type') }}</td>
                  <td>{{ __('front/privacy.status') }}</td>
                  <td>{{ __('front/common.created_at') }}</td>
                  <td></td>
                </tr>
              </thead>
              <tbody>
                @foreach($gdprRequests as $item)
                <tr>
                  <td>{{ __('front/privacy.type_'.$item->type) }}</td>
                  <td>{{ __('front/privacy.status_'.$item->status) }}</td>
                  <td>{{ $item->created_at }}</td>
                  <td>
                    @if($item->type === 'export' && $item->status === 'completed')
                    <a href="{{ account_route('privacy.download', [$item->id]) }}" class="btn btn-sm btn-link">{{ __('front/privacy.download') }}</a>
                    @endif
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('footer')
<script>
  $('.consent-btn').on('click', function () {
    axios.post('{{ account_route('privacy.consent') }}', { type: $(this).data('type') })
      .then(function (res) { layer.msg(res.message || 'OK'); location.reload(); })
      .catch(function (err) { layer.msg(err.response?.data?.message || 'Error'); });
  });
  $('#btn-export').on('click', function () {
    if (!confirm('{{ __('front/privacy.export_confirm') }}')) return;
    axios.post('{{ account_route('privacy.export') }}')
      .then(function (res) { layer.msg(res.message); location.reload(); })
      .catch(function (err) { layer.msg(err.response?.data?.message || 'Error'); });
  });
  $('#btn-delete').on('click', function () {
    if (!confirm('{{ __('front/privacy.delete_confirm') }}')) return;
    axios.post('{{ account_route('privacy.delete') }}')
      .then(function (res) { layer.msg(res.message); location.reload(); })
      .catch(function (err) { layer.msg(err.response?.data?.message || 'Error'); });
  });
</script>
@endpush
