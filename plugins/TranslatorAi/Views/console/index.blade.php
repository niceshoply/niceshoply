@extends('console::layouts.app')

@section('title', __('TranslatorAi::common.workbench'))

@section('content')
  <div class="alert alert-info py-2">{{ __('TranslatorAi::common.tip') }}</div>

  <div class="card">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">{{ __('TranslatorAi::common.source_lang') }}</label>
          <input id="tr-source" class="form-control" value="auto" placeholder="{{ __('TranslatorAi::common.auto_detect') }}">
        </div>
        <div class="col-md-3">
          <label class="form-label">{{ __('TranslatorAi::common.target_lang') }}</label>
          <input id="tr-target" class="form-control" value="English">
        </div>
        <div class="col-md-6">
          <label class="form-label d-block">&nbsp;</label>
          <div class="btn-group" role="group">
            <input type="radio" class="btn-check" name="tr-mode" id="mode-text" value="text" checked>
            <label class="btn btn-outline-primary" for="mode-text">{{ __('TranslatorAi::common.mode_text') }}</label>
            <input type="radio" class="btn-check" name="tr-mode" id="mode-lines" value="lines">
            <label class="btn btn-outline-primary" for="mode-lines">{{ __('TranslatorAi::common.mode_lines') }}</label>
          </div>
        </div>
      </div>

      <div class="row g-3 mt-1">
        <div class="col-lg-6">
          <label id="tr-input-label" class="form-label">{{ __('TranslatorAi::common.text_input') }}</label>
          <textarea id="tr-input" class="form-control" rows="14"></textarea>
          <button id="tr-run" class="btn btn-primary mt-3">{{ __('TranslatorAi::common.translate') }}</button>
        </div>
        <div class="col-lg-6">
          <label class="form-label d-flex justify-content-between">
            <span>{{ __('TranslatorAi::common.result') }}</span>
            <button id="tr-copy" type="button" class="btn btn-sm btn-outline-secondary">{{ __('TranslatorAi::common.copy') }}</button>
          </label>
          <textarea id="tr-output" class="form-control" rows="14" readonly></textarea>
        </div>
      </div>

      <div id="tr-php-wrap" class="mt-3 d-none">
        <label class="form-label">{{ __('TranslatorAi::common.php_export') }}</label>
        <textarea id="tr-php" class="form-control" rows="10" readonly></textarea>
      </div>

      <div id="tr-warn-wrap" class="mt-3 d-none">
        <div class="alert alert-warning mb-0">
          <strong>{{ __('TranslatorAi::common.warnings') }}</strong>
          <div id="tr-warn"></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function () {
      const csrf = '{{ csrf_token() }}';
      const runBtn = document.getElementById('tr-run');
      const input = document.getElementById('tr-input');
      const output = document.getElementById('tr-output');
      const inputLabel = document.getElementById('tr-input-label');
      const phpWrap = document.getElementById('tr-php-wrap');
      const phpBox = document.getElementById('tr-php');
      const warnWrap = document.getElementById('tr-warn-wrap');
      const warnBox = document.getElementById('tr-warn');

      function mode() {
        return document.querySelector('input[name="tr-mode"]:checked').value;
      }
      document.querySelectorAll('input[name="tr-mode"]').forEach(function (r) {
        r.addEventListener('change', function () {
          inputLabel.innerText = mode() === 'lines'
            ? '{{ __('TranslatorAi::common.lines_input') }}'
            : '{{ __('TranslatorAi::common.text_input') }}';
        });
      });

      runBtn.addEventListener('click', function () {
        const original = runBtn.innerText;
        runBtn.disabled = true;
        runBtn.innerText = '{{ __('TranslatorAi::common.translating') }}';
        phpWrap.classList.add('d-none');
        warnWrap.classList.add('d-none');

        const isLines = mode() === 'lines';
        const url = isLines ? '{{ console_route('translator.lines') }}' : '{{ console_route('translator.text') }}';
        const form = new FormData();
        form.set('target', document.getElementById('tr-target').value);
        form.set('source', document.getElementById('tr-source').value);
        form.set(isLines ? 'lines' : 'text', input.value);

        fetch(url, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(function (res) {
          const d = res && res.data ? res.data : {};
          if (isLines) {
            output.value = d.result ? JSON.stringify(d.result, null, 2) : (res.message || 'error');
            if (d.php) { phpBox.value = d.php; phpWrap.classList.remove('d-none'); }
            warnBox.innerText = (d.warnings && d.warnings.length) ? d.warnings.join(', ') : '{{ __('TranslatorAi::common.no_warnings') }}';
            warnWrap.classList.remove('d-none');
          } else {
            output.value = d.output !== undefined ? d.output : (res.message || 'error');
          }
        }).catch(function (e) {
          output.value = e.message;
        }).finally(function () {
          runBtn.disabled = false;
          runBtn.innerText = original;
        });
      });

      document.getElementById('tr-copy').addEventListener('click', function () {
        output.select();
        document.execCommand('copy');
      });
    })();
  </script>
@endsection
