@extends('console::layouts.app')

@section('title', __('PointsMall::common.menu_items'))

@section('content')
  @php($isEdit = $item->exists)
  <div class="card">
    <div class="card-body">
      <form id="item-form">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">{{ __('PointsMall::common.title') }}</label>
            <input name="title" class="form-control" value="{{ $item->title }}" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('PointsMall::common.image') }}</label>
            <input name="image" class="form-control" value="{{ $item->image }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.type') }}</label>
            <select name="type" class="form-select">
              <option value="goods" @selected($item->type === 'goods')>{{ __('PointsMall::common.type_goods') }}</option>
              <option value="coupon" @selected($item->type === 'coupon')>{{ __('PointsMall::common.type_coupon') }}</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.ref_id') }}</label>
            <input name="ref_id" type="number" class="form-control" value="{{ $item->ref_id ?? 0 }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.points_cost') }}</label>
            <input name="points_cost" type="number" class="form-control" value="{{ $item->points_cost ?? 0 }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.cash_cost') }}</label>
            <input name="cash_cost" type="number" step="0.01" class="form-control" value="{{ $item->cash_cost ?? 0 }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.stock') }}</label>
            <input name="stock" type="number" class="form-control" value="{{ $item->stock ?? 0 }}" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.per_limit') }}</label>
            <input name="per_limit" type="number" class="form-control" value="{{ $item->per_limit ?? 0 }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.sort') }}</label>
            <input name="sort" type="number" class="form-control" value="{{ $item->sort ?? 0 }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PointsMall::common.is_active') }}</label>
            <select name="is_active" class="form-select">
              <option value="1" @selected($item->is_active)>{{ __('PointsMall::common.yes') }}</option>
              <option value="0" @selected(! $item->is_active)>{{ __('PointsMall::common.no') }}</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('PointsMall::common.description') }}</label>
            <textarea name="description" class="form-control" rows="3">{{ $item->description }}</textarea>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">{{ __('PointsMall::common.submit') }}</button>
          <a href="{{ console_route('points_mall.items') }}" class="btn btn-light">{{ __('PointsMall::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('item-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      @if($isEdit)
        form.append('_method', 'PUT');
        const url = '{{ console_route('points_mall.items.update', $item->id) }}';
      @else
        const url = '{{ console_route('points_mall.items.store') }}';
      @endif
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(function (res) {
        if (res && res.success) { location.href = '{{ console_route('points_mall.items') }}'; }
        else { alert(res.message || 'error'); }
      });
    });
  </script>
@endsection
