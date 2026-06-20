<!-- Storage Settings -->
<div class="tab-pane fade" id="tab-setting-storage">
<div class="card mb-4">
  <div class="card-header">
    <h5 class="card-title mb-0">{{ __('console/setting.storage_settings') }}</h5>
    <p class="text-muted small mb-0">{{ __('console/setting.storage_settings_desc') }}</p>
  </div>
  <div class="card-body">
    @php $currentDriver = old('fm_driver', plugin_setting('file_manager', 'driver', 'local')); @endphp

    <x-console::form.row :title="__('console/setting.storage_type')">
      <select class="form-select w-max-500" name="fm_driver" id="fm-driver-select"
              onchange="switchStorageDriver(this.value)">
        <option value="local" {{ $currentDriver === 'local' ? 'selected' : '' }}>{{ __('console/setting.local_storage') }}</option>
        <option value="s3" {{ $currentDriver === 's3' ? 'selected' : '' }}>{{ __('console/setting.amazon_s3') }}</option>
        <option value="oss" {{ $currentDriver === 'oss' ? 'selected' : '' }}>{{ __('console/setting.alibaba_oss') }}</option>
      </select>
    </x-console::form.row>

    <div id="cloud-fields" style="display: {{ in_array($currentDriver, ['oss', 's3']) ? 'block' : 'none' }}">
      <div class="form-row mb-3">
        <div class="row"><div class="col-6"><div class="col-form-label" id="label-key"></div></div></div>
        <div class="flex-fill">
          <input type="text" class="form-control" name="fm_key" id="fm-key"
                 value="{{ old('fm_key', plugin_setting('file_manager', 'key', '')) }}">
        </div>
      </div>

      <div class="form-row mb-3">
        <div class="row"><div class="col-6"><div class="col-form-label" id="label-secret"></div></div></div>
        <div class="flex-fill">
          <input type="password" class="form-control" name="fm_secret" id="fm-secret"
                 value="{{ old('fm_secret', plugin_setting('file_manager', 'secret', '')) }}"
                 autocomplete="new-password">
        </div>
      </div>

      <div class="form-row mb-3">
        <div class="row"><div class="col-6"><div class="col-form-label" id="label-endpoint"></div></div></div>
        <div class="flex-fill">
          <input type="text" class="form-control" name="fm_endpoint" id="fm-endpoint"
                 value="{{ old('fm_endpoint', plugin_setting('file_manager', 'endpoint', '')) }}">
        </div>
      </div>

      <div class="form-row mb-3">
        <div class="row"><div class="col-6"><div class="col-form-label" id="label-bucket"></div></div></div>
        <div class="flex-fill">
          <input type="text" class="form-control" name="fm_bucket" id="fm-bucket"
                 value="{{ old('fm_bucket', plugin_setting('file_manager', 'bucket', '')) }}">
        </div>
      </div>

      <div class="form-row mb-3">
        <div class="row"><div class="col-6"><div class="col-form-label" id="label-region"></div></div></div>
        <div class="flex-fill">
          <input type="text" class="form-control" name="fm_region" id="fm-region"
                 value="{{ old('fm_region', plugin_setting('file_manager', 'region', '')) }}">
        </div>
      </div>

      <div class="form-row mb-3">
        <div class="row"><div class="col-6"><div class="col-form-label" id="label-cdn"></div></div></div>
        <div class="flex-fill">
          <input type="text" class="form-control" name="fm_cdn_domain" id="fm-cdn-domain"
                 value="{{ old('fm_cdn_domain', plugin_setting('file_manager', 'cdn_domain', '')) }}">
        </div>
        <div class="text-muted small mt-1" id="cdn-desc"></div>
      </div>
    </div>
  </div>
</div>
</div>

<script>
var storageDriverConfig = {
  s3: {
    key:      { label: 'Access Key ID',     placeholder: 'e.g. accesskeyid' },
    secret:   { label: 'Secret Access Key',  placeholder: 'e.g. secretaccesskey' },
    endpoint: { label: 'Endpoint URL',       placeholder: 'e.g. https://s3.us-east-1.amazonaws.com' },
    bucket:   { label: 'Bucket',             placeholder: 'e.g. my-bucket' },
    region:   { label: 'Region',             placeholder: 'e.g. us-east-1' },
    cdn:      { label: 'CDN Domain',         placeholder: 'e.g. https://cdn.example.com', desc: '{{ __('console/setting.s3_cdn_desc') }}' }
  },
  oss: {
    key:      { label: 'AccessKey ID',       placeholder: 'e.g. accesskeyid' },
    secret:   { label: 'AccessKey Secret',   placeholder: 'e.g. accesskeysecret' },
    endpoint: { label: 'Endpoint',           placeholder: 'e.g. https://oss-cn-hangzhou.aliyuncs.com' },
    bucket:   { label: 'Bucket',             placeholder: 'e.g. my-oss-bucket' },
    region:   { label: 'Region',             placeholder: 'e.g. oss-cn-hangzhou' },
    cdn:      { label: 'CDN 加速域名', placeholder: 'e.g. https://cdn.example.com', desc: '{{ __('console/setting.oss_cdn_desc') }}' }
  }
};

function switchStorageDriver(driver) {
  var cloudFields = document.getElementById('cloud-fields');
  if (driver === 'local') {
    cloudFields.style.display = 'none';
    return;
  }
  cloudFields.style.display = 'block';
  var cfg = storageDriverConfig[driver] || storageDriverConfig['s3'];
  var fields = ['key', 'secret', 'endpoint', 'bucket', 'region', 'cdn'];
  for (var i = 0; i < fields.length; i++) {
    var f = fields[i];
    var labelEl = document.getElementById('label-' + f);
    var inputId = f === 'cdn' ? 'fm-cdn-domain' : 'fm-' + f;
    var inputEl = document.getElementById(inputId);
    if (labelEl) labelEl.textContent = cfg[f].label;
    if (inputEl) inputEl.placeholder = cfg[f].placeholder;
  }
  var descEl = document.getElementById('cdn-desc');
  if (descEl) descEl.textContent = cfg.cdn.desc;
}

document.addEventListener('DOMContentLoaded', function() {
  var sel = document.getElementById('fm-driver-select');
  if (sel) switchStorageDriver(sel.value);
});
</script>
