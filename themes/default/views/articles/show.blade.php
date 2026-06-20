{{--
  ============================================================
  【文件说明】
    文章（博客）详情页面。
    展示单篇文章的完整内容，包含标题、标签、发布时间、作者、分类、
    浏览数、正文内容，以及右侧边栏的搜索框、分类导航、相关文章，
    以及正文下方的相关商品推荐模块。

  【触发场景】
    用户点击文章列表中的某篇文章，访问详情页时渲染。

  【对应路由 / 控制器】
    路由名称：articles.show
    控制器：Front\ArticleController@show
    URL 示例：/{locale}/articles/{slug}

  【可用变量】
    $article          — Article 模型  当前文章对象，主要属性：
                          - translation->title    当前语言标题
                          - translation->content  当前语言正文 HTML（需用 {!! !!} 输出）
                          - translation->summary  摘要
                          - image                 封面图路径
                          - author                作者名称
                          - created_at            发布时间（Carbon 实例）
                          - viewed                浏览次数
                          - tags                  标签集合（含 translation->name、url）
                          - catalog               所属分类（含 translation->title）
                          - url                   文章 URL（模型内置属性）
    $catalogs         — Collection  所有文章分类列表（传给 catalogs.blade.php）
    $relatedArticles  — Collection  同分类相关文章列表（传给 related-articles.blade.php）
    $relatedProducts  — Collection  文章关联商品列表（传给 related-products.blade.php）

  【SEO Meta】
    MetaInfo::getInstance($article)->getTitle()       — 自动读取文章 meta_title 或 title
    MetaInfo::getInstance($article)->getDescription() — 自动读取文章 meta_description 或 summary
    MetaInfo::getInstance($article)->getKeywords()    — 自动读取文章 meta_keywords

  【Sections】
    body-class   → page-news-details
    title        → 文章 SEO 标题
    description  → 文章 SEO 描述
    keywords     → 文章 SEO 关键词
    content      → 页面主体内容区

  【钩子点】
    @hookinsert('article.show.top')             — 文章页顶部（面包屑下方）
    @hookinsert('article.show.content.before')  — 正文内容前
    @hookinsert('article.show.content.after')   — 正文内容后
    @hookinsert('article.show.bottom')          — 整个文章区底部

  【局部视图】
    articles.partials.related-products  — 相关商品展示
    articles.partials.catalogs          — 侧边栏分类列表
    articles.partials.related-articles  — 侧边栏相关文章

  【自定义建议】
    - 可在 .newes-title / .newes-top 等 CSS 类上自定义排版样式
    - 在 article.show.content.before/after 钩子点插入广告位
    - 可增加评论模块（在 article.show.bottom 钩子后添加）
    - 搜索框目前仅有样式，需绑定 JS 或 form action 实现真正跳转
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-news-details')

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($article)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($article)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($article)->getKeywords())

@section('content')

  <x-front-breadcrumb type="article" :value="$article" />

  @hookinsert('article.show.top')

  <div class="container mt-3 mt-md-5">
    <div class="row">
      <div class="col-12 col-md-9">
        <div class="newest-box">
          <div class="newes-title">{{ $article->translation->title }}</div>
          @if ($article->tags->count())
          <div class="newes-tags mb-3 mt-n2">
            <i class="bi bi-tags me-1"></i>
            <div class="d-flex flex-wrap">
              @foreach($article->tags as $tag)
                <a href="{{ $tag->url }}">{{ $tag->translation->name }}</a>
              @endforeach
            </div>
          </div>
          @endif
          <div class="newes-top">
            <div class="newes-time"><i class="bi bi-clock"></i> {{ $article->created_at->format('Y-m-d') }}</div>
            <div class="newes-author"><i class="bi bi-person-square"></i> {{ $article->author ?? '' }}</div>
            <div class="newes-author"><i class="bi bi-ui-radios-grid"></i> {{ $article->catalog->translation->title ?? '' }}</div>
            <div class="newes-author"><i class="bi bi-eye"></i> {{ $article->viewed }}</div>
          </div>
          <div class="content">
            @hookinsert('article.show.content.before')

            <div class="mt-5 mb-5">
            {!! $article->translation->content !!}
            </div>

            @hookinsert('article.show.content.after')
          </div>
        </div>

        @include('articles.partials.related-products', ['relatedProducts' => $relatedProducts])

      </div>
      <div class="col-12 col-md-3">
        <div class="newes-sidebar">
          <div class="search-box mb-4">
            <div class="input-group input-group-lg">
              <input type="text" class="form-control" value="{{ request('keyword') }}" placeholder="{{ __('front/article.keyword')}}">
              <button class="btn btn-primary" type="button">{{ __('front/article.search') }}</button>
            </div>
          </div>
          
          @include('articles.partials.catalogs', ['catalogs' => $catalogs])
          
          @include('articles.partials.related-articles', ['relatedArticles' => $relatedArticles])
        </div>
      </div>
    </div>

    @hookinsert('article.show.bottom')
    
  </div>

@endsection
