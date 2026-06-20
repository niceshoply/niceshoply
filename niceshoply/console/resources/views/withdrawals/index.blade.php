@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/withdrawal.customer_withdrawals'))

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('withdrawals.index')"/>

      @if ($withdrawals->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('console/common.id') }}</td>
              <td>{{ __('console/withdrawal.customer_name') }}</td>
              <td>{{ __('console/withdrawal.customer_email') }}</td>
              <td>{{ __('console/withdrawal.amount') }}</td>
              <td>{{ __('console/withdrawal.account_type') }}</td>
              <td>{{ __('console/withdrawal.account_number') }}</td>
              <td>{{ __('console/withdrawal.status') }}</td>
              <td>{{ __('console/withdrawal.created_at') }}</td>
              @hookinsert('console.withdrawals.index.thead.bottom')
              <td>{{ __('console/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($withdrawals as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td>
                  <a href="{{ console_route('customers.edit', [$item->customer->id]) }}" class="text-decoration-none">
                    {{ $item->customer->name ?? '' }}
                  </a>
                </td>
                <td>{{ $item->customer->email ?? '' }}</td>
                <td>
                  <span class="fw-bold text-primary">{{ currency_format($item->amount) }}</span>
                </td>
                <td>{{ $item->account_type_format }}</td>
                <td>
                  <span class="text-muted font-monospace">{{ substr($item->account_number, 0, 6) }}****{{ substr($item->account_number, -4) }}</span>
                </td>
                <td>
                  @switch($item->status)
                    @case('pending')
                      <span class="badge bg-warning">{{ $item->status_format }}</span>
                      @break
                    @case('approved')
                      <span class="badge bg-info">{{ $item->status_format }}</span>
                      @break
                    @case('paid')
                      <span class="badge bg-success">{{ $item->status_format }}</span>
                      @break
                    @case('rejected')
                      <span class="badge bg-danger">{{ $item->status_format }}</span>
                      @break
                    @default
                      <span class="badge bg-secondary">{{ $item->status_format }}</span>
                  @endswitch
                </td>
                <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                @hookinsert('console.withdrawals.index.tbody.bottom', $item)
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ console_route('withdrawals.show', [$item->id]) }}" class="btn btn-primary btn-sm">
                      {{ __('console/withdrawal.view_detail') }}
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $withdrawals->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data text="{{ __('console/withdrawal.no_withdrawals') }}"/>
      @endif
    </div>
  </div>
@endsection

@push('footer')

@endpush 