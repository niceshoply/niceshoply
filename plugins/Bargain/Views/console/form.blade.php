@extends('console::layouts.app')

@section('title', $activity->exists ? __('Bargain::common.edit') : __('Bargain::common.create'))

@section('content')
  <div class="card">
    <div class="card-body">
      <form id="bargain-form">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Bargain::common.name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ $activity->name }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.sku_id') }} *</label>
            <input type="number" name="sku_id" class="form-control" value="{{ $activity->sku_id }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.product_id') }}</label>
            <input type="number" name="product_id" class="form-control" value="{{ $activity->product_id }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.origin_price') }}</label>
            <input type="number" step="0.01" name="origin_price" class="form-control" value="{{ $activity->origin_price }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.floor_price') }} *</label>
            <input type="number" step="0.01" name="floor_price" class="form-control" value="{{ $activity->floor_price }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.min_cut') }} *</label>
            <input type="number" step="0.01" name="min_cut" class="form-control" value="{{ $activity->min_cut }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.max_cut') }} *</label>
            <input type="number" step="0.01" name="max_cut" class="form-control" value="{{ $activity->max_cut }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.time_limit') }} *</label>
            <input type="number" name="time_limit_minutes" class="form-control" value="{{ $activity->time_limit_minutes }}" required min="1">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input type="checkbox" name="active" value="1" class="form-check-input" id="active" @checked($activity->active)>
              <label class="form-check-label" for="active">{{ __('Bargain::common.active') }}</label>
            </div>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.start_at') }}</label>
            <input type="datetime-local" name="start_at" class="form-control" value="{{ $activity->start_at?->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('Bargain::common.end_at') }}</label>
            <input type="datetime-local" name="end_at" class="form-control" value="{{ $activity->end_at?->format('Y-m-d\TH:i') }}">
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">{{ __('Bargain::common.saved') }}</button>
          <a href="{{ console_route('bargain_activities.index') }}" class="btn btn-light">{{ __('Bargain::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('bargain-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      if (!form.get('active')) form.set('active', '0');
      const isEdit = {{ $activity->exists ? 'true' : 'false' }};
      const url = isEdit ? '{{ $activity->exists ? console_route('bargain_activities.update', $activity->id) : '' }}' : '{{ console_route('bargain_activities.store') }}';
      if (isEdit) form.append('_method', 'PUT');
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(res => {
        if (res.code === 0 || res.success) location.href = '{{ console_route('bargain_activities.index') }}';
        else alert(res.message || 'Error');
      });
    });
  </script>
@endsection
