{{--
================================================================================
【文件说明】
  热门商品多分类 Tab 展示模块组件（Hot Products Module）。
  在首页或自定义页面中展示按分类分组的热门商品，
  当有多个分组时自动显示 Bootstrap Tab 切换标签，点击不同 Tab 展示对应分类商品。
  只有一个分组时，隐藏 Tab 标签，直接展示商品网格。

  在主题模板中通过 nice 标签调用：
    {nice:hot-products title="热销商品" limit="8"}
    {nice:hot-products title="热门推荐" category_ids="1,2,3" limit="4"}
  等价于 Blade 组件：
    <x-nice-hot-products title="热销商品" limit="8" />

【注册方式】
  FrontServiceProvider 中以别名 "nice-hot-products" 注册：
    Blade::component('nice-hot-products', Components\Nice\HotProducts::class);

【可用变量 / Props】
  以下 Props 通过标签属性传入组件类，组件类处理后注入视图：
  - $title          — 模块标题文字（字符串），为空时不显示标题区域
  - $limit          — 每个分类 Tab 显示的商品数量（整数，默认 8）
  - $category_ids   — 指定显示哪些分类的商品（逗号分隔的 ID 字符串，可选）
                      不传时由系统自动选取热门分类
  - $groups         — 分组数组（由组件类自动查询并注入），每项结构：
      [
        'category_name' => '分类名称',         // Tab 按钮上显示的分类名
        'products'      => Collection|array,   // 该分类下的商品对象列表
      ]
      多个分组时显示 Tab，单个分组时隐藏 Tab 直接展示商品

  Tab 面板 ID 规则：nice-hot-tab-{{ $loop->iteration }}（从 1 开始）

  商品卡片子模板：
  - @include('shared.product') 使用 $product 变量渲染商品卡片
    商品列固定为 col-6 col-md-4 col-lg-3（每行最多 4 列）

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 修改 Tab 按钮样式：将 .nav-tabs 替换为 .nav-pills 实现胶囊样式切换。
  2. 商品列固定为 col-lg-3（每行 4 列），如需改为每行 5 列可修改为 col-lg-2（取近似值）
     或添加自定义 CSS 类 .col-lg-5ths { width: 20%; }
  3. 若只需展示单一分类的热门商品，直接传入对应 category_ids，
     Tab 栏会自动隐藏，等同于普通商品列表展示效果。
  4. Tab 功能依赖 Bootstrap 5 的 Tab 组件，需确保布局中已引入 bootstrap.bundle.js。
  5. 热门商品的筛选逻辑（如按销量、浏览量、手动标记）在组件类中实现，
     可通过继承/替换组件类来自定义排序规则。
================================================================================
--}}
<section class="module-line">
  <div class="container">
    @if($title)
      <div class="module-title-wrap">
        <div class="module-title">{{ $title }}</div>
      </div>
    @endif

    @if (count($groups) > 1)
      <ul class="nav nav-tabs">
        @foreach ($groups as $group)
          <li class="nav-item" role="presentation">
            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                    data-bs-toggle="tab"
                    data-bs-target="#nice-hot-tab-{{ $loop->iteration }}"
                    type="button">{{ $group['category_name'] }}</button>
          </li>
        @endforeach
      </ul>
    @endif

    <div class="tab-content">
      @foreach ($groups as $group)
        <div class="tab-pane fade show {{ $loop->first ? 'active' : '' }}"
             id="nice-hot-tab-{{ $loop->iteration }}">
          <div class="row gx-3 gx-lg-4">
            @foreach ($group['products'] as $product)
              <div class="col-6 col-md-4 col-lg-3">
                @include('shared.product')
              </div>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>
