{{--
================================================================================
【文件说明】
  商品分类展示模块组件（Categories Module）。
  在首页或自定义页面中以图文网格形式展示商品分类入口，
  每个分类显示分类图片和分类名称，点击跳转到对应分类商品列表页。
  通常用于首页的「浏览分类」区域，引导用户快速进入感兴趣的分类。

  在主题模板中通过 nice 标签调用：
    {nice:categories cols="6" parent_id="0"}
    {nice:categories cols="4" parent_id="5"}
  等价于 Blade 组件：
    <x-nice-categories cols="6" parent_id="0" />

【注册方式】
  FrontServiceProvider 中以别名 "nice-categories" 注册：
    Blade::component('nice-categories', Components\Nice\Categories::class);

【可用变量 / Props】
  以下 Props 通过标签属性传入组件类，组件类处理后注入视图：
  - $cols        — 每行显示列数（整数，默认 6），Bootstrap 列宽：col-lg-{{ 12 / $cols }}
  - $parent_id   — 父分类 ID（整数，默认 0 表示顶级分类），用于筛选显示哪一级的分类
  - $limit       — 显示分类数量限制（可选）
  - $categories  — 分类对象 Collection（由组件类自动查询并注入），每个对象包含：
      id          — 分类 ID
      image       — 分类图片路径（可为空，为空时不显示图片区域）
      url         — 分类页面 URL（自动生成，指向商品列表页并筛选该分类）
      fallbackName() — 方法，返回当前语言的分类名称（自动降级到默认语言）

  全局辅助函数：
  - image_origin($path)        — 将相对路径转为完整图片 URL
  - categories_tree()          — 获取完整分类树（组件类内部可能使用此函数）

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 为分类卡片添加 hover 效果（如缩放、阴影）：
     .module-line a:hover img { transform: scale(1.05); transition: .3s; }
  2. 图片建议保持统一的宽高比（如 1:1 正方形），可通过 CSS aspect-ratio 实现。
  3. 若需显示分类描述文字，可在 $cat->fallbackName() 下方添加
     <div class="text-muted small">{{ $cat->fallbackDescription() }}</div>
  4. parent_id 为 0 时显示顶级分类，传入具体 ID 可显示该分类的子分类，
     适合在分类详情页侧边栏使用。
  5. 分类图片和名称在后台「商品 → 分类」中管理，支持多语言名称配置。
================================================================================
--}}
<section class="module-line">
  <div class="container">
    <div class="row gx-3 gx-lg-4 justify-content-center">
      @foreach ($categories as $cat)
        <div class="col-4 col-md-3 col-lg-{{ 12 / $cols }}">
          <a href="{{ $cat->url }}" class="d-block text-center text-decoration-none">
            @if ($cat->image)
              <div class="mb-2">
                <img src="{{ image_origin($cat->image) }}" alt="{{ $cat->fallbackName() }}" class="img-fluid rounded">
              </div>
            @endif
            <div class="text-sm fw-medium text-dark">{{ $cat->fallbackName() }}</div>
          </a>
        </div>
      @endforeach
    </div>
  </div>
</section>
