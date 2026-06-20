@extends('console::layouts.app')

@section('title', __('console/menu.categories'))

<x-console::form.right-btns />

@section('content')
  <form class="needs-validation" novalidate id="app-form"
    action="{{ $category->id ? console_route('categories.update', [$category->id]) : console_route('categories.store') }}"
    method="POST">
    @csrf
    @method($category->id ? 'PUT' : 'POST')

    <div class="row">
      <div class="col-12">
        <div class="card mb-3">
          <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-tab-pane"
                  type="button" role="tab" aria-controls="basic-tab-pane"
                  aria-selected="true">{{ __('console/common.basic_info') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane"
                  type="button" role="tab" aria-controls="content-tab-pane"
                  aria-selected="false">{{ console_trans('category.description_tab') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="extra-tab" data-bs-toggle="tab" data-bs-target="#extra-tab-pane"
                  type="button" role="tab" aria-controls="extra-tab-pane"
                  aria-selected="false">{{ console_trans('category.extra_tab') }}</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-tab-pane"
                  type="button" role="tab" aria-controls="seo-tab-pane"
                  aria-selected="false">{{ __('console/product.seo') }}</button>
              </li>
              @hookinsert('console.category.edit.tab.nav.bottom')
            </ul>

            <div class="tab-content" id="myTabContent">
              @include('console::categories.panes.tab_pane_basic', $category)
              @include('console::categories.panes.tab_pane_content', $category)
              @include('console::categories.panes.tab_pane_extra', $category)
              @include('console::categories.panes.tab_pane_seo', $category)

              @hookinsert('console.category.edit.tab.pane.bottom')
            </div>
        </div>
      </div>


    </div>
  </form>
@endsection

@push('footer')
  <script></script>
@endpush
