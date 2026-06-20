# NiceShoply

> 开源、自托管、可二次开发的电商建站系统。  
> 官网：[https://niceshoply.com](https://niceshoply.com)

NiceShoply 是一套基于 Laravel 的开源电商系统，面向希望拥有完整源码、独立部署能力和深度定制空间的商家、开发者与服务商。与纯 SaaS 平台不同，NiceShoply 的核心代码采用 MIT 许可开放，你可以下载、修改、私有化部署，并将数据掌握在自己的服务器中。

## 核心亮点

- **完整源码开放**：核心系统采用 MIT License，支持商用、修改、二次开发和自托管。
- **自托管优先**：数据库、商品、订单、客户与文件资产归你所有，不被平台锁定。
- **零 GMV 抽成**：自托管使用核心系统无需按交易额向官方付费。
- **现代 Laravel 架构**：基于 Laravel 12，配套后台控制台、前台主题、REST API、队列、搜索、任务调度等能力。
- **插件化扩展**：通过插件机制扩展支付、物流、营销、AI、数据分析等功能。
- **主题系统**：基于 Laravel Blade 的主题体系，支持文件级覆盖、自动回退、多主题共存。
- **多语言与多币种**：适合跨境电商、本地化店铺和多市场运营场景。
- **商业友好**：开源核心免费，可选 NiceShoply Cloud、应用市场插件/主题、去版权/白标/企业授权。

## 适合谁使用

- 想要摆脱平台锁定、掌握源码和数据的独立商家。
- 需要为客户交付电商项目的建站公司、外包团队和数字代理商。
- 希望基于 Laravel 快速二次开发商城系统的开发者。
- 需要私有化部署、定制流程、对接内部系统的企业团队。

## 功能概览

NiceShoply 核心系统覆盖电商建站的主要场景：

- 商品、分类、库存、SKU、图片与内容管理
- 购物车、结账、订单、客户与会员体系
- 后台控制台与运营管理
- CMS 页面、评价、税务、邮件、日志等基础能力
- 多语言、多币种、本地化配置
- REST API 与前后端扩展入口
- 插件系统、主题系统、Hook 扩展机制
- 基础支付、基础物流以及可扩展的第三方服务接入

仓库中还包含示例/内置插件与主题，用于展示支付、营销、AI 辅助、备份、数据分析等扩展方向。

## 技术栈

- **后端**：PHP 8.2+、Laravel 12
- **前端构建**：Vite、Vue 3、Bootstrap、Element Plus
- **数据库**：MySQL/MariaDB（默认配置为 MySQL）
- **缓存/队列**：Laravel Cache、Redis、Laravel Horizon
- **搜索**：Laravel Scout，可接入 Meilisearch
- **可选服务**：Sentry、AWS/S3、Stripe、PayPal、EasyWeChat、短信服务等

## 快速开始

### 环境要求

- PHP 8.2 或 8.3
- Composer
- Node.js 与 npm
- MySQL/MariaDB
- Redis（推荐，用于队列与缓存）
- 常用 PHP 扩展：bcmath、curl、dom、fileinfo、libxml、openssl、pdo、simplexml、opcache 等

### 本地安装

```bash
git clone <your-repository-url> niceshoply
cd niceshoply

composer install
npm install

cp .env.example .env
php artisan key:generate
```

编辑 `.env`，配置数据库、Redis、邮件、站点 URL 等信息：

```env
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=niceshoply
DB_USERNAME=root
DB_PASSWORD=
```

执行数据库迁移并构建前端资源：

```bash
php artisan migrate
npm run build
```

启动本地开发服务：

```bash
php artisan serve
```

然后访问：

- 前台：`http://localhost:8000`
- 安装/后台入口请根据项目路由与部署配置访问

> 生产环境建议使用 Nginx/Apache 指向 `public/` 目录，并配置队列、计划任务、缓存、HTTPS 与文件权限。

## 常用命令

```bash
# 开发模式构建前端资源
npm run dev

# 生产构建
npm run build

# Laravel 代码风格格式化
composer pint

# 静态分析
composer phpstan

# 插件目录静态分析
composer phpstan:plugins

# NiceShoply 核心目录静态分析
composer phpstan:niceshoply
```

## 目录结构

```text
niceshoply/
├── app/                 # Laravel 应用层代码
├── config/              # Laravel 与 NiceShoply 配置
├── database/            # 数据库迁移、种子与 sqlite 文件
├── niceshoply/          # NiceShoply 核心模块
│   ├── common/          # 通用模型、服务与基础能力
│   ├── console/         # 后台控制台
│   ├── front/           # 前台商城
│   ├── install/         # 安装向导
│   ├── plugin/          # 插件基础设施
│   └── restapi/         # REST API
├── plugins/             # 插件目录
├── themes/              # 主题目录
├── public/              # Web 入口目录
├── routes/              # 应用路由
├── scripts/             # 部署与运维脚本
└── legal/               # 授权与法律文档
```

## 插件与主题

NiceShoply 鼓励通过插件和主题扩展系统能力，而不是直接修改核心代码。

- 插件目录：[plugins/](plugins/)
- 主题目录：[themes/](themes/)
- 主题开发文档：[themes/THEME-GUIDE.md](themes/THEME-GUIDE.md)

主题系统支持：

- 文件级模板覆盖
- 未覆盖模板自动回退
- 多主题共存与后台切换
- Blade 语法与 Hook 扩展

## 授权说明

NiceShoply 采用分层授权模式：

1. **开源核心**：MIT License，永久免费，可商用、可修改、可自托管。
2. **应用市场商品**：付费插件/主题适用独立 EULA。
3. **商业授权**：去版权、白标、服务商交付、企业私有化等场景适用商业合同。
4. **NiceShoply Cloud**：托管云服务是可选 SaaS 服务，不影响自托管用户使用开源核心。

相关文件：

- [LICENSE](LICENSE)
- [TRADEMARK.md](TRADEMARK.md)
- [legal/README.md](legal/README.md)
- [legal/开源核心许可说明.md](legal/开源核心许可说明.md)
- [legal/插件与主题EULA.md](legal/插件与主题EULA.md)
- [legal/商业授权协议.md](legal/商业授权协议.md)

> 注意：MIT 许可覆盖的是开源核心代码，不包含 NiceShoply 名称、Logo、官方域名等商标授权。商标使用边界请阅读 [TRADEMARK.md](TRADEMARK.md)。

## 官网与生态

- 官网：[https://niceshoply.com](https://niceshoply.com)
- 应用市场：[https://marketplace.niceshoply.com](https://marketplace.niceshoply.com)

## 贡献

欢迎开发者通过 Issue、Pull Request、插件、主题、文档改进等方式参与 NiceShoply 生态建设。

在提交代码前，建议运行：

```bash
composer pint
composer phpstan
npm run build
```

请尽量保持代码风格与现有项目一致，并为重要变更补充必要说明。

## 安全与问题反馈

如果你发现安全问题，请不要在公开 Issue 中披露可利用细节。请通过官网 [https://niceshoply.com](https://niceshoply.com) 联系官方团队。

普通 Bug、功能建议、文档问题可以通过仓库 Issue 或 Pull Request 反馈。

## License

NiceShoply 开源核心基于 [MIT License](LICENSE) 发布。

Copyright © 2024-present NiceShoply.
