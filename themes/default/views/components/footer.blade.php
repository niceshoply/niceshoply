{{--
================================================================================
【文件说明】
  前台底部页脚组件（Footer）。
  渲染网站的完整页脚，包括：关于我们简介、商品分类链接、文章目录链接、
  单页面链接、特惠活动链接、版权信息、ICP 备案号，以及支付图标展示区域。
  同时在页脚区域输出后台配置的自定义 JS 代码。

  在布局文件中通过 Blade 组件标签调用：
    <x-front-footer />

【注册方式】
  FrontServiceProvider 中以别名 "front-footer" 注册：
    Blade::component('front-footer', Components\Footer::class);
  组件类可通过插件钩子 "front.footer.component.class" 进行替换：
    fire_hook_filter('front.footer.component.class', Components\Footer::class)

【可用变量 / Props】
  组件类在 render() 中向视图注入以下变量：
  - $footerMenus  — 页脚菜单数据数组，包含以下键：
      'categories' => [['name' => '分类名', 'url' => '链接'], ...]   — 商品分类链接
      'catalogs'   => [['name' => '目录名', 'url' => '链接'], ...]   — 文章目录链接
      'pages'      => [['name' => '页面名', 'url' => '链接'], ...]   — 单页面链接
      'specials'   => [['name' => '名称',   'url' => '链接'], ...]   — 特惠活动链接

  全局辅助函数（在模板中随时可用）：
  - system_setting('key', $default)        — 读取系统设置
      'meta_description'  — 网站简介（多语言版用 system_setting_locale()）
      'icp_number'        — ICP 备案号（为空则不显示）
      'js_code'           — 后台配置的自定义 JavaScript 代码片段
  - system_setting_locale('key', $default) — 读取当前语言版本的系统设置
  - front_route('name')                    — 生成带语言前缀的前台路由 URL
  - niceshoply_brand_link()                — 输出 NiceShoply 品牌链接（HTML 字符串）
  - niceshoply_version()                   — 返回当前系统版本号
  - config('app.name')                     — 读取 Laravel 应用名称

【插件钩子】
  以下 @hookinsert 点位允许插件在对应位置插入额外 HTML 内容，请勿删除：
  - @hookinsert('layout.footer.top')    — Footer 最顶部（整个 footer 标签之前）
  - @hookinsert('layout.footer.bottom') — Footer 最底部（整个 footer 标签之后，js_code 之前）

【自定义建议】
  开发新主题时，可以：
  1. 修改 .footer-box 内的 HTML 结构以调整布局和分栏比例。
  2. 支付图标目前为静态图片（images/demo/payment/1-5.png），
     建议替换为实际支持的支付方式图片，或改为动态配置项。
  3. 版权年份通过 date('Y') 自动生成，无需手动更新。
  4. ICP 备案号在系统设置中配置后自动显示，否则隐藏该链接。
  5. 自定义 JS 代码（如第三方统计、客服插件）在后台「设置 → 系统设置 → js_code」
     中粘贴，系统会在页脚自动输出（使用 {!! !!} 原始输出，支持 <script> 标签）。
  6. 多语言场景下，页脚简介建议使用 system_setting_locale('meta_description', '')
     以获取当前语言对应的内容。
================================================================================
--}}
@hookinsert('layout.footer.top')

<footer id="appFooter">
  <div class="footer-box">
    <div class="container">
      <div class="footer-top-links">
        <div class="row">
          <div class="col-12 col-md-4 footer-item">
            <div class="about">
              <div class="footer-link-title">
                <span>{{ __('front/common.about_us') }}</span>
                <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
              </div>
              <div class="about-text footer-item-content">
                <p>
                  <b>{{ system_setting_locale('meta_description', '') }}</b>
                </p>
              </div>
            </div>
          </div>
          <div class="col-12 col-md-8">
            <div class="row">
              <div class="col-12 col-md-3 footer-item">
                <div class="footer-links">
                  <div class="footer-link-title">
                    <span>{{ __('front/common.products') }}</span>
                    <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
                  </div>
                  <ul class="footer-item-content">
                    @foreach($footerMenus['categories'] as $item)
                      <li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>
                    @endforeach
                  </ul>
                </div>
              </div>
              <div class="col-12 col-md-3 footer-item">
                <div class="footer-links">
                  <div class="footer-link-title">
                    <span>{{ __('front/common.news') }}</span>
                    <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
                  </div>
                  <ul class="footer-item-content">
                    @foreach($footerMenus['catalogs'] as $item)
                      <li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>
                    @endforeach
                  </ul>
                </div>
              </div>
              <div class="col-12 col-md-3 footer-item">
                <div class="footer-links">
                  <div class="footer-link-title">
                    <span>{{ __('front/common.pages') }}</span>
                    <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
                  </div>
                  <ul class="footer-item-content">
                    @foreach($footerMenus['pages'] as $item)
                      <li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>
                    @endforeach
                  </ul>
                </div>
              </div>
              <div class="col-12 col-md-3 footer-item">
                <div class="footer-links">
                  <div class="footer-link-title">
                    <span>{{ __('front/common.specials') }}</span>
                    <div class="footer-link-icon"><i class="bi bi-plus-lg"></i></div>
                  </div>
                  <ul class="footer-item-content">
                    @foreach($footerMenus['specials'] as $item)
                      <li><a href="{{ $item['url'] }}">{{ $item['name'] }}</a></li>
                    @endforeach
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="bottom-box">
        <div class="row">
          <div class="col-md-6">
            <div class="left-links">
              @php $brandLinkHtml = niceshoply_brand_link(); @endphp
              {!! $brandLinkHtml !!}
              @if($brandLinkHtml !== '')
                <a href="{{ front_route('legal.open_source') }}" class="ms-2 small text-muted">{{ __('common/powered_by.license_page') }}</a>
              @endif
              <span class="copyright-text">
                <a href="{{ front_route('home.index') }}" class="ms-2" target="_blank">{{ config('app.name') }}</a>
                &copy; {{ date('Y') }} All Rights Reserved
                @if(system_setting('icp_number', ''))
                  <a href="https://beian.miit.gov.cn" class="ms-2" target="_blank">{{ system_setting('icp_number', '') }}</a>
                @endif
              </span>
            </div>
          </div>
          <div class="col-md-6">
            <div class="payment-icon">
              <img src="{{ asset('images/demo/payment/1.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/2.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/3.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/4.png') }}" class="img-fluid">
              <img src="{{ asset('images/demo/payment/5.png') }}" class="img-fluid">
            </div>
          </div>
      </div>
      </div>
    </div>
  </div>
</footer>

@hookinsert('layout.footer.bottom')

@if (system_setting('js_code', ''))
  {!! system_setting('js_code', '') !!}
@endif