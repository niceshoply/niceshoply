# NiceShoply 主题开发与使用手册

> 版本：v1.0.0 · 更新日期：2026-05-30  
> 适用系统：NiceShoply · 基础框架：Laravel 12 + Blade

---

## 目录

1. [主题系统概览](#1-主题系统概览)
2. [目录结构说明](#2-目录结构说明)
3. [快速开始：发布默认主题](#3-快速开始发布默认主题)
4. [创建新主题](#4-创建新主题)
5. [模板文件说明](#5-模板文件说明)
6. [可用变量与辅助函数](#6-可用变量与辅助函数)
7. [Blade 组件参考](#7-blade-组件参考)
8. [{nice:xxx} 自定义标签](#8-nicexxx-自定义标签)
9. [插件钩子](#9-插件钩子)
10. [样式与脚本](#10-样式与脚本)
11. [主题查找优先级机制](#11-主题查找优先级机制)
12. [常见问题与排查](#12-常见问题与排查)

---

## 1. 主题系统概览

NiceShoply 主题系统基于 Laravel 的 `FileViewFinder` 视图查找器构建，支持**按主题隔离模板文件**。

### 核心特性

| 特性 | 说明 |
|------|------|
| 文件级覆盖 | 只需在主题目录放置同名文件即可覆盖内置模板，无需修改系统核心 |
| 自动回退 | 主题中未覆盖的文件自动使用内置默认模板，无需复制全部文件 |
| 多主题共存 | `themes/` 目录下可存放多个主题，后台切换即生效 |
| 插件钩子 | 主题模板可通过 `@hookinsert()` 与插件交互 |
| Blade 全兼容 | 主题模板使用标准 Laravel Blade 语法，`.blade.php` 或 `.html` 均可 |

### 工作流程

```
后台设置当前主题 (theme = 'default')
        ↓
前台请求页面 nice_view('home')
        ↓
FileViewFinder 按优先级查找:
  1. themes/default/views/home.blade.php   ← 优先使用（自定义）
  2. niceshoply/front/resources/views/home.blade.php  ← 回退（内置原始）
  3. resources/views/home.blade.php        ← 最低（Laravel 应用层）
        ↓
渲染并输出 HTML
```

---

## 2. 目录结构说明

### themes/ 目录总览

```
themes/
├── THEME-GUIDE.md          ← 本手册
├── default/                ← 默认主题（通过 inno:publish-theme 生成）
│   ├── config.json         ← 主题元数据
│   ├── views/              ← Blade 模板
│   ├── css/                ← SCSS 样式源文件
│   ├── js/                 ← JavaScript 脚本文件
│   └── public/             ← 静态资源（图片等）
└── my-theme/               ← 自定义主题（示例）
    ├── config.json
    └── views/
        └── home.blade.php  ← 只覆盖需要修改的文件即可
```

### 主题内部目录结构

```
themes/default/
├── config.json                         主题元数据（必须）
├── public/
│   └── images/
│       └── preview.jpg                 主题预览图（后台展示用）
├── views/
│   ├── layouts/
│   │   ├── app.blade.php               ★ 全局主布局（所有页面继承）
│   │   └── mail.blade.php              邮件布局
│   ├── components/                     全局 UI 组件
│   │   ├── header.blade.php            页头导航
│   │   ├── footer.blade.php            页脚
│   │   ├── mini-cart.blade.php         迷你购物车浮层
│   │   ├── breadcrumb.blade.php        面包屑导航
│   │   ├── review.blade.php            评论组件
│   │   └── nice/                       {nice:xxx} 组件模板
│   │       ├── slideshow.blade.php     轮播图
│   │       ├── products.blade.php      商品列表块
│   │       ├── hot-products.blade.php  热门商品块
│   │       ├── categories.blade.php    分类块
│   │       ├── articles.blade.php      文章块
│   │       └── pages.blade.php         页面块
│   ├── shared/                         公共局部模板（@include 引入）
│   │   ├── product.blade.php           商品卡片（列表页商品项）
│   │   ├── blog.blade.php              博客文章卡片
│   │   ├── articles.blade.php          文章列表
│   │   ├── filter_sidebar.blade.php    筛选侧边栏
│   │   ├── page-head.blade.php         页面大标题区
│   │   ├── account-sidebar.blade.php   会员中心侧边菜单
│   │   ├── address-form.blade.php      地址表单
│   │   ├── no-data.blade.php           无数据占位提示
│   │   └── review.blade.php            评论列表
│   ├── home.blade.php                  首页
│   ├── maintenance.blade.php           维护模式页
│   ├── debug.blade.php                 调试信息页
│   ├── products/
│   │   ├── index.blade.php             商品列表页
│   │   ├── show.blade.php              商品详情页
│   │   └── components/                 商品详情子组件
│   │       ├── _images.blade.php       商品图片画廊
│   │       ├── _options.blade.php      商品选项（颜色/尺码等）
│   │       ├── _variants.blade.php     商品变体选择
│   │       ├── _bundle_items.blade.php 捆绑销售商品项
│   │       ├── _video.blade.php        商品视频
│   │       ├── _review_section.blade.php 评价区（含评分统计）
│   │       └── _review_list.blade.php  评价列表
│   ├── categories/
│   │   ├── index.blade.php             全部分类页
│   │   ├── show.blade.php              分类商品列表页
│   │   └── partials/                   分类页子组件
│   ├── brands/
│   │   ├── index.blade.php             品牌列表页
│   │   ├── show.blade.php              品牌商品列表页
│   │   └── partials/
│   ├── cart/
│   │   └── index.blade.php             购物车页
│   ├── checkout/
│   │   ├── index.blade.php             结算页
│   │   └── success.blade.php           下单成功页
│   ├── orders/
│   │   ├── show.blade.php              订单详情页（访客）
│   │   └── pay.blade.php               订单支付页
│   ├── payment/
│   │   ├── success.blade.php           支付成功页
│   │   ├── cancel.blade.php            支付取消页
│   │   └── fail.blade.php              支付失败页
│   ├── account/                        会员中心
│   │   ├── login.blade.php             登录页
│   │   ├── register.blade.php          注册页
│   │   ├── forgotten.blade.php         忘记密码页
│   │   ├── home.blade.php              会员首页
│   │   ├── edit.blade.php              编辑资料
│   │   ├── password.blade.php          修改密码
│   │   ├── addresses.blade.php         地址管理
│   │   ├── favorites.blade.php         收藏夹
│   │   ├── order_index.blade.php       我的订单列表
│   │   ├── order_info.blade.php        订单详情
│   │   ├── order_return_*.blade.php    退货申请相关
│   │   ├── reviews_index.blade.php     我的评价
│   │   ├── transactions_index.blade.php 交易记录
│   │   ├── wallet_index.blade.php      钱包余额
│   │   ├── withdrawals_*.blade.php     提现相关
│   │   ├── _social.blade.php           第三方登录按钮
│   │   └── social_callback.blade.php   第三方登录回调页
│   ├── articles/
│   │   ├── index.blade.php             文章列表页
│   │   ├── show.blade.php              文章详情页
│   │   └── partials/
│   ├── pages/
│   │   ├── show.blade.php              CMS 页面（关于我们等）
│   │   └── _sample_*.blade.php         CMS 页面内容示例模板
│   ├── tags/
│   │   └── show.blade.php              标签商品页
│   ├── catalogs/
│   │   └── show.blade.php              目录页
│   ├── mails/                          邮件模板
│   │   ├── forgotten.blade.php         找回密码邮件
│   │   ├── registration.blade.php      注册欢迎邮件
│   │   ├── order_new.blade.php         新订单通知邮件
│   │   └── order_update.blade.php      订单状态更新邮件
│   └── errors/
│       ├── 404.blade.php               404 页面
│       └── 503.blade.php               503/维护页面
├── css/                                SCSS 样式文件
│   ├── app.scss                        ★ 入口文件（@import 其他 scss）
│   ├── _variables.scss                 全局变量（颜色/字体/间距）
│   ├── global.scss                     全局基础样式
│   ├── header.scss                     页头样式
│   ├── footer.scss                     页脚样式
│   ├── home.scss                       首页样式
│   ├── page-product.scss               商品详情页样式
│   ├── page-cart.scss                  购物车页样式
│   ├── page-checkout.scss              结算页样式
│   ├── page-account.scss               会员中心样式
│   └── ...                             其他页面样式
└── js/
    ├── app.js                          ★ 入口文件
    ├── bootstrap.js                    基础库初始化（axios/jQuery 等）
    ├── common.js                       公共工具函数
    ├── header.js                       页头交互（搜索/菜单/语言切换等）
    ├── footer.js                       页脚交互
    ├── autocomplete.js                 搜索自动补全
    └── bootstrap-validation.js        表单验证
```

---

## 3. 快速开始：发布默认主题

### 前提条件

- PHP 8.2+
- Composer 依赖已安装（`vendor/` 目录存在）

### 操作步骤

**第一步：执行发布命令**

```bash
cd /path/to/niceshoply
php artisan inno:publish-theme
```

等同于：

```bash
php artisan vendor:publish \
  --provider='NiceShoply\Front\FrontServiceProvider' \
  --tag=views
```

**第二步：确认发布结果**

命令成功后会输出：

```
INFO  Publishing [views] assets.
Copying directory [niceshoply/front/resources] to [themes/default] .... DONE
```

此时 `themes/default/` 目录即为默认主题的完整可编辑副本，共约 **130 个文件**。

**第三步：在后台启用主题**

登录后台 → 外观设置 → 主题管理 → 选择 `default` → 保存。

也可直接在系统设置中将 `theme` 字段设为 `default`。

**第四步：开始自定义**

直接修改 `themes/default/views/` 下的 Blade 模板文件即可，修改立即生效（无需重启）。

> **提示**：若某个模板文件被删除，系统会自动回退到内置原始模板，无需担心删错。

---

## 4. 创建新主题

### 4.1 从零创建

**第一步：新建主题目录和配置文件**

```bash
mkdir -p themes/my-theme/views
```

创建 `themes/my-theme/config.json`：

```json
{
    "code": "my-theme",
    "name": {
        "zh-cn": "我的主题",
        "en": "My Theme"
    },
    "description": {
        "zh-cn": "一个自定义主题",
        "en": "A custom theme"
    },
    "version": "v1.0.0",
    "icon": "images/icon.png",
    "author": {
        "name": "Your Name",
        "email": "you@example.com"
    }
}
```

> `code` 字段必须与目录名一致，且只能包含小写字母、数字和连字符。

**第二步：只覆盖需要修改的模板**

无需复制全部文件，只需在 `themes/my-theme/views/` 下放置需要修改的文件：

```
themes/my-theme/
├── config.json
└── views/
    ├── home.blade.php         ← 覆盖首页
    └── layouts/
        └── app.blade.php      ← 覆盖主布局
```

其余页面会自动回退到内置默认模板。

**第三步：激活主题**

后台 → 外观设置 → 主题管理 → 选择 `my-theme`。

或修改系统设置，将 `theme` 字段设为 `my-theme`。

### 4.2 基于 default 主题修改

推荐方式：先发布默认主题，再基于它修改。

```bash
# 1. 将 default 目录复制为新主题目录
cp -r themes/default themes/my-theme

# 2. 修改 config.json 中的 code 字段
# 将 "code": "default" 改为 "code": "my-theme"

# 3. 修改模板文件
# 4. 后台激活
```

---

## 5. 模板文件说明

### 5.1 创建页面模板的基本结构

所有前台页面模板均继承主布局 `layouts.app`：

```blade
{{-- 继承主布局 --}}
@extends('layouts.app')

{{-- 覆盖页面标题 --}}
@section('title', '商品列表 - ' . system_setting_locale('meta_title'))

{{-- 为 body 添加页面标识 CSS 类 --}}
@section('body-class', 'page-products')

{{-- 主体内容 --}}
@section('content')
<div class="container">
    <h1>商品列表</h1>
    {{-- 页面内容 --}}
</div>
@endsection

{{-- 注入页面专属 CSS（在 </head> 前） --}}
@push('header')
<link rel="stylesheet" href="{{ theme_asset('css/page-products.css') }}">
@endpush

{{-- 注入页面专属 JS（在 </body> 前） --}}
@push('footer')
<script src="{{ theme_asset('js/products.js') }}"></script>
@endpush
```

### 5.2 layouts/app.blade.php — 主布局

主布局是所有页面的基础骨架，提供以下插槽（Sections）：

| Section 名 | 类型 | 说明 |
|-----------|------|------|
| `title` | yield | 页面 `<title>` |
| `description` | yield | `<meta description>` |
| `keywords` | yield | `<meta keywords>` |
| `body-class` | yield | `<body>` 额外 CSS 类 |
| `content` | yield | **页面主体内容（必填）** |
| `header` | stack | 在 `</head>` 前注入（CSS/JS） |
| `footer` | stack | 在 `</body>` 前注入（JS） |

### 5.3 邮件布局 layouts/mail.blade.php

邮件模板继承 `layouts.mail`：

```blade
@extends('layouts.mail')

@section('content')
<p>您好，{{ $customer->name }}</p>
<p>您的订单已确认。</p>
@endsection
```

### 5.4 模板与路由对应关系

| 模板文件 | 路由名称 | URL 示例 |
|---------|---------|---------|
| `home.blade.php` | `front.home.index` | `/en` |
| `products/index.blade.php` | `front.products.index` | `/en/products` |
| `products/show.blade.php` | `front.products.show` | `/en/products/iphone-15` |
| `categories/show.blade.php` | `front.categories.show` | `/en/categories/phones` |
| `brands/index.blade.php` | `front.brands.index` | `/en/brands` |
| `brands/show.blade.php` | `front.brands.show` | `/en/brands/apple` |
| `cart/index.blade.php` | `front.cart.index` | `/en/cart` |
| `checkout/index.blade.php` | `front.checkout.index` | `/en/checkout` |
| `checkout/success.blade.php` | `front.checkout.success` | `/en/checkout/success` |
| `account/login.blade.php` | `front.account.login` | `/en/account/login` |
| `account/register.blade.php` | `front.account.register` | `/en/account/register` |
| `account/home.blade.php` | `front.account.home` | `/en/account` |
| `account/order_index.blade.php` | `front.account.orders` | `/en/account/orders` |
| `articles/index.blade.php` | `front.articles.index` | `/en/articles` |
| `articles/show.blade.php` | `front.articles.show` | `/en/articles/slug` |
| `pages/show.blade.php` | `front.pages.show` | `/en/pages/about-us` |
| `tags/show.blade.php` | `front.tags.show` | `/en/tags/sale` |
| `errors/404.blade.php` | — | 404 错误时自动触发 |

---

## 6. 可用变量与辅助函数

### 6.1 全局辅助函数（所有模板可用）

#### 系统设置

```php
system_setting('key')           // 读取系统设置（语言无关）
system_setting_locale('key')    // 读取当前语言的系统设置（如 meta_title）
```

常用 key 值：

| key | 说明 |
|-----|------|
| `front_logo` | 前台 Logo 路径 |
| `favicon` | 网站图标路径 |
| `meta_title` | 网站默认标题 |
| `meta_description` | 默认 meta 描述 |
| `meta_keywords` | 默认 meta 关键词 |
| `theme` | 当前主题 code |

#### 路由生成

```php
front_route('name', $params)        // 生成带语言前缀的前台路由 URL
account_route('name', $params)      // 生成带语言前缀的会员中心路由 URL
front_root_route('name', $params)   // 生成根路径路由（不带语言前缀）
```

示例：

```blade
<a href="{{ front_route('front.products.show', ['slug' => $product->slug]) }}">
    {{ $product->name }}
</a>
```

#### 图片处理

```php
image_origin($path)                // 将相对路径转为完整图片 URL
image_resize($path, $w, $h)       // 生成指定尺寸的缩略图 URL
```

示例：

```blade
<img src="{{ image_origin($product->image) }}" alt="{{ $product->name }}">
<img src="{{ image_resize($product->image, 400, 400) }}" alt="{{ $product->name }}">
```

#### 价格格式化

```php
currency_format($amount)           // 按当前货币格式化金额，如 "$99.99"
```

#### 当前用户与货币

```php
current_customer()                 // 当前登录会员对象（未登录返回空对象）
current_currency()                 // 当前货币对象
current_currency_code()            // 当前货币代码字符串，如 'USD'
```

#### 语言与方向

```php
front_locale_code()                // 当前前台语言代码，如 'en'、'zh-CN'
front_locale_direction()           // 文字方向，'ltr' 或 'rtl'
```

#### 其他

```php
nice_view($view, $data)            // 渲染视图（触发 ViewHook 插件钩子后调用 view()）
niceshoply_version()               // 系统版本号
build_asset('front/css/app.css')   // 获取 Vite 编译产物 URL（带内容哈希缓存失效）
theme_asset('css/page.css')        // 获取当前主题资源 URL
csrf_token()                       // Laravel CSRF Token
```

### 6.2 全局 JavaScript 变量

主布局 `layouts/app.blade.php` 会自动注入以下 JS 全局变量：

```javascript
urls.front_api          // 前台 API 基础地址
urls.front_base         // 前台首页地址
urls.front_upload       // 图片上传接口地址
urls.front_cart_add     // 加入购物车接口地址
urls.front_cart_mini    // 迷你购物车数据接口地址
urls.front_cart         // 购物车页面地址
urls.front_checkout     // 结算页面地址
urls.front_login        // 登录页面地址
urls.front_favorites    // 收藏夹列表页面地址
urls.front_favorite_cancel  // 取消收藏接口地址

config.isLogin          // 当前是否已登录（Boolean）
config.currency.code    // 货币代码，如 'USD'
config.currency.symbol_left   // 货币左侧符号，如 '$'
config.currency.symbol_right  // 货币右侧符号
config.currency.decimal_place // 价格小数位数
config.currency.rate    // 货币汇率

asset_url               // 静态资源根路径
```

### 6.3 各页面可用变量

#### 首页（home.blade.php）

| 变量 | 类型 | 说明 |
|------|------|------|
| `$slideshow` | array | 轮播图数据，每项含 `image`（多语言）和 `link` |
| `$home_categories` | array | 首页分类导航，每项含 `name`、`url`、`image` |
| `$tab_products` | array | 精选商品标签组，每项含 `tab_title` 和 `products` |
| `$hot_products` | array | 热门商品分组，每项含 `category_name` 和 `products` |
| `$news` | array | 博客/资讯文章数组 |

#### 商品详情页（products/show.blade.php）

| 变量 | 类型 | 说明 |
|------|------|------|
| `$product` | object | 商品主对象 |
| `$product->name` | string | 商品名称 |
| `$product->description` | string | 商品描述（HTML） |
| `$product->price` | float | 商品价格 |
| `$product->original_price` | float | 原价（用于显示划线价） |
| `$product->images` | array | 商品图片数组 |
| `$product->options` | array | 商品选项（颜色/尺码等） |
| `$product->variants` | array | 商品变体数组 |
| `$product->in_stock` | bool | 是否有库存 |
| `$product->reviews_count` | int | 评价数量 |
| `$product->reviews_avg` | float | 平均评分 |
| `$related_products` | array | 相关商品数组 |

#### 商品列表页（products/index.blade.php）

| 变量 | 类型 | 说明 |
|------|------|------|
| `$products` | Paginator | 商品分页对象 |
| `$filters` | array | 当前筛选条件 |
| `$sort` | string | 当前排序方式 |

#### 分类页（categories/show.blade.php）

| 变量 | 类型 | 说明 |
|------|------|------|
| `$category` | object | 分类对象（含 name、description、image 等） |
| `$products` | Paginator | 分类商品分页对象 |
| `$subcategories` | array | 子分类数组 |

#### 购物车页（cart/index.blade.php）

| 变量 | 类型 | 说明 |
|------|------|------|
| `$cart_items` | array | 购物车商品项数组 |
| `$cart_total` | float | 购物车总金额 |

#### 会员中心（account/）

| 变量 | 类型 | 说明 |
|------|------|------|
| `$customer` | object | 当前登录会员对象 |
| `$customer->name` | string | 会员姓名 |
| `$customer->email` | string | 邮箱 |
| `$customer->avatar` | string | 头像路径 |

---

## 7. Blade 组件参考

### 7.1 内置 Blade 组件

#### `<x-front-header />`

渲染页头导航（对应 `components/header.blade.php`）。  
由主布局自动引入，一般不需要在子模板中手动调用。

插件可通过 `front.header.component.class` Hook 替换整个组件类：

```php
// 插件中替换页头组件
fire_hook_filter('front.header.component.class', MyCustomHeader::class);
```

#### `<x-front-footer />`

渲染页脚（对应 `components/footer.blade.php`）。

#### `<x-front-breadcrumb :items="$items" />`

渲染面包屑导航。

```blade
<x-front-breadcrumb :items="[
    ['name' => '首页', 'url' => front_route('front.home.index')],
    ['name' => '商品', 'url' => front_route('front.products.index')],
    ['name' => $product->name],
]" />
```

#### `<x-front-review :product="$product" />`

渲染商品评分摘要组件（星级 + 评价数量）。

### 7.2 Nice 系列组件（页面构建器使用）

以下组件对应 CMS 页面构建器中的内容块：

| 标签 | 模板文件 | 说明 |
|------|---------|------|
| `<x-nice-slideshow />` | `components/nice/slideshow.blade.php` | 轮播图 |
| `<x-nice-products />` | `components/nice/products.blade.php` | 商品列表块 |
| `<x-nice-hot-products />` | `components/nice/hot-products.blade.php` | 热门商品块 |
| `<x-nice-categories />` | `components/nice/categories.blade.php` | 分类展示块 |
| `<x-nice-articles />` | `components/nice/articles.blade.php` | 文章/博客块 |
| `<x-nice-pages />` | `components/nice/pages.blade.php` | CMS 页面块 |

---

## 8. {nice:xxx} 自定义标签

NiceShoply 提供 `{nice:xxx}` 自定义标签语法，作为 Blade 组件的简写形式，主要用于 CMS 页面编辑器中。

系统会在 Blade 编译前将 `{nice:xxx}` 预处理为标准的 `<x-nice-xxx />` 组件。

```blade
{{-- 在模板中使用 {nice:xxx} 标签 --}}
{nice:slideshow}
{nice:products limit="8" category="electronics"}
{nice:hot-products}
{nice:categories}
{nice:articles limit="3"}
```

等同于：

```blade
<x-nice-slideshow />
<x-nice-products limit="8" category="electronics" />
<x-nice-hot-products />
<x-nice-categories />
<x-nice-articles limit="3" />
```

---

## 9. 插件钩子

主题模板可通过 `@hookinsert()` 在指定位置插入插件内容。

### 9.1 内置钩子位置

| 钩子名称 | 位置 | 说明 |
|---------|------|------|
| `front.head.end` | `</head>` 前 | 注入 CSS/JS |
| `front.body.start` | `<body>` 后 | 注入追踪代码等 |
| `front.body.end` | `</body>` 前 | 注入 JS 脚本 |
| `front.header.start` | 页头开始 | 页头顶部内容 |
| `front.header.end` | 页头结束 | 页头底部内容 |
| `front.footer.start` | 页脚开始 | 页脚顶部内容 |
| `front.footer.end` | 页脚结束 | 页脚底部内容 |
| `front.product.tab.start` | 商品详情页 Tab 区域前 | 插入自定义 Tab |
| `front.product.info.end` | 商品信息区域后 | 插入额外信息 |
| `front.checkout.form.end` | 结算表单底部 | 插入额外字段/说明 |
| `front.cart.summary.end` | 购物车汇总区域后 | 插入优惠码等 |
| `front.account.sidebar.end` | 会员中心侧边栏底部 | 插入自定义菜单项 |

### 9.2 在自定义模板中使用钩子

```blade
{{-- 在模板的合适位置插入钩子 --}}
@hookinsert('front.product.info.end')

{{-- 带参数的钩子 --}}
@hookinsert('front.product.tab.start', ['product' => $product])
```

### 9.3 在插件中监听钩子

```php
// 插件 Boot.php 中
add_hook_action('front.checkout.form.end', function ($data) {
    echo view('my-plugin::checkout-extra-field')->render();
});
```

---

## 10. 样式与脚本

### 10.1 SCSS 文件说明

| 文件 | 说明 |
|------|------|
| `css/app.scss` | **入口文件**，通过 `@import` 引入其他 scss |
| `css/_variables.scss` | **全局变量**（颜色、字体、间距），修改此文件即可调整全局风格 |
| `css/global.scss` | 全局基础样式（reset、通用工具类等） |
| `css/header.scss` | 页头样式 |
| `css/footer.scss` | 页脚样式 |
| `css/home.scss` | 首页专属样式 |
| `css/page-product.scss` | 商品详情页样式 |
| `css/page-cart.scss` | 购物车页样式 |
| `css/page-checkout.scss` | 结算页样式 |
| `css/page-account.scss` | 会员中心样式 |
| `css/product-item.scss` | 商品卡片样式（商品列表中单个商品项） |
| `css/login.scss` | 登录/注册页样式 |

### 10.2 修改样式的推荐流程

1. 优先修改 `css/_variables.scss` 中的变量，统一调整颜色/字体
2. 修改对应页面的 scss 文件
3. 编译 SCSS：`npm run build` 或 `npm run dev`（热更新）

### 10.3 JavaScript 文件说明

| 文件 | 说明 |
|------|------|
| `js/app.js` | **入口文件**，导入其他 JS 模块 |
| `js/bootstrap.js` | 初始化基础库（axios 全局配置、CSRF Token 注入等） |
| `js/common.js` | 公共工具函数（加入购物车、收藏切换、货币格式化等） |
| `js/header.js` | 页头交互（搜索栏、移动端菜单、货币/语言切换等） |
| `js/footer.js` | 页脚交互（Back to top 等） |
| `js/autocomplete.js` | 搜索框自动补全功能 |
| `js/bootstrap-validation.js` | Bootstrap 表单校验扩展 |

### 10.4 编译资产

```bash
# 安装 Node 依赖
npm install

# 开发模式（热更新）
npm run dev

# 生产构建
npm run build
```

---

## 11. 主题查找优先级机制

这是理解主题系统的核心。当调用 `nice_view('home')` 时，系统按以下顺序查找模板文件：

```
优先级 1（最高）：用户自定义主题
  路径：themes/{theme_code}/views/
  条件：后台设置了主题，且该目录存在

优先级 2（回退）：内置原始默认模板
  路径：niceshoply/front/resources/views/
  条件：始终存在，任何视图名都能在此找到

优先级 3（最低）：Laravel 应用层
  路径：resources/views/
  说明：主要供 console 后台等应用级视图使用，前台通常不命中
```

### 实现原理

`FrontServiceProvider` 在启动时通过覆盖 `view.finder` 单例来实现：

```php
// FrontServiceProvider::loadThemeViewPath()
$this->app->singleton('view.finder', function ($app) {
    $themePaths = [];

    // 1. 自定义主题路径（如果存在）
    if ($theme = system_setting('theme')) {
        $themeViewPath = base_path("themes/{$theme}/views");
        if (is_dir($themeViewPath)) {
            $themePaths[] = $themeViewPath;
        }
    }

    // 2. 内置原始默认模板（始终存在）
    $themePaths[] = realpath(__DIR__.'/../resources/views');

    // 3. Laravel 应用层视图路径（来自 config/view.php）
    $viewPaths = array_merge($themePaths, $app['config']['view.paths']);

    return new FileViewFinder($app['files'], $viewPaths);
});
```

### 查找示例

| 场景 | 结果 |
|------|------|
| 主题为 `default`，`themes/default/views/home.blade.php` 存在 | 使用主题文件 |
| 主题为 `default`，`themes/default/views/special.blade.php` 不存在 | 回退到内置原始模板 |
| 未设置主题 | 直接使用内置原始模板 |
| 内置原始模板也不存在 | 报错（正常情况下不会发生） |

---

## 12. 常见问题与排查

### Q1：执行 `php artisan inno:publish-theme` 提示 "No publishable resources for tag [views]"

**原因**：系统未安装（缺少 `install.lock` 文件），`FrontServiceProvider::boot()` 提前退出。

**解决方案**：
- 确保系统已完成安装，`storage/install.lock` 文件存在
- 或检查 `FrontServiceProvider::boot()` 方法，确认 `publishViewTemplates()` 在安装锁检查之前被调用

### Q2：修改了主题模板文件，但页面没有更新

**可能原因**：
1. Laravel 视图缓存未清除

```bash
php artisan view:clear
```

2. 后台未激活该主题（检查系统设置中 `theme` 字段）

3. 修改的文件路径不对（注意是 `themes/{code}/views/`，不是 `themes/{code}/`）

### Q3：主题切换后部分页面样式丢失

**原因**：该页面对应的 SCSS 文件未在新主题中编译。

**解决**：重新执行 `npm run build` 编译资产文件。

### Q4：想在主题中使用插件提供的数据，如何实现？

通过插件钩子传递数据：

```php
// 插件中
add_hook_filter('front.view.data', function ($data, $view) {
    if ($view === 'home') {
        $data['my_plugin_data'] = MyPlugin::getData();
    }
    return $data;
});
```

然后在主题模板中直接使用 `$my_plugin_data`。

### Q5：如何在主题中覆盖组件模板（如 header）？

在主题的 `views/components/` 目录下放置同名文件即可：

```
themes/my-theme/views/components/header.blade.php
```

文件查找遵循相同的优先级机制，主题中的文件会覆盖内置组件模板。

### Q6：`composer install` 时提示 PHP 版本不满足要求

系统当前支持 PHP 8.2 ~ 8.4。若使用 PHP 8.5+ 可加 `--ignore-platform-reqs` 参数：

```bash
php composer.phar install --prefer-dist --ignore-platform-reqs --no-dev
```

注意：`laravel/pint`（代码格式化工具，仅开发依赖）可能因网络超时失败，不影响主应用运行，可通过 `--no-dev` 跳过。

---

## 附录：发布命令执行记录

以下是本项目首次执行 `php artisan inno:publish-theme` 的完整操作记录，供参考：

### 环境信息

- 执行时间：2026-05-29
- PHP 版本：8.5.3
- 系统：macOS darwin 22.6.0

### 问题与解决过程

**问题 1：`vendor/` 目录不存在**

```
PHP Fatal error: Uncaught Error: Failed opening required 'vendor/autoload.php'
```

原因：首次使用，尚未安装 Composer 依赖。

解决：

```bash
php composer.phar install --prefer-dist --no-interaction --ignore-platform-reqs --no-dev
```

**问题 2：`composer install` 提示 PHP 版本不满足**

```
openspout/openspout v4.28.5 requires php ~8.2.0 || ~8.3.0 || ~8.4.0
your php version (8.5.3) does not satisfy that requirement.
```

解决：添加 `--ignore-platform-reqs` 参数跳过版本检查。

**问题 3：`laravel/pint` git clone 超时**

```
The process 'git clone ... laravel/pint.git' exceeded the timeout of 300 seconds.
```

原因：网络问题，git clone 超慢。`laravel/pint` 是仅开发环境使用的代码格式化工具。

解决：使用 `--no-dev` 跳过开发依赖安装，不影响主应用运行。

**问题 4：`inno:publish-theme` 提示 "No publishable resources for tag [views]"**

```
INFO  No publishable resources for tag [views].
```

原因：`FrontServiceProvider::boot()` 中有安装锁检查（`has_install_lock()`），在未安装状态下提前 `return`，导致 `publishViewTemplates()` 未被调用，发布映射未注册。

解决：修改 `FrontServiceProvider.php`，将 `publishViewTemplates()` 移到安装锁检查之前：

```php
public function boot(): void
{
    // publishViewTemplates 必须在安装锁检查之前执行
    $this->publishViewTemplates();

    if (! has_install_lock()) {
        return;
    }
    // ... 其余初始化逻辑
}
```

### 最终执行结果

```bash
$ php artisan inno:publish-theme

INFO  Publishing [views] assets.
Copying directory [niceshoply/front/resources] to [themes/default] .... DONE
```

发布成功，`themes/default/` 目录下共生成 **130 个文件**：

| 类型 | 数量 | 说明 |
|------|------|------|
| Blade 模板（`.blade.php`） | ~90 个 | 所有前台页面模板 |
| SCSS 样式文件 | 31 个 | 含全局变量、各页面样式 |
| JavaScript 文件 | 7 个 | 含入口文件和功能模块 |
| 静态资源 | 1 个 | 主题预览图 |
| 配置文件 | 1 个 | `config.json` |

---

*本手册由 NiceShoply 开发团队维护。如有问题请参考 [NiceShoply 官方文档](https://www.niceshoply.com) 或提交 Issue。*
