<!-- AI Content Settings -->
<div class="tab-pane fade" id="tab-setting-content-ai">
<div class="container">
  <div class="row">
    <div class="col-6">
      <x-common-form-select title="{{ __('console/setting.ai_model') }}" name="ai_model"
                            :options="$ai_models" key="code" label="name" :emptyOption="false"
                            value="{{ old('ai_model', system_setting('ai_model')) }}" required />
    </div>
    <div class="col-6 d-flex align-items-center">
      <div class="form-group mt-4">
        <div class="d-flex flex-nowrap">
          <a class="btn btn-primary me-3" href="{{ console_route('plugins.index', ['type'=>'intelli']) }}" target="_blank">
            {{ __('console/common.setting') }}
          </a>
          <a class="btn btn-primary" href="{{ console_route('plugin-market.index', ['type'=>'intelli']) }}" target="_blank">
            {{ __('console/common.get_more') }}
          </a>
        </div>
      </div>
    </div>
  </div>

  @foreach($ai_prompts as $prompt)
    <x-common-form-textarea title="{{ __('console/setting.'.$prompt) }}" name="{{ $prompt }}"
                            value="{{ old($prompt, system_setting($prompt)) }}"
                            placeholder="{{ __('console/setting.'.$prompt) }}"/>
  @endforeach
</div>
</div>
