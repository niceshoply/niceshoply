@extends('console::layouts.app')

@section('title', __('PopupNotice::common.menu'))

@section('content')
  @php($isEdit = $notice->exists)
  <div class="card">
    <div class="card-body">
      <form id="notice-form">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">{{ __('PopupNotice::common.title') }}</label>
            <input name="title" class="form-control" value="{{ $notice->title }}" required>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('PopupNotice::common.type') }}</label>
            <select name="type" class="form-select">
              <option value="popup" @selected($notice->type === 'popup')>{{ __('PopupNotice::common.type_popup') }}</option>
              <option value="bar" @selected($notice->type === 'bar')>{{ __('PopupNotice::common.type_bar') }}</option>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">{{ __('PopupNotice::common.scope') }}</label>
            <select name="scope" class="form-select">
              <option value="all" @selected($notice->scope === 'all')>{{ __('PopupNotice::common.scope_all') }}</option>
              <option value="home" @selected($notice->scope === 'home')>{{ __('PopupNotice::common.scope_home') }}</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">{{ __('PopupNotice::common.content') }}</label>
            <textarea name="content" class="form-control" rows="3">{{ $notice->content }}</textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('PopupNotice::common.image') }}</label>
            <input name="image" class="form-control" value="{{ $notice->image }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">{{ __('PopupNotice::common.link_url') }}</label>
            <input name="link_url" class="form-control" value="{{ $notice->link_url }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PopupNotice::common.start_at') }}</label>
            <input name="start_at" type="datetime-local" class="form-control" value="{{ optional($notice->start_at)->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PopupNotice::common.end_at') }}</label>
            <input name="end_at" type="datetime-local" class="form-control" value="{{ optional($notice->end_at)->format('Y-m-d\TH:i') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PopupNotice::common.sort') }}</label>
            <input name="sort" type="number" class="form-control" value="{{ $notice->sort ?? 0 }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">{{ __('PopupNotice::common.is_active') }}</label>
            <select name="is_active" class="form-select">
              <option value="1" @selected($notice->is_active)>{{ __('PopupNotice::common.yes') }}</option>
              <option value="0" @selected(! $notice->is_active)>{{ __('PopupNotice::common.no') }}</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">{{ __('PopupNotice::common.submit') }}</button>
          <a href="{{ console_route('popup_notice.index') }}" class="btn btn-light">{{ __('PopupNotice::common.back') }}</a>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.getElementById('notice-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const form = new FormData(this);
      @if($isEdit)
        form.append('_method', 'PUT');
        const url = '{{ console_route('popup_notice.update', $notice->id) }}';
      @else
        const url = '{{ console_route('popup_notice.store') }}';
      @endif
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(function (res) {
        if (res && res.success) { location.href = '{{ console_route('popup_notice.index') }}'; }
        else { alert(res.message || 'error'); }
      });
    });
  </script>
@endsection
