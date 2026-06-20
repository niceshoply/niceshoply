<x-console::form.row :title="$title" :required="$required">
  <select class="form-select me-3" name="{{ $name }}">
    @if ($emptyOption)
    <option value="">{{ __('console/common.please_choose') }}</option>
    @endif
    @foreach ($options as $option)
      <option value="{{ $option[$key] }}" {{ $option[$key] == $value ? 'selected': '' }}>{{ $option[$label] }}</option>
    @endforeach
  </select>
  {{ $slot }}
</x-console::form.row>
