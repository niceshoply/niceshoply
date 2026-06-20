{{--
  ============================================================
  【文件说明】
    文章（博客）列表页面。
    展示全部已发布的文章，支持按分类筛选、关键词搜索，带分页功能。

  【触发场景】
    用户访问前台博客/文章列表页时渲染。

  【对应路由 / 控制器】
    路由名称：articles.index
    控制器：Front\ArticleController@index
    URL 示例：/{locale}/articles

  【可用变量】
    $articles  — LengthAwarePaginator  分页文章列表，每条含：
                   - translation->title   当前语言标题
                   - translation->summary 摘要
                   - image                封面图路径（用 image_origin() 生成完整 URL）
                   - url                  文章详情页 URL（模型内置）
                   - catalog              所属分类对象（含 translation->title）
                   - created_at           发布时间
                   - viewed               浏览次数
    $catalogs  — Collection  文章分类列表，供侧边栏展示，含：
                   - translation->title   分类名称
                   - url                  分类过滤 URL

  【Sections】
    body-class  → page-news（可自定义页面根类名，用于 CSS 定制）
    content     → 页面主体内容区

  【钩子点】
    @hookinsert('article.index.top')    — 文章列表顶部，可插入 Banner 或公告
    @hookinsert('article.index.bottom') — 文章列表底部，可插入推荐内容

  【共享局部视图】
    @include('shared.articles') — 通用文章列表局部视图（含列表 + 分页）

  【自定义建议】
    - 修改 shared/articles.blade.php 调整文章列表的卡片样式
    - 在钩子点插入广告 Banner 或热门标签云
    - 可在此页面增加 @section('title') 自定义 SEO 标题
  ============================================================
--}}
@extends('layouts.app')

@section('body-class', 'page-news')

@section('content')

<x-front-breadcrumb type="route" value="articles.index" title="{{ __('front/article.articles') }}" />

@hookinsert('article.index.top')

@include('shared.articles')

@hookinsert('article.index.bottom')

@endsection