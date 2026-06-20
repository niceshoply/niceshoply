{{--
  ============================================================
  【文件说明】
    文章分类（目录/Catalog）详情页。
    展示指定文章分类下的所有文章列表，通过 shared.articles 局部模板渲染。
    文章分类相当于博客的"栏目"，一个分类可包含多篇文章。

  【对应路由/控制器】
    路由名称（GET）：catalogs.show
    URL 示例：/{locale}/catalogs/{catalog_slug}
    控制器：Front\CatalogController@show

  【可用变量】
    $catalog  — 文章分类对象（Catalog 模型），含：
                  id / name / slug
                  description（分类描述，富文本）
                  image（封面图路径）
                  articles（该分类下的文章集合）
    MetaInfo  — 由 \NiceShoply\Common\Libraries\MetaInfo::getInstance($catalog) 自动生成
                SEO title / description / keywords（来自分类的 meta 字段）

  【Sections】
    body-class → 'page-news'
    title      → SEO 标题（MetaInfo 生成）
    description → SEO 描述（MetaInfo 生成）
    keywords   → SEO 关键词（MetaInfo 生成）
    content    → 文章列表（通过 shared.articles 渲染）

  【包含的局部模板】
    shared.articles — 文章列表网格（含分页），接收 $articles、$catalog 等变量

  【插件钩子】
    @hookinsert('catalog.show.top')    — 文章列表顶部
    @hookinsert('catalog.show.bottom') — 文章列表底部

  【自定义建议】
    - 可在 shared.articles 引用前添加 $catalog 的横幅/介绍区块
    - 如需在页面顶部显示分类封面图，在 @include 前手动渲染 $catalog->image
  ============================================================
--}}
{{--
===========================================================================
【文件说明】
  目录（Catalog）详情页，展示某个内容目录（如"新闻"、"博客"、"帮助中心"等）
  下的文章列表。通过引入 shared.articles 公共文章列表组件来渲染内容，
  自身结构极为精简，扩展点主要集中在插件钩子和 shared/articles 模板。

【对应路由 / 控制器】
  路由名称  : catalogs.show
  URL 示例  : /catalog/{slug}  或  /{lang}/catalog/{slug}
  控制器    : App\Http\Controllers\Front\CatalogController@show

【可用变量】
  $catalog  — Catalog 模型  当前目录对象
              常用属性:
                id, name（多语言目录名称）, slug（URL 别名）
                description（目录描述）
              MetaInfo 接口:
                getTitle()       — SEO 标题（优先自定义，否则取 name）
                getDescription() — SEO 描述
                getKeywords()    — SEO 关键词

【Sections / Blocks】
  body-class  — 值为 'page-news'（目录页复用新闻页样式类）
  title       — SEO 标题（由 MetaInfo 自动生成）
  description — SEO 描述
  keywords    — SEO 关键词
  content     — 页面主体内容

【包含的局部模板】
  shared.articles  — 文章列表通用组件，负责渲染文章卡片列表、分页等
                     该模板内部使用的变量（由控制器注入）通常包括:
                       $articles  — LengthAwarePaginator  文章分页集合
                       $catalog   — Catalog 模型（可供文章列表引用当前目录信息）

【Blade 组件】
  <x-front-breadcrumb type="catalog" :value="$catalog" />
    — 面包屑组件，type="catalog" 时自动生成目录层级面包屑

【插件钩子】
  @hookinsert('catalog.show.top')    — 文章列表顶部（可插入目录简介、置顶文章、Banner 等）
  @hookinsert('catalog.show.bottom') — 文章列表底部（可插入订阅表单、相关目录推荐等）

【自定义建议】
  - 修改文章列表样式：编辑 shared/articles.blade.php 中的布局和卡片结构。
  - 在顶部展示目录描述：通过 @hookinsert('catalog.show.top') 注入，
    或直接在 @include('shared.articles') 前添加:
      @if($catalog->description)
        <div class="catalog-desc">{{ $catalog->description }}</div>
      @endif
  - 为不同 catalog slug 定制不同布局：在控制器中根据 slug 传入不同的视图，
    或在此模板中添加 @if($catalog->slug === 'blog') ... @endif 条件分支。
  - SEO 优化：在后台为每个目录配置专属的 Meta 标题和描述，MetaInfo 会自动调用。
===========================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-news')

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($catalog)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($catalog)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($catalog)->getKeywords())

@section('content')

  <x-front-breadcrumb type="catalog" :value="$catalog"/>

  @hookinsert('catalog.show.top')

  @include('shared.articles')

  @hookinsert('catalog.show.bottom')

@endsection

