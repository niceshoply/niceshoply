@extends('console::layouts.app')

@section('title', __('PrintCenter::common.title'))

@section('content')
  <div class="card"><div class="card-body">
    <p class="text-muted small">{{ __('PrintCenter::common.tip') }}</p>
    <form class="row g-2 align-items-end" method="GET" target="_blank">
      <div class="col-md-6"><input name="ids" class="form-control" placeholder="{{ __('PrintCenter::common.order_ids') }}：1, 2, 3" required></div>
      <div class="col-md-3">
        <select class="form-select" onchange="this.form.action='{{ console_route('print_center.print', ['type' => '__TYPE__']) }}'.replace('__TYPE__', this.value)">
          <option value="picking">{{ __('PrintCenter::common.picking') }}</option>
          <option value="packing" selected>{{ __('PrintCenter::common.packing') }}</option>
        </select>
      </div>
      <div class="col-md-3"><button class="btn btn-primary w-100">{{ __('PrintCenter::common.print') }}</button></div>
    </form>
    <script>document.querySelector('form').action='{{ console_route('print_center.print', ['type' => 'packing']) }}';</script>
  </div></div>
@endsection
