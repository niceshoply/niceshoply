@extends('console::layouts.app')

@section('title', __('ImageGuard::common.menu'))

@section('content')
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">{{ __('ImageGuard::common.preview_title') }}</div>
        <div class="card-body">
          <p class="text-muted">{{ __('ImageGuard::common.preview_desc') }}</p>
          <form id="pv-form">
            <input type="file" name="file" accept="image/*" class="form-control mb-2" required>
            <button type="submit" class="btn btn-primary">{{ __('ImageGuard::common.preview_btn') }}</button>
          </form>
          <div class="mt-3 text-center">
            <img id="pv-img" class="img-fluid border d-none" alt="preview">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">{{ __('ImageGuard::common.process_title') }}</div>
        <div class="card-body">
          <p class="text-muted small">{{ __('ImageGuard::common.process_desc') }}</p>
          <form id="proc-form" class="row g-2 align-items-end">
            <div class="col-8">
              <label class="form-label">{{ __('ImageGuard::common.dir') }}</label>
              <input name="dir" class="form-control" placeholder="products" required>
            </div>
            <div class="col-4">
              <button type="submit" class="btn btn-success w-100">{{ __('ImageGuard::common.process_btn') }}</button>
            </div>
          </form>
          <div id="proc-result" class="mt-3 d-none">
            <h6>{{ __('ImageGuard::common.result') }}</h6>
            <pre class="bg-light p-2 small" id="proc-body" style="max-height:240px;overflow:auto"></pre>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('pv-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = this.querySelector('button'); btn.disabled = true;
      fetch('{{ console_route('image_guard.preview') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => {
        btn.disabled = false;
        if (res.success) {
          const img = document.getElementById('pv-img');
          img.src = res.data.preview; img.classList.remove('d-none');
        } else { alert(res.message); }
      }).catch(() => { btn.disabled = false; });
    });

    document.getElementById('proc-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = this.querySelector('button'); btn.disabled = true;
      fetch('{{ console_route('image_guard.process') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => {
        btn.disabled = false;
        const box = document.getElementById('proc-result'); box.classList.remove('d-none');
        document.getElementById('proc-body').textContent = (res.message || '') + '\n' + JSON.stringify(res.data || {}, null, 2);
      }).catch(() => { btn.disabled = false; });
    });
  </script>
@endsection
