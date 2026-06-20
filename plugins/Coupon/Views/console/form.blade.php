@extends('console::layouts.app')

@section('title', $coupon->exists ? __('Coupon::common.edit') : __('Coupon::common.create'))

@section('content')
  <div class="card">
    <div class="card-body">
      <form id="coupon-form">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.code') }} *</label>
            <input type="text" name="code" class="form-control" value="{{ $coupon->code }}" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">{{ __('Coupon::common.name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ $coupon->name }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.type') }} *</label>
            <select name="type" class="form-select">
              <option value="fixed" @selected($coupon->type==='fixed')>{{ __('Coupon::common.type_fixed') }}</option>
              <option value="percent" @selected($coupon->type==='percent')>{{ __('Coupon::common.type_percent') }}</option>
              <option value="free_shipping" @selected($coupon->type==='free_shipping')>{{ __('Coupon::common.type_free_shipping') }}</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.value') }} *</label>
            <input type="number" step="0.01" name="value" class="form-control" value="{{ $coupon->value }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.max_discount') }}</label>
            <input type="number" step="0.01" name="max_discount" class="form-control" value="{{ $coupon->max_discount }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.min_amount') }}</label>
            <input type="number" step="0.01" name="min_amount" class="form-control" value="{{ $coupon->min_amount }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.usage_limit') }}</label>
            <input type="number" name="usage_limit" class="form-control" value="{{ $coupon->usage_limit }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.per_customer_limit') }}</label>
            <input type="number" name="per_customer_limit" class="form-control" value="{{ $coupon->per_customer_limit }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.start_at') }}</label>
            <input type="datetime-local" name="start_at" class="form-control"
                   value="{{ $coupon->start_at ? $coupon->start_at->format('Y-m-d\TH:i') : '' }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Coupon::common.end_at') }}</label>
            <input type="datetime-local" name="end_at" class="form-control"
                   value="{{ $coupon->end_at ? $coupon->end_at->format('Y-m-d\TH:i') : '' }}">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
              <input type="checkbox" name="active" value="1" class="form-check-input" id="active" @checked($coupon->active)>
              <label class="form-check-label" for="active">{{ __('Coupon::common.active') }}</label>
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">{{ __('Coupon::common.saved') }}</button>
          <a href="{{ console_route('coupons.index') }}" class="btn btn-light">{{ __('Coupon::common.actions') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('coupon-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      if (!form.get('active')) form.set('active', '0');
      const isEdit = {{ $coupon->exists ? 'true' : 'false' }};
      const url = isEdit ? '{{ $coupon->exists ? console_route('coupons.update', $coupon->id) : '' }}'
                         : '{{ console_route('coupons.store') }}';
      if (isEdit) form.append('_method', 'PUT');
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(res => {
        if (res.code === 0 || res.success) {
          location.href = '{{ console_route('coupons.index') }}';
        } else {
          alert(res.message || 'Error');
        }
      });
    });
  </script>
@endsection
