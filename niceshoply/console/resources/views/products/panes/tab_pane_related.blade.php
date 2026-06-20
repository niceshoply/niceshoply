<div class="tab-pane fade mt-3 col-md-6" id="relation-tab-pane" role="tabpanel"
     aria-labelledby="relation-tab" tabindex="5">
  <x-console-form-autocomplete-list name="related_ids[]"
                                  :value="old('related_ids', $product->relations->pluck('relation_id')->toArray() ?? [])"
                                  :selectedItems="$selectedRelatedProducts"
                                  placeholder="{{ __('console/product.searching_products') }}"
                                  title="{{ __('console/product.related_products') }}" api="{{ route('api.console.products.index') }}"/>
</div>
