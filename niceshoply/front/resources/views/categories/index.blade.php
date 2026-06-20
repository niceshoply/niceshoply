{{--
===========================================================================
【文件说明】
  分类列表页，展示全站所有顶级（或全部）分类，以网格卡片形式呈现分类图片、
  名称、描述摘要及"查看更多"链接。支持关键词搜索过滤。

【对应路由 / 控制器】
  路由名称  : categories.index
  URL 示例  : /categories  或  /{lang}/categories
  控制器    : App\Http\Controllers\Front\CategoryController@index

【可用变量】
  $categories  — LengthAwarePaginator  分类分页集合（Category 模型）
                 常用属性:
                   id, fallbackName()（默认语言名称）, url（详情页 URL）
                   image（图片路径）, translation->description（翻译描述）
                 分页方法: count(), total(), appends()->links()
  $keyword     — string|null  搜索关键词（URL 参数 ?keyword=xxx）；
                 有值时显示搜索结果标题，无值时正常列表

【URL 查询参数】
  keyword  — 搜索关键词，用于过滤分类名称

【Sections / Blocks】
  body-class — 值为 'page-categories'
  content    — 页面主体内容

【包含的局部模板】
  console::vendor/pagination/bootstrap-4 — 底部分页导航
  x-common-no-data                        — 无数据时的占位提示组件

【Blade 组件】
  <x-front-breadcrumb>  — 面包屑组件（type="route", value="categories.index"）

【辅助函数】
  image_resize($path, 300, 200) — 生成 300×200 分类封面图 URL
  mb_substr(strip_tags(...), 0, 100) — 截取纯文本描述（去 HTML 标签后取前 100 字符）

【插件钩子】
  @hookinsert('category.index.top')    — 分类列表顶部（可插入 Banner、筛选等）
  @hookinsert('category.index.bottom') — 分类列表底部（可插入推荐区块等）

【自定义建议】
  - 修改卡片列数：调整 col-6 col-md-4 col-lg-3 等 Bootstrap 栅格类。
  - 修改描述截取长度：调整 mb_substr() 的第三个参数（当前 100）。
  - 新增搜索框 UI：在面包屑下方或列表上方添加搜索表单，action 指向当前页面，
    参数名使用 keyword。
  - 分类卡片 hover 效果：为 .card 添加 CSS transition 和 transform。
===========================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-categories')

@section('content')
<x-front-breadcrumb type="route" value="categories.index" title="{{ __('front/category.categories') }}" />

@hookinsert('category.index.top')

<div class="container">
  @if($keyword)
    <div class="mb-4">
      <h2 class="h4">{{ __('front/common.search_results') }}: "{{ $keyword }}"</h2>
      <p class="text-muted">{{ __('front/common.found_categories', ['count' => $categories->total()]) }}</p>
    </div>
  @endif

  @if($categories->count() > 0)
    <div class="row g-4">
      @foreach($categories as $category)
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100 border-0 shadow-sm">
            @if($category->image)
              <a href="{{ $category->url }}">
                <img src="{{ image_resize($category->image, 300, 200) }}" 
                     class="card-img-top" 
                     alt="{{ $category->fallbackName() }}">
              </a>
            @endif
            <div class="card-body text-center">
              <h5 class="card-title">
                <a href="{{ $category->url }}" class="text-decoration-none">
                  {{ $category->fallbackName() }}
                </a>
              </h5>
              @if($category->translation && $category->translation->description)
                <p class="card-text text-muted small">
                  {{ mb_substr(strip_tags($category->translation->description), 0, 100) }}...
                </p>
              @endif
              <a href="{{ $category->url }}" class="btn btn-sm btn-outline-primary">
                {{ __('front/common.view_more') }}
              </a>
            </div>
          </div>
        </div>
      @endforeach
    </div>

    {{ $categories->appends(request()->query())->links('console::vendor/pagination/bootstrap-4') }}
  @else
    <div class="text-center py-5">
      <x-common-no-data />
      @if($keyword)
        <p class="text-muted mt-3">{{ __('front/common.no_search_results') }}</p>
      @endif
    </div>
  @endif
</div>

@hookinsert('category.index.bottom')

@endsection

