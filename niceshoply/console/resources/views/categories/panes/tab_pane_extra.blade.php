<div class="tab-pane fade mt-3" id="extra-tab-pane" role="tabpanel" aria-labelledby="extra-tab" tabindex="0">
  <div class="row">
    <div class="col-12 col-md-6">
      <x-common-form-select title="{{ console_trans('category.parent') }}" name="parent_id" 
        :value="old('parent_id', $category->parent_id ?? 0)"
        :options="$categories" key="id" label="name" :emptyOption=false />
    </div>

    <div class="col-12 col-md-6">
      <x-common-form-input title="{{ console_trans('common.position') }}" name="position" 
        :value="old('position', $category->position ?? 0)"
        placeholder="{{ console_trans('common.position') }}" />
    </div>
  </div>
</div>

@hookinsert('console.category.edit.settings.bottom')