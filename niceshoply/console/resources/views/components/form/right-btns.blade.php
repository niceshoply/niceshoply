@section('page-title-right')
<div class="title-right-btns">
  <button type="button" class="btn btn-primary submit-form" form="{{ $formid ?? 'app-form' }}">{{ __('console/common.btn_save') }}</button>
  <button type="button" class="btn btn-outline-secondary ms-2 btn-back" onclick="window.history.back()">{{ __('console/common.btn_back') }}</button>
</div>
@endsection