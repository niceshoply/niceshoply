{{--
================================================================================
【文件说明】
  标签详情页（Tag 聚合页）。展示某个标签下的所有文章/博客列表，支持 SEO 元信息
  自定义（标题、描述、关键词）。内容列表通过共享模板 shared.articles 渲染，
  保持与其他文章列表页（如分类页、博客首页）的一致性。

【对应路由 / 控制器】
  路由名称：tags.show
  路由参数：{slug} 或 {id} — 标签标识符
  HTTP 方法：GET
  控制器：Front\TagController@show（或等价方法）

【可用变量】（由控制器注入到视图）
  $tag       \App\Models\Tag（或等价模型）   标签对象，包含：
               - name         string   标签名称
               - slug         string   标签 slug（URL 友好名）
               - description  string   标签描述（可选）
               - meta_title   string   SEO 标题（为空时由 MetaInfo 自动生成）
               - meta_desc    string   SEO 描述（为空时由 MetaInfo 自动生成）
               - meta_keywords string  SEO 关键词（为空时由 MetaInfo 自动生成）
  $articles  Paginator   文章/博客分页集合（传入 shared.articles 模板使用）
             注意：该变量由 shared.articles 模板消费，当前文件中不直接访问

【SEO 元信息】
  使用 \NiceShoply\Common\Libraries\MetaInfo::getInstance($tag) 自动生成：
    - @section('title')       → MetaInfo::getTitle()
    - @section('description') → MetaInfo::getDescription()
    - @section('keywords')    → MetaInfo::getKeywords()
  MetaInfo 会优先使用模型自带的 meta_* 字段，为空时回退到默认规则生成。

【Sections】
  body-class   → page-news（与文章列表类页面共用）
  title        → SEO 页面标题
  description  → SEO 描述
  keywords     → SEO 关键词
  content      → 页面主体内容

【前端交互】
  本页无独立 JS 交互，依赖 shared.articles 模板中可能包含的分页/筛选逻辑。

【插件钩子】
  @hookinsert('tag.show.top')   标签页顶部（文件中出现两次，第二处建议改为
                                 'tag.show.bottom' 以区分上下钩子位置）

【共享模板依赖】
  @include('shared.articles')   文章列表共享模板，渲染文章卡片列表和分页，
                                 消费 $articles 分页变量；如需修改文章展示样式，
                                 请编辑 resources/views/shared/articles.blade.php

【自定义建议】
  1. 如需在标签页顶部显示标签名称和描述，可在 @hookinsert('tag.show.top') 后添加：
       <div class="container"><h1>{{ $tag->name }}</h1>
         @if($tag->description)<p>{{ $tag->description }}</p>@endif
       </div>
  2. body-class 当前为 page-news，如需与普通文章列表区分样式，
     可改为 'page-tag'，并在 CSS 中定义对应样式规则。
  3. 标签页与分类页（category.show）、作者页结构类似，可参考彼此模板互相扩展。
  4. 如需标签云/相关标签展示，可通过控制器注入 $related_tags 变量并在此处渲染。
================================================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-news')

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($tag)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($tag)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($tag)->getKeywords())

@section('content')

  <x-front-breadcrumb type="tag" :value="$tag" />

  @hookinsert('tag.show.top')

  @include('shared.articles')

  @hookinsert('tag.show.top')

@endsection

