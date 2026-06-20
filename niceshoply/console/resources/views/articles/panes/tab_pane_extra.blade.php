<div class="tab-pane fade mt-3" id="extra-tab-pane" role="tabpanel" aria-labelledby="extra-tab" tabindex="0">
  <div class="row">
    <div class="col-12 col-md-6">
      <x-common-form-select title="{{ __('console/article.catalog') }}" name="catalog_id"
                            :value="old('catalog_id', $article->catalog_id ?? 0)"
                            :options="$catalogs" key="id" label="name" :emptyOption="true" />

      <x-console-form-autocomplete-list name="tag_ids[]"
                                      :value="old('tag_ids', $article->tags->pluck('id')->toArray() ?? [])"
                                      :selectedItems="$selectedTags ?? []"
                                      placeholder="{{ __('console/article.tag_search') }}"
                                      title="{{ __('console/article.tag') }}"
                                      api="{{ route('api.console.tags.index') }}" />
    </div>

    <div class="col-12 col-md-6">
      <x-common-form-input title="{{ __('console/common.position') }}" name="position"
                           :value="old('position', $article->position ?? 0)" />

      <x-common-form-input title="{{ __('console/article.viewed') }}" name="viewed"
                           :value="old('viewed', $article->viewed ?? 0)" />

      <x-common-form-input title="{{ __('console/article.author') }}" name="author"
                           :value="old('author', $article->author ?? '')" />
    </div>
  </div>
</div>