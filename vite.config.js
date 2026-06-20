/*
 * NiceShoply Vite 配置（参考 InnoShop 标准）
 *
 * 设计：单入口、由环境变量驱动，每个入口由 build.js 调一次 `npx vite build`。
 * 不使用 Vite 多入口，也不使用 dev server / @vite() / HMR —— 沿用 InnoShop 的
 * 「IIFE 固定路径」运行时模型，产出可被普通 <script>/<link> 直接引用、立即执行
 * 的资源，从而无需改动数百个 blade 内联脚本与加载顺序。
 *
 * 环境变量：
 *   BUILD_INPUT       — 源文件路径（如 niceshoply/front/resources/css/app.scss）
 *   BUILD_OUTDIR      — 输出目录（如 public/build/front/css）
 *   BUILD_OUTPUT_NAME — 输出文件名（默认 app；CSS 入口可为 bootstrap）
 *   BUILD_DEV         — 1 时关闭压缩、开启 sourcemap（开发/监听用）
 *   BUILD_WATCH       — 1 时启用 Vite 自身 watch（保留能力，默认由 build.js 的 fs.watch 驱动）
 *
 * 构建模式：
 *   JS  入口 — lib(IIFE) 模式，输出 ${BUILD_OUTPUT_NAME}.js
 *   CSS 入口 — rollupOptions.input 模式，输出 ${BUILD_OUTPUT_NAME}.css（无 hash）
 */
import { defineConfig } from 'vite';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import fs from 'node:fs';

const root = dirname(fileURLToPath(import.meta.url));

const input = process.env.BUILD_INPUT;
const outDir = process.env.BUILD_OUTDIR;
const outputName = process.env.BUILD_OUTPUT_NAME || 'app';
const viteWatch = process.env.BUILD_WATCH === '1';
const dev = process.env.BUILD_DEV === '1' || viteWatch;

if (!input || !outDir) {
    throw new Error('需要 BUILD_INPUT / BUILD_OUTDIR 环境变量。请使用 `node build.js` 进行完整构建。');
}

const isJS = input.endsWith('.js');

// dart-sass 解析配置：通过 loadPaths 解析 node_modules 内的 bootstrap / bootstrap-icons
const scss = {
    loadPaths: [resolve(root, 'node_modules'), 'node_modules'],
    quietDeps: true,
    silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'mixed-decls', 'legacy-js-api'],
};

/*
 * 产物规整插件：
 *  - JS 入口：Vite lib(IIFE) 不同版本可能产出 index.js / app.iife.js，统一为 ${outputName}.js
 *  - CSS 入口：清理 scss 入口附带产生的空 JS 占位文件
 * 放在插件 closeBundle 中执行，使一次性构建与 watch 增量重建均生效。
 */
function finalizeOutput() {
    return {
        name: 'niceshoply-finalize-output',
        closeBundle() {
            const absOut = resolve(root, outDir);
            if (!fs.existsSync(absOut)) return;

            if (isJS) {
                const dst = join(absOut, `${outputName}.js`);
                if (!fs.existsSync(dst)) {
                    const produced = fs.readdirSync(absOut).find((f) => f.endsWith('.js') && f !== `${outputName}.js`);
                    if (produced) fs.renameSync(join(absOut, produced), dst);
                }
            } else {
                const stray = join(absOut, `${outputName}.js`);
                if (fs.existsSync(stray)) fs.rmSync(stray);
            }
        },
    };
}

export default defineConfig({
    publicDir: false,
    logLevel: 'warn',
    plugins: [finalizeOutput()],
    css: {
        preprocessorOptions: { scss },
    },
    build: {
        outDir,
        emptyOutDir: false,
        minify: !dev,
        sourcemap: dev,
        watch: viteWatch ? {} : null,
        // JS：lib + iife，自包含打包，产出可被普通 <script> 立即执行的文件
        lib: isJS
            ? {
                  entry: resolve(root, input),
                  name: 'app',
                  formats: ['iife'],
                  fileName: () => outputName,
              }
            : undefined,
        rollupOptions: {
            // CSS：scss 作为入口（会附带空 js，由 build.js 清理）
            input: isJS ? undefined : resolve(root, input),
            output: {
                dir: outDir,
                entryFileNames: `${outputName}.js`,
                // 统一以 BUILD_OUTPUT_NAME 命名产物，禁用内容 hash（版本化交给 manifest）
                assetFileNames: `${outputName}[extname]`,
            },
        },
    },
});
