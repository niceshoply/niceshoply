# 官网 Legal 静态页部署说明

将本目录静态文件部署到 **niceshoply.com** 的 `/legal/` 路径（或根路径重定向）。

## 文件

| 文件 | 建议 URL |
|------|----------|
| `index.html` | https://www.niceshoply.com/legal/ |
| `open-source.html` | https://www.niceshoply.com/legal/open-source.html |
| `commercial.html` | https://www.niceshoply.com/legal/commercial.html |

## Nginx 示例

```nginx
location /legal/ {
    alias /var/www/niceshoply-legal/;
    index index.html;
}
```

## 应用市场（marketplace.niceshoply.com）

动态 Legal 路由（Laravel）：

- `/legal` — 总览
- `/legal/open-source` — MIT
- `/legal/commercial` — 商业授权
- `/legal/plugin-eula` — 插件 EULA
- `/legal/trademark` — 商标政策

## 商业授权商品种子

```bash
cd niceshoply_marketplace
php artisan db:seed --class=CommercialLicenseProductSeeder
```

或在全量种子时自动执行（已加入 `DatabaseSeeder`）。

商品 code 与主程序 `BrandLicenseService` / `check-entitlement` API 对齐。
