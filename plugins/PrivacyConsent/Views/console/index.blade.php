@extends('console::layouts.app')

@section('title', __('PrivacyConsent::common.title'))

@section('content')
  <div class="mb-3 d-flex gap-2">
    <span class="badge bg-success fs-6">{{ __('PrivacyConsent::common.accepted') }}: {{ $accepted }}</span>
    <span class="badge bg-secondary fs-6">{{ __('PrivacyConsent::common.rejected') }}: {{ $rejected }}</span>
  </div>

  <div class="card"><div class="card-body table-responsive">
    <table class="table table-bordered align-middle mb-0">
      <thead><tr>
        <th>#</th><th>{{ __('PrivacyConsent::common.choice') }}</th><th>{{ __('PrivacyConsent::common.ip') }}</th>
        <th>{{ __('PrivacyConsent::common.ua') }}</th><th>{{ __('PrivacyConsent::common.time') }}</th>
      </tr></thead>
      <tbody>
      @forelse($consents as $c)
        <tr>
          <td>{{ $c->id }}</td>
          <td>
            @if($c->choice === 'accept')<span class="badge bg-success">{{ __('PrivacyConsent::common.choice_accept') }}</span>
            @else<span class="badge bg-secondary">{{ __('PrivacyConsent::common.choice_reject') }}</span>@endif
          </td>
          <td>{{ $c->ip }}</td>
          <td class="small text-muted text-truncate" style="max-width:320px">{{ $c->user_agent }}</td>
          <td>{{ $c->created_at }}</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('PrivacyConsent::common.no_data') }}</td></tr>
      @endforelse
      </tbody>
    </table>
  </div><div class="card-footer">{{ $consents->links() }}</div></div>
@endsection
