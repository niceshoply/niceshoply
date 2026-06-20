@extends('console::layouts.app')

@section('title', __('Referral::common.title'))

@section('content')
  <p class="text-muted small">{{ __('Referral::common.tip') }}</p>

  <div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card text-center"><div class="card-body">
      <div class="text-muted small">{{ __('Referral::common.total_bindings') }}</div>
      <div class="fs-3 fw-bold">{{ number_format($totalBindings) }}</div>
    </div></div></div>
    <div class="col-md-6"><div class="card text-center"><div class="card-body">
      <div class="text-muted small">{{ __('Referral::common.total_rewards') }}</div>
      <div class="fs-3 fw-bold">{{ number_format($totalRewards) }}</div>
    </div></div></div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card"><div class="card-header">{{ __('Referral::common.bindings') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-bordered align-middle mb-0">
            <thead><tr>
              <th>{{ __('Referral::common.inviter') }}</th><th>{{ __('Referral::common.invitee') }}</th>
              <th>{{ __('Referral::common.code') }}</th><th>{{ __('Referral::common.bound_at') }}</th>
            </tr></thead>
            <tbody>
            @forelse($bindings as $b)
              <tr><td>{{ $b->inviter_id }}</td><td>{{ $b->invitee_id }}</td><td><code>{{ $b->code }}</code></td><td class="small">{{ $b->bound_at }}</td></tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">{{ __('Referral::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div><div class="card-footer">{{ $bindings->links() }}</div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card mb-3"><div class="card-header">{{ __('Referral::common.rewards') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead><tr><th>{{ __('Referral::common.scene') }}</th><th>{{ __('Referral::common.reward') }}</th></tr></thead>
            <tbody>
            @forelse($rewards as $r)
              <tr><td class="small">{{ $r->scene }}</td><td class="small">{{ $r->reward_type }}: {{ $r->reward_value }}</td></tr>
            @empty
              <tr><td colspan="2" class="text-center text-muted py-3">{{ __('Referral::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </div>
      <div class="card"><div class="card-header">{{ __('Referral::common.api_title') }}</div>
        <div class="card-body small"><pre class="bg-light p-2 mb-0">GET /referral/info</pre></div>
      </div>
    </div>
  </div>
@endsection
