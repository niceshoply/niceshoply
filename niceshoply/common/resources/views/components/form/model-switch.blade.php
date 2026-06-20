<div class="form-check form-switch d-flex align-items-center">
  <input type="hidden" name="{{ $name }}" value="0">
  <input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" 
         {{ $value ? 'checked' : '' }} 
         id="{{ $id }}" style="cursor: pointer;">
  <label class="form-check-label" for="{{ $id }}" style="cursor: pointer;">
    {{ $label }}
  </label>
</div>