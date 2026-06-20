@extends('console::layouts.app')

@section('title', __('ProductIo::common.menu'))

@section('content')
  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card mb-3">
        <div class="card-header">{{ __('ProductIo::common.export_title') }}</div>
        <div class="card-body">
          <p class="text-muted">{{ __('ProductIo::common.export_desc') }}</p>
          <a href="{{ console_route('product_io.export') }}" class="btn btn-primary">
            <i class="bi bi-download"></i> {{ __('ProductIo::common.export_btn') }}
          </a>
        </div>
      </div>

      <div class="card">
        <div class="card-header">{{ __('ProductIo::common.columns') }}</div>
        <div class="card-body small text-muted">
          <ul class="mb-2">
            <li>{{ __('ProductIo::common.col_spu') }}</li>
            <li>{{ __('ProductIo::common.col_sku') }}</li>
            <li>{{ __('ProductIo::common.col_name') }}</li>
            <li>{{ __('ProductIo::common.col_price') }}</li>
            <li>{{ __('ProductIo::common.col_qty') }}</li>
            <li>{{ __('ProductIo::common.col_active') }}</li>
          </ul>
          <div class="alert alert-warning mb-0">{{ __('ProductIo::common.note') }}</div>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      <div class="card">
        <div class="card-header">{{ __('ProductIo::common.import_title') }}</div>
        <div class="card-body">
          <p class="text-muted">{{ __('ProductIo::common.import_desc') }}</p>
          <form id="imp-form">
            <div class="mb-2">
              <input type="file" name="file" accept=".csv,text/csv" class="form-control" required>
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="apply_active" value="1" id="apply-active">
              <label class="form-check-label" for="apply-active">{{ __('ProductIo::common.apply_active') }}</label>
            </div>
            <button type="submit" class="btn btn-success">{{ __('ProductIo::common.import_btn') }}</button>
          </form>

          <div id="result" class="mt-3 d-none">
            <h6>{{ __('ProductIo::common.result') }}</h6>
            <pre class="bg-light p-2 small" id="result-body" style="max-height:240px;overflow:auto"></pre>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('imp-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = this.querySelector('button'); btn.disabled = true;
      fetch('{{ console_route('product_io.import') }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: new FormData(this)
      }).then(r => r.json()).then(function (res) {
        btn.disabled = false;
        const box = document.getElementById('result');
        box.classList.remove('d-none');
        document.getElementById('result-body').textContent =
          (res.message || '') + '\n' + JSON.stringify(res.data || {}, null, 2);
      }).catch(() => { btn.disabled = false; });
    });
  </script>
@endsection
