{{--
  ============================================================
  【文件说明】
    CMS 自定义页面展示模板（通用页面渲染入口）。
    所有后台创建的 CMS 页面（关于我们、联系我们、服务介绍等）均通过此模板渲染。
    支持两种渲染模式：
      1. 若控制器通过自定义模板文件生成了 $result（HTML 字符串），则直接输出 $result；
      2. 否则退回默认模式，展示页面的 title + content 富文本内容。

  【触发场景】
    用户访问前台自定义 CMS 页面时渲染（如 /about、/contact 等）。

  【对应路由 / 控制器】
    路由名称：pages.show
    控制器：Front\PageController@show
    URL 示例：/{locale}/pages/{slug}

  【可用变量】
    $page    — Page 模型  CMS 页面对象，主要属性：
                 - translation->title    当前语言页面标题
                 - translation->content  当前语言正文 HTML（用 {!! !!} 输出，支持富文本）
                 - show_breadcrumb       bool，是否显示面包屑导航
                 - meta_title / meta_description / meta_keywords  SEO 信息（由 MetaInfo 读取）
                 - slug                  URL 别名
    $result  — string|null  可选，由控制器渲染自定义模板后传入的 HTML 字符串。
               当页面关联了独立的 Blade 模板文件时，控制器会将其渲染为字符串赋给 $result。
               示例模板文件：_sample_about.blade.php、_sample_products.blade.php 等。

  【SEO Meta】
    MetaInfo::getInstance($page)->getTitle()       — 优先读取 meta_title，回退到 title
    MetaInfo::getInstance($page)->getDescription() — 读取 meta_description
    MetaInfo::getInstance($page)->getKeywords()    — 读取 meta_keywords

  【Sections】
    body-class   → page-news-details（可在后台页面配置中自定义）
    title        → 页面 SEO 标题
    description  → 页面 SEO 描述
    keywords     → 页面 SEO 关键词
    content      → 页面主体内容区

  【钩子点】
    @hookinsert('page.show.top')    — 页面顶部（面包屑下方），可插入横幅
    @hookinsert('page.show.bottom') — 页面底部，可插入联系表单、CTA 等

  【自定义建议】
    - 若需要为某个页面使用完全独立的布局，可创建专属 Blade 模板文件，
      后台在页面配置中绑定模板路径，控制器会将其渲染为 $result 注入
    - 参考 _sample_about.blade.php、_sample_products.blade.php 实现自定义页面模板
    - 可根据 $page->slug 做条件判断，为特定页面追加专属 CSS/JS
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-news-details')

@section('title', \NiceShoply\Common\Libraries\MetaInfo::getInstance($page)->getTitle())
@section('description', \NiceShoply\Common\Libraries\MetaInfo::getInstance($page)->getDescription())
@section('keywords', \NiceShoply\Common\Libraries\MetaInfo::getInstance($page)->getKeywords())

@section('content')
  @if($page->show_breadcrumb)
      <x-front-breadcrumb type="page" :value="$page" />
  @endif

  @hookinsert('page.show.top')

  @if(isset($result))
    {!! $result !!}
  @else
    <div class="container mt-3 mt-md-5">
      <div class="row justify-content-center">
        <div class="col-12">
          <div class="newest-box">
            <div class="newes-title">{{ $page->translation->title }}</div>
            <div class="newes-content">
              {!! $page->translation->content !!}
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  @hookinsert('page.show.bottom')
@endsection
