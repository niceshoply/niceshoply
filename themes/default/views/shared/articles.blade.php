{{--
================================================================================
【文件说明】
  文章列表页完整局部模板 —— 用于渲染文章（新闻/博客）列表页的主体区域，
  包含左侧文章列表（含标签、摘要、日期、阅读量）和右侧边栏（搜索框、分类、标签）。
  无数据时自动 fallback 渲染 shared.no-data。

【引用方式】
  @include('shared.articles')
  ※ 所需变量由控制器注入到视图，无需手动传参。

【可用变量】
  来自控制器注入（必须）：
    $articles               — 分页后的文章集合（支持 ->count() 判断）
      每个 $article 包含：
        ->url               — 文章详情页 URL
        ->image             — 封面图路径（传入 image_resize() 裁剪为 200×150）
        ->translation       — 当前语言翻译对象（?-> 可选链）
          ->title           — 文章标题
          ->summary         — 文章摘要（由 sub_string() 截断至 180 字）
        ->tags              — 文章标签集合
          每个 $tag 包含：
            ->url           — 标签筛选页 URL
            ->translation   — 标签翻译对象（?-> 可选链）
              ->name        — 标签名称
        ->created_at        — 创建时间（Carbon 对象，格式化为 Y-m-d）
        ->viewed            — 文章阅读量（整数）

  来自控制器注入（可选）：
    $catalogs               — 文章分类集合，用于右侧边栏分类导航
      每个 $catalog 包含：
        ->url               — 分类页 URL
        ->translation       — 分类翻译对象（?-> 可选链）
          ->title           — 分类名称
    $tags                   — 全局标签集合，用于右侧边栏标签云
      每个 $tag 包含：
        ->url               — 标签筛选页 URL
        ->translation       — 标签翻译对象（?-> 可选链）
          ->name            — 标签名称

  全局辅助函数：
    image_resize($path, 200, 150) — 将图片裁剪为 200×150 并返回 URL
    sub_string($str, 180)         — 截取字符串至指定长度

  URL 请求参数：
    request('keyword')      — 关键词搜索，用于初始化搜索框的默认值

  多语言翻译 Key：
    front/article.keyword           — 搜索框占位符文字
    front/article.search            — 搜索按钮文字
    front/article.news_classification — 右侧边栏分类标题
    front/article.news_tag          — 右侧边栏标签标题

【输出内容】
  Bootstrap 栅格布局（col-12 col-md-9 + col-12 col-md-3）：
  - 左栏（9列）：.newest-box 列表，每项含封面图 + 标题 + 标签 + 摘要 + 日期 + 阅读量
  - 右栏（3列）：.newes-sidebar 边栏，含关键词搜索框 + 分类列表 + 标签列表
  - 无数据时渲染 @include('shared.no-data')

【JS 行为（@push('footer')）】
  - 点击搜索按钮：将 keyword 参数追加到当前 URL 并跳转
  - 搜索框回车：触发搜索按钮点击
  - 依赖全局工具函数：inno.updateQueryStringParameter、inno.removeURLParameters

【自定义建议】
  1. 右侧边栏为可选项，$catalogs 和 $tags 不存在时不渲染对应区块，
     控制器不传这两个变量时边栏将只显示搜索框。
  2. 如需自定义列表每行布局（如横排 vs 竖排），修改 .newest-item 内的 HTML 结构。
  3. 分页控件需在父视图中单独引入（本模板不包含分页导航）。
  4. 搜索功能依赖后端路由支持 keyword 查询参数过滤。
================================================================================
--}}
<div class="container mt-3 mt-md-5">
  <div class="row">
    <div class="col-12 col-md-9">
      @if ($articles->count())
        <div class="newest-box">
          @foreach($articles as $article)
          <div class="newest-item">
            <div class="item-img">
              <a href="{{ $article->url }}">
                <img src="{{ image_resize($article->image, 200, 150) }}" class="img-fluid">
              </a>
            </div>
            <div class="item-content d-flex flex-column justify-content-between">
              <div class="content-top">
                <div class="item-title mt-2"><a href="{{ $article->url }}">{{ $article->translation?->title ?? '' }}</a></div>
                @if ($article->tags->count())
                <div class="newes-tags">
                  <i class="bi bi-tags me-1"></i>
                  <div class="d-flex flex-wrap">
                    @foreach($article->tags as $tag)
                      <a href="{{ $tag->url }}">{{ $tag->translation?->name ?? '' }}</a>
                    @endforeach
                  </div>
                </div>
                @endif
                <div class="item-summary">{{ sub_string($article->translation?->summary ?? '', 180) }}</div>
              </div>
              <div class="item-date text-secondary">
                <span><i class="bi bi-clock"></i> {{ $article->created_at->format('Y-m-d') }}</span>
                <span class="ms-3"><i class="bi bi-eye"></i> {{ $article->viewed }}</span>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      @else
        @include('shared.no-data', ['text' => '没有数据 ~'])
      @endif
    </div>
    <div class="col-12 col-md-3">
      <div class="newes-sidebar">
        <div class="search-box">
          <div class="input-group input-group-lg">
            <input type="text" class="form-control" value="{{ request('keyword') }}" placeholder="{{__("front/article.keyword")}}">
            <button class="btn btn-primary" type="button">{{__("front/article.search")}}</button>
          </div>
        </div>

        @if(isset($catalogs) && $catalogs)
          <div class="sidebar-item">
            <div class="sidebar-title">{{__("front/article.news_classification")}}</div>
            <div class="sidebar-list">
              <ul>
                @foreach($catalogs as $catalog)
                  <li><a
                        href="{{ $catalog->url }}">{{ $catalog->translation?->title ?? '' }}</a>
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif

        @if(isset($tags) && $tags)
          <div class="sidebar-item">
            <div class="sidebar-title">{{__("front/article.news_tag")}}</div>
            <div class="sidebar-list">
              <ul>
                @foreach($tags as $tag)
                  <li><a href="{{ $tag->url }}">{{ $tag->translation?->name ?? '' }}</a></li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@push('footer')
  <script>
    $(function () {
      $('.search-box button').click(function () {
        var keyword = $('.search-box input').val();
        if (keyword) {
          window.location.href = inno.updateQueryStringParameter(window.location.href, 'keyword', keyword);
          return;
        }

        window.location.href = inno.removeURLParameters(window.location.href, 'keyword')
      });

      $('.search-box input').keydown(function (e) {
        if (e.keyCode === 13) {
          $('.search-box button').trigger('click');
        }
      });
    });
  </script>
@endpush
