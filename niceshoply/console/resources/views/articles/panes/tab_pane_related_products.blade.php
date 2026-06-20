<div class="tab-pane fade mt-3" id="related-products-tab-pane" role="tabpanel" aria-labelledby="related-products-tab" tabindex="0">
  <x-console-form-autocomplete-list 
    name="product_ids[]" 
    :value="old('product_ids', $article->products->pluck('id')->toArray() ?? [])"
    :selectedItems="$selectedRelatedProducts"
    placeholder="{{ __('console/article.search_related_products') }}"
    title="{{ __('console/article.related_products') }}"
    api="{{ route('api.console.products.index') }}" />
</div>

@hookinsert('console.article.edit.related_products.bottom')