@extends('console::layouts.app')

@section('title', $level->exists ? __('Membership::common.edit') : __('Membership::common.create'))

@section('content')
  <div class="card">
    <div class="card-body">
      <form id="level-form">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('Membership::common.name') }} *</label>
            <input type="text" name="name" class="form-control" value="{{ $level->name }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('Membership::common.min_spent') }} *</label>
            <input type="number" step="0.01" name="min_spent" class="form-control" value="{{ $level->min_spent }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Membership::common.discount_percent') }} *</label>
            <input type="number" step="0.01" name="discount_percent" class="form-control" value="{{ $level->discount_percent }}" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">{{ __('Membership::common.sort') }}</label>
            <input type="number" name="sort" class="form-control" value="{{ $level->sort }}">
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
              <input type="checkbox" name="active" value="1" class="form-check-input" id="active" @checked($level->active)>
              <label class="form-check-label" for="active">{{ __('Membership::common.active') }}</label>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <button type="submit" class="btn btn-primary">{{ __('Membership::common.saved') }}</button>
          <a href="{{ console_route('membership_levels.index') }}" class="btn btn-light">{{ __('Membership::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('level-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      if (!form.get('active')) form.set('active', '0');
      const isEdit = {{ $level->exists ? 'true' : 'false' }};
      const url = isEdit ? '{{ $level->exists ? console_route('membership_levels.update', $level->id) : '' }}' : '{{ console_route('membership_levels.store') }}';
      if (isEdit) form.append('_method', 'PUT');
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(res => {
        if (res.code === 0 || res.success) location.href = '{{ console_route('membership_levels.index') }}';
        else alert(res.message || 'Error');
      });
    });
  </script>
@endsection
