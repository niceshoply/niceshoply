{{--
================================================================================
【文件说明】
  首页轮播图组件（Slideshow / Banner Swiper）。
  使用 Swiper.js 实现全宽自动轮播，展示后台配置的 Banner 图片列表，
  每张图片可设置独立链接。图片根据当前语言（front_locale_code()）显示对应版本。
  轮播图通常放置在首页顶部，是视觉冲击力最强的区域。

  在主题模板中通过 nice 标签调用：
    {nice:slideshow}
  等价于 Blade 组件：
    <x-nice-slideshow />

【注册方式】
  FrontServiceProvider 中以别名 "nice-slideshow" 注册：
    Blade::component('nice-slideshow', Components\Nice\Slideshow::class);

【可用变量 / Props】
  组件类从数据库读取后台配置的轮播图数据，向视图注入：
  - $slides  — 轮播图数组，每项结构：
      [
        'image' => [
          'zh' => 'uploads/slides/zh_banner.jpg',  // 各语言对应的图片路径
          'en' => 'uploads/slides/en_banner.jpg',
        ],
        'link'  => 'https://example.com/sale',     // 点击跳转链接，为空则不跳转
      ]
  当前语言图片通过 $slide['image'][front_locale_code()] 获取；
  若当前语言无图片配置，该 slide 会被跳过（@if 判断）。

  全局辅助函数：
  - front_locale_code()   — 返回当前前台语言代码（如 'zh'、'en'）
  - image_origin($path)   — 将相对路径转为完整图片 URL

【插件钩子】
  本组件内部暂无 @hookinsert 点位。

【自定义建议】
  开发新主题时，可以：
  1. 修改 Swiper 配置（loop、autoplay delay、pagination 样式等）以满足设计需求。
  2. 为不同屏幕尺寸配置 breakpoints，实现响应式切换（移动端显示不同图片或比例）。
  3. Swiper 挂载节点 id 为 "nice-slideshow-swiper"，若页面有多个轮播图，
     需为每个实例设置不同 id 避免冲突。
  4. 轮播图数据在后台「外观 → 轮播图」中配置，支持按语言上传不同图片。
  5. 若需添加箭头导航，在 Swiper 配置中增加：
     navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }
     并在 HTML 中添加对应的导航按钮元素。
  6. 页面需引入 Swiper.js，通常在布局文件的 @stack('styles') 中引入 CSS，
     @stack('scripts') 中引入 JS（或直接在全局引入）。
================================================================================
--}}
<section class="module-line">
  <div class="swiper" id="nice-slideshow-swiper">
    <div class="swiper-wrapper">
      @foreach ($slides as $slide)
        @if ($slide['image'][front_locale_code()] ?? false)
          <div class="swiper-slide">
            <a href="{{ $slide['link'] ?: 'javascript:void(0)' }}">
              <img src="{{ image_origin($slide['image'][front_locale_code()]) }}" class="img-fluid" alt="">
            </a>
          </div>
        @endif
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    new Swiper('#nice-slideshow-swiper', {
      loop: true,
      pagination: { el: '#nice-slideshow-swiper .swiper-pagination', clickable: true },
      autoplay: { delay: 4000, disableOnInteraction: true },
    });
  });
</script>
