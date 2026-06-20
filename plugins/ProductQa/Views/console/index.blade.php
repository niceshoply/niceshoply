@extends('console::layouts.app')

@section('title', __('ProductQa::common.menu'))

@section('content')
  <div class="card">
    <div class="card-body">
      @forelse($questions as $q)
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between">
            <div>
              <span class="badge bg-light text-dark">#{{ $q->id }}</span>
              <span class="text-muted small">{{ __('ProductQa::common.product_id') }}: {{ $q->product_id }}</span>
              @if($q->is_featured)<span class="badge bg-warning text-dark">{{ __('ProductQa::common.featured') }}</span>@endif
              @switch($q->status)
                @case('approved')<span class="badge bg-success">{{ __('ProductQa::common.st_approved') }}</span>@break
                @case('rejected')<span class="badge bg-danger">{{ __('ProductQa::common.st_rejected') }}</span>@break
                @default<span class="badge bg-warning text-dark">{{ __('ProductQa::common.st_pending') }}</span>
              @endswitch
            </div>
            <div class="text-nowrap">
              <button class="btn btn-sm btn-outline-success qa-q-audit" data-id="{{ $q->id }}" data-status="approved">{{ __('ProductQa::common.approve') }}</button>
              <button class="btn btn-sm btn-outline-danger qa-q-audit" data-id="{{ $q->id }}" data-status="rejected">{{ __('ProductQa::common.reject') }}</button>
              <button class="btn btn-sm btn-outline-warning qa-q-featured" data-id="{{ $q->id }}">★</button>
            </div>
          </div>
          <div class="fw-bold mt-2">Q: {{ $q->content }}</div>

          @foreach($q->answers as $a)
            <div class="ms-3 mt-2 ps-2 border-start">
              <span>{{ $a->is_merchant ? '['.__('ProductQa::common.merchant').'] ' : '' }}A: {{ $a->content }}</span>
              <span class="ms-2">
                @switch($a->status)
                  @case('approved')<span class="badge bg-success">{{ __('ProductQa::common.st_approved') }}</span>@break
                  @case('rejected')<span class="badge bg-danger">{{ __('ProductQa::common.st_rejected') }}</span>@break
                  @default<span class="badge bg-warning text-dark">{{ __('ProductQa::common.st_pending') }}</span>
                @endswitch
                <button class="btn btn-sm btn-link text-success qa-a-audit" data-id="{{ $a->id }}" data-status="approved">{{ __('ProductQa::common.approve') }}</button>
                <button class="btn btn-sm btn-link text-danger qa-a-audit" data-id="{{ $a->id }}" data-status="rejected">{{ __('ProductQa::common.reject') }}</button>
              </span>
            </div>
          @endforeach

          <div class="input-group input-group-sm mt-2" style="max-width:520px">
            <input class="form-control qa-reply-input" data-id="{{ $q->id }}" placeholder="{{ __('ProductQa::common.reply_placeholder') }}">
            <button class="btn btn-outline-primary qa-reply-btn" data-id="{{ $q->id }}">{{ __('ProductQa::common.merchant_reply') }}</button>
          </div>
        </div>
      @empty
        <div class="text-center text-muted py-4">{{ __('ProductQa::common.no_data') }}</div>
      @endforelse
      <div class="mt-3">{{ $questions->links() }}</div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    function post(url, body) {
      return fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: body
      }).then(r => r.json());
    }
    const base = '{{ console_route('product_qa.index') }}';
    document.querySelectorAll('.qa-q-audit').forEach(b => b.addEventListener('click', function () {
      const f = new FormData(); f.set('status', this.dataset.status);
      post(base + '/questions/' + this.dataset.id + '/audit', f).then(() => location.reload());
    }));
    document.querySelectorAll('.qa-q-featured').forEach(b => b.addEventListener('click', function () {
      post(base + '/questions/' + this.dataset.id + '/featured', new FormData()).then(() => location.reload());
    }));
    document.querySelectorAll('.qa-a-audit').forEach(b => b.addEventListener('click', function () {
      const f = new FormData(); f.set('status', this.dataset.status);
      post(base + '/answers/' + this.dataset.id + '/audit', f).then(() => location.reload());
    }));
    document.querySelectorAll('.qa-reply-btn').forEach(b => b.addEventListener('click', function () {
      const input = document.querySelector('.qa-reply-input[data-id="' + this.dataset.id + '"]');
      const f = new FormData(); f.set('content', input.value);
      post(base + '/questions/' + this.dataset.id + '/reply', f).then(() => location.reload());
    }));
  </script>
@endsection
