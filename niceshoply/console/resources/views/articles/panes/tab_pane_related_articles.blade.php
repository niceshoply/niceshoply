<div class="tab-pane fade mt-3" id="related-articles-tab-pane" role="tabpanel" aria-labelledby="related-articles-tab" tabindex="0">
  <x-console-form-autocomplete-list 
    name="related_article_ids[]" 
    :value="old('related_article_ids', $article->relatedArticles->pluck('relation_id')->toArray() ?? [])"
    :selectedItems="$selectedRelatedArticles"
    placeholder="{{ __('console/article.search_related_articles') }}"
    title="{{ __('console/article.related_articles') }}"
    api="{{ route('api.console.articles.index') }}" />
</div>

@hookinsert('console.article.edit.related_articles.bottom')