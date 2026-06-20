/*
 * NiceShoply 资源构建编排脚本（参考 InnoShop 标准）
 *
 * 每个入口通过环境变量在同一进程内串行调用 Vite 的 build() API（复用 vite.config.js）。
 * 采用 InnoShop 的「IIFE 固定路径」模型：不使用 @vite()/HMR，产物可被普通
 * <script>/<link> 直接引用。构建完成后额外生成 public/build/manifest.json，
 * 供后端 build_asset() 做内容哈希缓存失效（InnoShop 无此项，为 NiceShoply 增强）。
 *
 * 用法：
 *   node build.js                       构建全部核心模块（front + console + install）
 *   node build.js --watch               监听源文件变更自动重建（开发；非 HMR）
 *   TARGET=front   node build.js        仅构建 front 模块
 *   TARGET=console node build.js        仅构建 console 模块
 *   TARGET=install node build.js        仅构建 install 模块
 *   THEME=xxx      node build.js        仅构建指定主题（存在 assets/ 时）
 */
import { build } from 'vite';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import fs from 'node:fs';
import crypto from 'node:crypto';

const root = dirname(fileURLToPath(import.meta.url));
const watch = process.argv.includes('--watch');
const target = process.env.TARGET || '';
const theme = process.env.THEME || '';
const startTime = Date.now();

// vite.config.js 作为单一配置源（env 驱动）。为兼顾「真实 vite.config.js」与构建速度，
// 这里在同一进程内串行调用 Vite 的 build() API，并通过 configFile 复用配置——
// 既保持 InnoShop 式 env 驱动单入口模型，又避免每入口冷启动一个 vite 进程的巨大开销。
const viteConfig = join(root, 'vite.config.js');

// 核心入口（front / console / install）。CSS 与 bootstrap 分别独立产出，沿用现有 blade 引用。
const coreEntries = [
    { name: 'front/js',        input: 'niceshoply/front/resources/js/app.js',                    outDir: 'public/build/front/js',    outputName: 'app',       group: 'front' },
    { name: 'front/css',       input: 'niceshoply/front/resources/css/app.scss',                 outDir: 'public/build/front/css',   outputName: 'app',       group: 'front' },
    { name: 'front/bootstrap', input: 'niceshoply/front/resources/css/bootstrap/bootstrap.scss', outDir: 'public/build/front/css',   outputName: 'bootstrap', group: 'front' },
    { name: 'console/js',        input: 'niceshoply/console/resources/js/app.js',                    outDir: 'public/build/console/js',  outputName: 'app',       group: 'console' },
    { name: 'console/css',       input: 'niceshoply/console/resources/css/app.scss',                 outDir: 'public/build/console/css', outputName: 'app',       group: 'console' },
    { name: 'console/bootstrap', input: 'niceshoply/console/resources/css/bootstrap/bootstrap.scss', outDir: 'public/build/console/css', outputName: 'bootstrap', group: 'console' },
    { name: 'install/css',     input: 'niceshoply/install/resources/css/app.scss',               outDir: 'public/build/install/css', outputName: 'app',       group: 'install' },
];

// 选择入口：THEME 优先（只构建主题），否则按 TARGET 过滤核心入口
const entries = theme ? [] : coreEntries.filter((e) => !target || e.group === target);

// 主题入口（与 InnoShop 一致：存在 assets/ 才编译并分发）
if (theme) {
    const themeDir = `niceshoply/themes/${theme}`;
    const themeOut = `public/static/themes/${theme}`;

    for (const dir of ['css', 'js']) {
        const p = join(root, themeOut, dir);
        if (fs.existsSync(p)) fs.rmSync(p, { recursive: true, force: true });
    }

    if (fs.existsSync(join(root, `${themeDir}/assets/scss/app.scss`))) {
        entries.push({ name: 'theme/css', input: `${themeDir}/assets/scss/app.scss`, outDir: `${themeOut}/css`, outputName: 'app' });
    }
    if (fs.existsSync(join(root, `${themeDir}/assets/js/app.js`))) {
        entries.push({ name: 'theme/js', input: `${themeDir}/assets/js/app.js`, outDir: `${themeOut}/js`, outputName: 'app' });
    }
}

if (entries.length === 0) {
    console.log('没有可构建的入口（检查 TARGET / THEME 是否正确，或主题是否含 assets/）。');
    process.exit(0);
}

/**
 * 构建单个入口：设置 env 后调用 vite.config.js（产物规整在配置内的插件完成）。
 *
 * 串行调用，故复用 process.env 不存在竞态。
 *
 * @param {object} entry 入口定义
 */
async function buildEntry(entry) {
    process.env.BUILD_INPUT = entry.input;
    process.env.BUILD_OUTDIR = entry.outDir;
    process.env.BUILD_OUTPUT_NAME = entry.outputName;
    process.env.BUILD_WATCH = '';
    // 监听模式下关闭压缩、开启 sourcemap（buildEntry 仅用于一次性构建路径）
    process.env.BUILD_DEV = watch ? '1' : '';

    await build({ configFile: viteConfig, root, logLevel: 'warn' });
}

/*
 * 生成 manifest.json：为已存在的核心产物计算内容哈希，供 build_asset() 缓存失效。
 * 扫描磁盘上实际存在的文件，因此即便本次只构建了部分 TARGET，manifest 仍保持完整。
 */
function writeManifest() {
    const buildDir = join(root, 'public/build');
    if (!fs.existsSync(buildDir)) return;

    const manifest = {};
    for (const e of coreEntries) {
        const ext = e.input.endsWith('.js') ? 'js' : 'css';
        const rel = `${e.outDir.replace('public/build/', '')}/${e.outputName}.${ext}`;
        const file = join(buildDir, rel);
        if (!fs.existsSync(file)) continue;
        const hash = crypto.createHash('md5').update(fs.readFileSync(file)).digest('hex').slice(0, 12);
        manifest[rel] = { file: rel, hash };
    }

    fs.writeFileSync(join(buildDir, 'manifest.json'), JSON.stringify(manifest, null, 2));
}

/**
 * 执行一次完整构建（全部选中入口 + manifest）。
 *
 * @returns {Promise<number>} 失败的入口数
 */
async function buildAll() {
    let failed = 0;
    for (const entry of entries) {
        try {
            await buildEntry(entry);
            console.log(`  ✓ ${entry.name}`);
        } catch (e) {
            console.error(`  ✗ ${entry.name}`);
            const err = e.message || '';
            err.split('\n').filter((l) => l.toLowerCase().includes('error')).slice(0, 3).forEach((l) => console.error(`    ${l}`));
            failed++;
        }
    }

    if (!theme) {
        writeManifest();
    }
    return failed;
}

if (watch) {
    // 监听模式：使用 Vite 自身的增量 watch（非 HMR），首次构建后改文件秒级重建。
    // 串行创建各入口 watcher，避免共享 process.env 竞态。
    // 开发期删除 manifest.json，让 build_asset() 回退为无版本号 URL（始终取最新产物）。
    const manifestFile = join(root, 'public/build/manifest.json');
    if (fs.existsSync(manifestFile)) fs.rmSync(manifestFile);

    console.log('首次构建并进入监听…');
    for (const entry of entries) {
        process.env.BUILD_INPUT = entry.input;
        process.env.BUILD_OUTDIR = entry.outDir;
        process.env.BUILD_OUTPUT_NAME = entry.outputName;
        process.env.BUILD_WATCH = '1';
        try {
            await build({ configFile: viteConfig, root, logLevel: 'warn' });
            console.log(`  ✓ ${entry.name}（监听中）`);
        } catch (e) {
            console.error(`  ✗ ${entry.name}: ${e.message?.split('\n')[0] || e}`);
        }
    }
    console.log('👀 监听中（Ctrl+C 退出）…');
} else {
    const failed = await buildAll();
    const elapsed = ((Date.now() - startTime) / 1000).toFixed(2);
    const scope = theme ? `主题 ${theme}` : target ? `${target} 模块` : 'public/build/{front,console,install}（含 manifest.json）';
    console.log(`\n✅ 资源构建完成 → ${scope}，用时 ${elapsed}s${failed ? `（${failed} 个入口失败）` : ''}`);
    if (failed) process.exit(1);
}
