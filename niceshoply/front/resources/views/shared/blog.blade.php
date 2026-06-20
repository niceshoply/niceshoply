{{--
================================================================================
【文件说明】
  博客文章卡片局部模板 —— 用于在博客列表页、首页博客推荐区等位置
  渲染单篇博客文章的缩略图、所属分类、标题、作者及发布时间。

【引用方式】
  @foreach ($blogs as $item)
      @include('shared.blog')
  @endforeach
  ※ 变量名必须是 $item，由父级 @foreach 循环注入。

【可用变量】
  来自父模板 / 循环注入：
    $item                       — 博客文章模型对象，包含：
      ->translation             — 当前语言的翻译对象（不存在则跳过整个卡片渲染）
        ->title                 — 文章标题
      ->url                     — 文章详情页 URL
      ->image                   — 文章封面图路径（传入 image_resize() 裁剪）
      ->catalog                 — 所属分类对象（可为 null）
        ->translation           — 分类翻译对象
          ->title               — 分类名称
      ->author                  — 文章作者名（字符串，可为空）
      ->created_at              — 文章创建时间（Carbon 对象，格式化为 Y-m-d）

  全局辅助函数：
    image_resize($path, 300, 300) — 将图片裁剪为 300×300 尺寸并返回 URL

【输出内容】
  渲染一个 .blog-item 容器，包含：
  - 图片区：封面缩略图（300×300），点击跳转文章详情
  - 信息区：分类标签（可选）+ 文章标题 + 作者（可选）+ 发布日期

【自定义建议】
  1. 若需展示文章摘要，可在 .blog-title 下方添加：
     <div class="blog-summary">{{ $item->translation->summary ?? '' }}</div>
  2. 图片裁剪尺寸 300×300 可根据主题卡片比例调整，
     修改 image_resize() 的第二、三参数即可。
  3. 分类和作者均为可选字段，模板已做 @if 守护，不存在时不渲染。
  4. 如需添加阅读量、标签等字段，参考 shared/articles.blade.php 中的实现方式。
================================================================================
--}}
@if ($item->translation)
  <div class="blog-item">
    <div class="image">
      <a href="{{ $item->url }}">
        <img src="{{ image_resize($item->image, 300, 300) }}" class="img-fluid">
      </a>
    </div>
    <div class="blog-item-info">
      @if($item->catalog->translation ?? '')
        <div class="blog-catalog"><a href="{{ $item->url }}">{{ $item->catalog->translation->title }}</a></div>
      @endif
      <div class="blog-title"><a href="{{ $item->url }}">{{ $item->translation->title }}</a></div>
      <div class="author-wrap">
        @if($item->author)
          <div class="blog-author"><i class="bi bi-person"></i> {{ $item->author }}</div>
        @endif
        <div class="blog-created"><i class="bi bi-clock"></i> {{ $item->created_at->format('Y-m-d') }}</div>
      </div>
    </div>
  </div>
@endif