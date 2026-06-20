@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.transactions'))
@section('page-title-right')
  <a href="{{ console_route('transactions.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-square"></i> {{ __('console/common.create') }}</a>
@endsection

@section('content')
  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('transactions.index')"/>

      @if ($transactions->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <td>{{ __('console/common.id') }}</td>
              <td>{{ __('console/transaction.customer') }}</td>
              <td>{{ __('console/common.email') }}</td>
              <td>{{ __('console/transaction.type') }}</td>
              <td>{{ __('console/transaction.amount') }}</td>
              <td>{{ __('console/transaction.balance') }}</td>
              @hookinsert('console.transactions.index.thead.bottom')
              <td>{{ __('console/common.actions') }}</td>
            </tr>
            </thead>
            <tbody>
            @foreach($transactions as $item)
              <tr>
                <td>{{ $item->id }}</td>
                <td><a href="{{ console_route('customers.edit', [$item->customer->id]) }}" class="text-decoration-none">
                  {{ $item->customer->name ?? '' }}
                </a></td>
                <td>{{ $item->customer->email ?? '' }}</td>
                <td>{{ $item->type_format }}</td>
                <td>{{ $item->amount }}</td>
                <td>{{ $item->balance }}</td>
                @hookinsert('console.transactions.index.tbody.bottom', $item)
                <td>
                  <div class="d-flex gap-1">
                    <a href="{{ console_route('transactions.show', [$item->id]) }}" class="btn btn-primary btn-sm">
                      {{ __('console/common.view') }}
                    </a>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $transactions->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif
    </div>
  </div>
@endsection

@push('footer')

@endpush
