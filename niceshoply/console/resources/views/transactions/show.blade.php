@extends('console::layouts.app')
@section('body-class', 'page-transaction')
@section('title', __('console/menu.transactions'))

<x-console::form.right-btns/>

@section('content')
  <div class="card h-min-600">
    <div class="card-header">
      <h5 class="card-title mb-0">{{ __('console/menu.transactions') }}</h5>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('console/transaction.customer') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->customer->name ?? '' }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('console/transaction.amount') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->amount }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('console/transaction.balance') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->balance }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('console/transaction.type') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->type }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('console/transaction.comment') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->comment }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('console/common.created_at') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->created_at }}</div>
        </div>

        <div class="col-12 col-md-6 mb-3">
          <div class="col-sm-2 col-form-label text-start">
            <div class="fw-bold">{{ __('console/common.updated_at') }}</div>
          </div>
          <div class="form-control-plaintext">{{ $transaction->updated_at }}</div>
        </div>
      </div>
    </div>
  </div>
@endsection
