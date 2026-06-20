@extends('console::layouts.app')

@section('title', __('AiMarketing::common.workbench'))

@section('content')
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">{{ __('AiMarketing::common.workbench') }}</div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label">{{ __('AiMarketing::common.scene') }}</label>
            <select id="ai-scene" class="form-select">
              @foreach(['product_title','product_desc','selling_point','seo_meta','sms','email','social'] as $s)
                <option value="{{ $s }}">{{ __('AiMarketing::common.scene_'.$s) }}</option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label">{{ __('AiMarketing::common.tone') }}</label>
              <select id="ai-tone" class="form-select">
                <option value="planting">{{ __('AiMarketing::common.tone_planting') }}</option>
                <option value="pro">{{ __('AiMarketing::common.tone_pro') }}</option>
                <option value="promo">{{ __('AiMarketing::common.tone_promo') }}</option>
              </select>
            </div>
            <div class="col-6 mb-3">
              <label class="form-label">{{ __('AiMarketing::common.lang') }}</label>
              <input id="ai-lang" class="form-control" value="中文">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('AiMarketing::common.keywords') }}</label>
            <input id="ai-keywords" class="form-control" placeholder="...">
          </div>
          <div class="mb-3">
            <label class="form-label">{{ __('AiMarketing::common.input') }}</label>
            <textarea id="ai-input" class="form-control" rows="5"></textarea>
          </div>
          <button id="ai-generate" class="btn btn-primary">{{ __('AiMarketing::common.generate') }}</button>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <span>{{ __('AiMarketing::common.result') }}</span>
          <button id="ai-copy" class="btn btn-sm btn-outline-secondary">{{ __('AiMarketing::common.copy') }}</button>
        </div>
        <div class="card-body">
          <textarea id="ai-output" class="form-control" rows="16" readonly></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header">{{ __('AiMarketing::common.recent_logs') }}</div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('AiMarketing::common.scene') }}</th>
            <th>{{ __('AiMarketing::common.input') }}</th>
            <th>{{ __('AiMarketing::common.result') }}</th>
          </tr>
          </thead>
          <tbody>
          @foreach($logs as $log)
            <tr>
              <td>{{ $log->id }}</td>
              <td>{{ __('AiMarketing::common.scene_'.$log->scene) }}</td>
              <td class="text-truncate" style="max-width:200px">{{ $log->input }}</td>
              <td class="text-truncate" style="max-width:320px">{{ $log->output }}</td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    (function () {
      const btn = document.getElementById('ai-generate');
      const out = document.getElementById('ai-output');
      btn.addEventListener('click', function () {
        const original = btn.innerText;
        btn.disabled = true;
        btn.innerText = '{{ __('AiMarketing::common.generating') }}';
        const form = new FormData();
        form.set('scene', document.getElementById('ai-scene').value);
        form.set('tone', document.getElementById('ai-tone').value);
        form.set('lang', document.getElementById('ai-lang').value);
        form.set('keywords', document.getElementById('ai-keywords').value);
        form.set('input', document.getElementById('ai-input').value);
        fetch('{{ console_route('ai_marketing.generate') }}', {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(function (res) {
          out.value = (res && res.data && res.data.output) ? res.data.output : (res.message || 'error');
        }).catch(function (e) {
          out.value = e.message;
        }).finally(function () {
          btn.disabled = false;
          btn.innerText = original;
        });
      });

      document.getElementById('ai-copy').addEventListener('click', function () {
        out.select();
        document.execCommand('copy');
      });
    })();
  </script>
@endsection
