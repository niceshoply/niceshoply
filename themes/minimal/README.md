# Minimal — NiceShoply 极简主题

极简主义电商主题。纯白留白、黑白克制配色、发丝级线条与现代无衬线字体（Inter），去除一切多余装饰，让商品本身成为视觉主角。适合服饰、家居、数码、设计师品牌等追求质感与高级感的店铺。

主题基于 NiceShoply 的 `{nice:xxx}` 声明式标签系统编写（参见 [`docs/主题开发文档/theme-development-guide.html`](../../docs/主题开发文档/theme-development-guide.html)），视图采用沙盒安全的 `.html` 模板，并按需混合 Blade 原生语法。

## 设计语言

| 维度 | 取值 |
|---|---|
| 主背景 | `#ffffff` 纯白 / `#f6f6f4` 浅灰区块 |
| 文字 | `#141414` 主文 / `#5b5b5b` 次文 / `#9a9a9a` 弱化 |
| 线条 | `#e7e7e3` 发丝线，强调线为纯黑 |
| 字体 | Inter（Google Fonts），大量字距（letter-spacing）营造高级感 |
| 圆角 | `0`（方正），按钮/卡片均无圆角 |
| 交互 | 图片缓慢放大、下划线滑入、滚动渐显等克制微动效 |

所有自定义类统一使用 `.mn-` 前缀，`<body>` 带 `.minimal-theme` 类，便于隔离与覆盖。

## 目录结构

```
minimal/
├── config.json                      # 主题配置（必需）
├── README.md
├── public/
│   ├── css/theme.css                # 主题样式（设计系统核心）
│   ├── js/theme.js                  # 渐进增强脚本（可选，无则不影响功能）
│   └── images/icon.png              # 主题缩略图
└── views/
    ├── home.html                    # 首页
    ├── layouts/app.html             # 主布局
    ├── components/
    │   ├── header.html              # 头部（搜索/语言/账户/收藏/购物车 + 导航 + 移动端抽屉）
    │   └── footer.html              # 底部（订阅 + 链接栏 + 版权）
    ├── shared/
    │   ├── product.html             # 产品卡片
    │   ├── blog.html                # 博客卡片
    │   ├── newsletter.html          # 订阅区块
    │   ├── no-data.html             # 空状态
    │   ├── articles.html            # 文章列表 + 侧栏
    │   ├── filter_sidebar.html      # 分类页筛选侧栏（分类/价格/品牌/属性/库存）
    │   └── review.html              # 评价表单
    ├── products/
    │   ├── show.html                # 产品详情页
    │   └── components/
    │       ├── _variants.html       # 规格切换
    │       ├── _options.html        # 自定义选项（下拉/单选/多选）
    │       ├── _video.html          # 产品视频
    │       ├── _review_section.html # 评价区
    │       └── _review_list.html    # 评价列表
    ├── categories/
    │   ├── show.html                # 分类列表页（含 hero/筛选/分页/描述）
    │   └── partials/
    │       ├── _subcategories.html  # 子分类
    │       └── _description.html    # 分类底部描述
    ├── articles/
    │   ├── index.html               # 文章首页
    │   └── show.html                # 文章详情
    └── pages/
        └── show.html                # 单页详情
```

## 安装

主题开发于本仓库 `niceshoply_theme/`，部署时复制到主程序 `themes/` 目录：

```bash
# 将主题部署到主程序
cp -r niceshoply_theme/minimal niceshoply/themes/minimal
```

随后进入后台 **系统设置 → 模板/主题** 选择「极简 Minimal」并启用即可。启用时系统会自动将 `public/` 下的资源发布到 `public/static/themes/minimal/`（与 `lilysilk` 主题一致）。

> `theme_css('theme')` 会优先加载 `public/static/themes/minimal/css/theme.css`；`theme_js('theme')` 同理加载 `theme.js`（不存在时自动跳过，不影响功能）。

## 依赖与对接点

- **复用主程序公共组件**：`<x-front-header />`、`<x-front-footer />`、`<x-front-mini-cart />`、`<x-front-review />` 与购物车/收藏/登录等前端逻辑（`inno.*`、`layer`、`axios`、jQuery、Bootstrap、Swiper）。
- **插件钩子**：完整保留首页、头尾、产品/分类/文章/单页的 `{nice:hook ...}` 与 `@hookupdate` 扩展点，促销/统计/客服等插件可正常注入。
- **数据来源**：所有页面变量（`$slideshow`、`$tab_products`、`$hot_products`、`$news`、`$product`、`$category`、`$products`、`$article`、`$page` 等）均由核心控制器注入，契约与默认主题/`lilysilk` 完全一致。

## 自定义建议

- 调整配色/字体/圆角：直接修改 `public/css/theme.css` 顶部 `:root` 变量。
- 首页区块顺序与显隐：编辑 `views/home.html`，各区块均以 `{nice:if ...}` 包裹，安全增删。
- 想换字体：替换 `layouts/app.html` 中的 Google Fonts 链接并更新 `--mn-font`。
