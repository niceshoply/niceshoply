@extends('console::layouts.app')

@section('title', $rule->exists ? __('CartRule::common.edit') : __('CartRule::common.create'))

@section('content')
  <div class="card">
    <div class="card-body">
      <form id="rule-form">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('CartRule::common.name') }}</label>
            <input type="text" name="name" class="form-control" value="{{ $rule->name }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('CartRule::common.min_amount') }} *</label>
            <input type="number" step="0.01" name="min_amount" class="form-control" value="{{ $rule->min_amount }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('CartRule::common.discount_type') }} *</label>
            <select name="discount_type" class="form-select">
              <option value="fixed" @selected($rule->discount_type==='fixed')>{{ __('CartRule::common.type_fixed') }}</option>
              <option value="percent" @selected($rule->discount_type==='percent')>{{ __('CartRule::common.type_percent') }}</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('CartRule::common.discount_value') }} *</label>
            <input type="number" step="0.01" name="discount_value" class="form-control" value="{{ $rule->discount_value }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('CartRule::common.max_discount') }}</label>
            <input type="number" step="0.01" name="max_discount" class="form-control" value="{{ $rule->max_discount }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('CartRule::common.start_at') }}</label>
            <input type="datetime-local" name="start_at" class="form-control" value="{{ $rule->start_at ? $rule->start_at->format('Y-m-d\TH:i') : '' }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('CartRule::common.end_at') }}</label>
            <input type="datetime-local" name="end_at" class="form-control" value="{{ $rule->end_at ? $rule->end_at->format('Y-m-d\TH:i') : '' }}">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
              <input type="checkbox" name="active" value="1" class="form-check-input" id="active" @checked($rule->active)>
              <label class="form-check-label" for="active">{{ __('CartRule::common.active') }}</label>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">{{ __('CartRule::common.saved') }}</button>
          <a href="{{ console_route('cart_rules.index') }}" class="btn btn-light">{{ __('CartRule::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('rule-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      if (!form.get('active')) form.set('active', '0');
      const isEdit = {{ $rule->exists ? 'true' : 'false' }};
      const url = isEdit ? '{{ $rule->exists ? console_route('cart_rules.update', $rule->id) : '' }}' : '{{ console_route('cart_rules.store') }}';
      if (isEdit) form.append('_method', 'PUT');
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(res => {
        if (res.code === 0 || res.success) location.href = '{{ console_route('cart_rules.index') }}';
        else alert(res.message || 'Error');
      });
    });
  </script>
@endsection
