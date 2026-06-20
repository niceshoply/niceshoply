@extends('console::layouts.app')

@section('title', $activity->name . ' · ' . __('GroupBuy::common.groups'))

@section('page-title-right')
  <a href="{{ console_route('group_buy_activities.index') }}" class="btn btn-light btn-sm">{{ __('GroupBuy::common.back') }}</a>
@endsection

@section('content')
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('GroupBuy::common.leader') }}</th>
            <th>{{ __('GroupBuy::common.members') }}</th>
            <th>{{ __('GroupBuy::common.status') }}</th>
            <th>{{ __('GroupBuy::common.expire_at') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($groups as $group)
            <tr>
              <td>{{ $group->id }}</td>
              <td>{{ $group->leader_customer_id }}</td>
              <td>{{ $group->members_count }} / {{ $activity->group_size }}</td>
              <td>
                @switch($group->status)
                  @case('success')<span class="badge bg-success">{{ __('GroupBuy::common.status_success') }}</span>@break
                  @case('failed')<span class="badge bg-danger">{{ __('GroupBuy::common.status_failed') }}</span>@break
                  @default<span class="badge bg-warning text-dark">{{ __('GroupBuy::common.status_open') }}</span>
                @endswitch
              </td>
              <td>{{ $group->expire_at?->format('Y-m-d H:i') ?? '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('GroupBuy::common.no_group') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      {{ $groups->links() }}
    </div>
  </div>
@endsection
