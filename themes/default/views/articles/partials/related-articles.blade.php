{{--
  ============================================================
  【文件说明】
    文章详情页侧边栏 — 相关文章列表局部视图。
    展示与当前文章同分类的其他文章，每条含缩略图（60×60）、标题、
    发布时间、浏览数、摘要（最多 80 字）。若无相关文章则整块不渲染。

  【触发场景】
    由 articles/show.blade.php 通过 @include 引入，显示在侧边栏底部。

  【引入方式】
    @include('articles.partials.related-articles', ['relatedArticles' => $relatedArticles])

  【可用变量】
    $relatedArticles — Collection  相关文章集合，每条含：
                         - translation->title    当前语言标题（无 translation 则跳过）
                         - translation->summary  摘要（可为空）
                         - image                 缩略图路径（用 image_resize($path,60,60) 生成）
                         - url                   文章详情页 URL（模型内置属性）
                         - created_at            发布时间（Carbon 实例）
                         - viewed                浏览次数

  【辅助函数】
    image_resize($path, $width, $height) — 生成指定尺寸的缩略图 URL

  【内置样式】
    文件末尾内嵌了 <style> 块，定义了卡片式相关文章布局样式，
    可直接提取到主题 CSS 文件统一管理。

  【自定义建议】
    - 可修改 Str::limit($summary, 80) 的截断长度
    - 可调整 image_resize 尺寸适配不同设计
    - 建议将 <style> 内联样式移至主题 CSS 文件（避免重复输出）
    - 可增加「查看全部文章」按钮链接到 articles.index
  ============================================================
--}}
@if($relatedArticles && $relatedArticles->count() > 0)
<div class="related-articles-section mb-4">
  <div class="sidebar-title">{{ __('front/article.related_articles') }}</div>
  <div class="sidebar-list">
    <ul class="list-unstyled">
      @foreach($relatedArticles as $relatedArticle)
        @if(!$relatedArticle->translation)
          @continue
        @endif
        <li class="mb-3">
          <div class="related-article-item">
            @if($relatedArticle->image)
              <div class="related-article-image">
                <a href="{{ $relatedArticle->url }}">
                  <img src="{{ image_resize($relatedArticle->image, 60, 60) }}" alt="{{ $relatedArticle->translation->title }}" class="img-fluid rounded">
                </a>
              </div>
            @endif
            <div class="related-article-content">
              <h6 class="related-article-title">
                <a href="{{ $relatedArticle->url }}" class="text-decoration-none">
                  {{ $relatedArticle->translation->title }}
                </a>
              </h6>
              <div class="related-article-meta text-muted small">
                <i class="bi bi-clock me-1"></i>
                {{ $relatedArticle->created_at->format('Y-m-d') }}
                @if($relatedArticle->viewed > 0)
                  <span class="ms-2">
                    <i class="bi bi-eye me-1"></i>
                    {{ $relatedArticle->viewed }}
                  </span>
                @endif
              </div>
              @if($relatedArticle->translation->summary)
                <p class="related-article-summary text-muted small mt-1 mb-0">
                  {{ Str::limit($relatedArticle->translation->summary, 80) }}
                </p>
              @endif
            </div>
          </div>
        </li>
      @endforeach
    </ul>
  </div>
</div>
@endif

<style>
.related-articles-section {
  margin-bottom: 2rem;
}

.related-article-item {
  display: flex;
  gap: 0.75rem;
  padding: 0.75rem;
  border: 1px solid #e9ecef;
  border-radius: 0.375rem;
  transition: all 0.2s ease;
}

.related-article-item:hover {
  border-color: #dee2e6;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.related-article-image {
  flex-shrink: 0;
  width: 60px;
  height: 60px;
}

.related-article-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.related-article-content {
  flex: 1;
  min-width: 0;
}

.related-article-title {
  font-size: 0.875rem;
  line-height: 1.3;
  margin-bottom: 0.25rem;
}

.related-article-title a {
  color: #212529;
}

.related-article-title a:hover {
  color: #0d6efd;
}

.related-article-meta {
  font-size: 0.75rem;
}

.related-article-summary {
  font-size: 0.75rem;
  line-height: 1.3;
}
</style>