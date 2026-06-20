# NiceShoply 升级包自动打包

## 本地一键打包

在 `niceshoply/` 目录：

```bash
chmod +x scripts/release-upgrade.sh scripts/build-upgrade-package.sh

# 推荐：自动 composer + npm build + 增量打包（自上一 v* tag）
npm run release:upgrade

# 全量包（含 vendor + public/build）
npm run release:upgrade:full

# 打包并导入本地 marketplace（需 sibling 目录 niceshoply_marketplace）
npm run release:upgrade:upload
```

输出：
- `dist/upgrades/niceshoply-{version}.zip`
- `dist/upgrades/niceshoply-{version}.meta.json`（size / sha256 等）

## GitHub Actions 自动打包

推送版本 tag 即触发：

```bash
git tag v1.7.0
git push origin v1.7.0
```

Workflow：`.github/workflows/release-upgrade.yml`

- 安装依赖 + 构建前端
- 增量打包（相对上一 tag）
- 上传 Artifact（保留 90 天）
- 创建 GitHub Release 并附加 zip

手动触发：Actions → **Release Upgrade Package** → Run workflow

### 自动发布到 marketplace（可选）

在 GitHub 仓库 Settings → Secrets and variables：

| 名称 | 类型 | 说明 |
|------|------|------|
| `MARKETPLACE_DEPLOY_TOKEN` | Secret | 与 marketplace `.env` 的 `UPGRADE_DEPLOY_TOKEN` 一致 |
| `MARKETPLACE_URL` | Variable | 默认 `https://marketplace.niceshoply.com` |
| `MARKETPLACE_AUTO_DEPLOY` | Variable | 设为 `true` 启用 tag 发布后自动上传 |

Marketplace 端：

```env
UPGRADE_DEPLOY_TOKEN=your-random-secret
```

## 服务器导入（无 CI 时）

```bash
# marketplace 服务器
php artisan upgrade:import /path/to/niceshoply-1.7.0.zip \
  --version=1.7.0 --build=20260614 --mark-latest
```

## 发布检查清单

1. 更新 `niceshoply/common/config/niceshoply.php` 中 `version` / `build`
2. 运行 `npm run release:upgrade` 或 push tag
3. 确认 marketplace `/admin/upgrades` 中版本为 Latest
4. 在测试站点后台「系统更新」验证 check + download
