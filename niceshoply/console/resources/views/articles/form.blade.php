@extends('console::layouts.app')
@section('body-class', 'page-article-form')
@section('title', __('console/menu.articles'))

<x-console::form.right-btns formid="article-form" />

@push('header')
<script src="{{ asset('vendor/tinymce/5.9.1/tinymce.min.js') }}"></script>
@endpush

@section('content')
  <form class="needs-validation no-load" novalidate
    action="{{ $article->id ? console_route('articles.update', [$article->id]) : console_route('articles.store') }}"
    method="POST" id="article-form">
    @csrf
    @method($article->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12 col-md-12">
        <div class="card mb-3">
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane"
                  type="button" role="tab" aria-controls="basic-tab-pane"
                  aria-selected="true">{{ __('console/common.basic_info') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane" type="button"
                  role="tab" aria-controls="content-tab-pane" aria-selected="false">文章内容</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="extra-tab" data-bs-toggle="tab" data-bs-target="#extra-tab-pane"
                  type="button" role="tab" aria-controls="extra-tab-pane"
                  aria-selected="false">扩展信息</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane" type="button"
                  role="tab" aria-controls="seo-tab-pane" aria-selected="false">{{ __('console/product.seo') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="related-articles-tab" data-bs-toggle="tab" data-bs-target="#related-articles-tab-pane" type="button"
                  role="tab" aria-controls="related-articles-tab-pane" aria-selected="false">相关文章</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="related-products-tab" data-bs-toggle="tab" data-bs-target="#related-products-tab-pane" type="button"
                  role="tab" aria-controls="related-products-tab-pane" aria-selected="false">关联产品</button>
              </li>
              @hookinsert('console.article.edit.tab.nav.bottom')
            </ul>

            <div class="tab-content" id="myTabContent">
              @include('console::articles.panes.tab_pane_basic', $article)
              @include('console::articles.panes.tab_pane_content', $article)
              @include('console::articles.panes.tab_pane_extra', $article)
              @include('console::articles.panes.tab_pane_seo', $article)
              @include('console::articles.panes.tab_pane_related_articles', $article)
              @include('console::articles.panes.tab_pane_related_products', $article)

              @hookinsert('console.article.edit.tab.pane.bottom')
            </div>
          </div>
        </div>
      </div>
    </div>

    <button type="submit" class="d-none"></button>
  </form>
@endsection

@push('footer')
  <script>
    // Article form module
    const ArticleForm = {
      // Initialize the module
      init() {
        this.preventEnterSubmit();
      },

      // Prevent form submission on Enter key press
      preventEnterSubmit() {
        $('#article-form').on('keypress', function(e) {
          if (e.which === 13) {
            e.preventDefault();
          }
        });
      }
    };

    // Initialize when document is ready
    $(function() {
      ArticleForm.init();
    });
  </script>
@endpush
