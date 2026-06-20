@extends('console::layouts.app')

@section('title', $activity->exists ? __('GroupBuy::common.edit') : __('GroupBuy::common.create'))

@section('content')
  <div class="card">
    <div class="card-body">
      <form id="gb-form">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('GroupBuy::common.name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ $activity->name }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('GroupBuy::common.sku_id') }} *</label>
            <input type="number" name="sku_id" class="form-control" value="{{ $activity->sku_id }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('GroupBuy::common.product_id') }}</label>
            <input type="number" name="product_id" class="form-control" value="{{ $activity->product_id }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('GroupBuy::common.group_price') }} *</label>
            <input type="number" step="0.01" name="group_price" class="form-control" value="{{ $activity->group_price }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('GroupBuy::common.group_size') }} *</label>
            <input type="number" name="group_size" class="form-control" value="{{ $activity->group_size }}" required min="2">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('GroupBuy::common.time_limit') }} *</label>
            <input type="number" name="time_limit_minutes" class="form-control" value="{{ $activity->time_limit_minutes }}" required min="1">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input type="checkbox" name="active" value="1" class="form-check-input" id="active" @checked($activity->active)>
              <label class="form-check-label" for="active">{{ __('GroupBuy::common.active') }}</label>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('GroupBuy::common.start_at') }}</label>
            <input type="datetime-local" name="start_at" class="form-control" value="{{ $activity->start_at?->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('GroupBuy::common.end_at') }}</label>
            <input type="datetime-local" name="end_at" class="form-control" value="{{ $activity->end_at?->format('Y-m-d\TH:i') }}">
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">{{ __('GroupBuy::common.saved') }}</button>
          <a href="{{ console_route('group_buy_activities.index') }}" class="btn btn-light">{{ __('GroupBuy::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('gb-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      if (!form.get('active')) form.set('active', '0');
      const isEdit = {{ $activity->exists ? 'true' : 'false' }};
      const url = isEdit ? '{{ $activity->exists ? console_route('group_buy_activities.update', $activity->id) : '' }}' : '{{ console_route('group_buy_activities.store') }}';
      if (isEdit) form.append('_method', 'PUT');
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(res => {
        if (res.code === 0 || res.success) location.href = '{{ console_route('group_buy_activities.index') }}';
        else alert(res.message || 'Error');
      });
    });
  </script>
@endsection
