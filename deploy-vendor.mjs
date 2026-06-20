/*
 * 把 node_modules 里的第三方 standalone 库部署到 public/vendor/。
 *
 * 后台/前台的 blade 通过 asset('vendor/...') 直接以 <script>/<link> 引用这些库
 * （jQuery、Bootstrap、Vue、Element Plus、layer、laydate、tinymce、chart、codemirror 等），
 * 它们不经过 Vite 打包。本脚本在 `npm run build` 前自动执行，保证 public/vendor 完整可重现。
 *
 * 用法：node deploy-vendor.mjs   （或 npm run vendor）
 */
import fs from 'node:fs';
import path from 'node:path';

const root = process.cwd();
const nm = path.join(root, 'node_modules');
const out = path.join(root, 'public/vendor');

function cp(src, dest) {
    const s = path.join(nm, src);
    const d = path.join(out, dest);
    if (!fs.existsSync(s)) { console.log('✗ 缺失: ' + src); return false; }
    fs.mkdirSync(path.dirname(d), { recursive: true });
    // 目标文件属主为 www 时，copyFileSync 可能 EPERM；先删除再写入
    if (fs.existsSync(d)) {
        try { fs.unlinkSync(d); } catch { /* 尽力删除旧文件 */ }
    }
    try {
        fs.copyFileSync(s, d);
    } catch {
        fs.writeFileSync(d, fs.readFileSync(s));
    }
    return true;
}

function cpDir(src, dest) {
    const s = path.join(nm, src);
    const d = path.join(out, dest);
    if (!fs.existsSync(s)) { console.log('✗ 缺失目录: ' + src); return false; }
    fs.cpSync(s, d, { recursive: true });
    return true;
}

let ok = 0, miss = 0;
const tick = (r) => { r ? ok++ : miss++; };

// —— 基础库 ——
tick(cp('jquery/dist/jquery.min.js', 'jquery/jquery-3.7.1.min.js'));
tick(cp('bootstrap/dist/js/bootstrap.bundle.min.js', 'bootstrap/js/bootstrap.bundle.min.js'));
tick(cp('bootstrap-icons/font/bootstrap-icons.css', 'bootstrap-icons/bootstrap-icons.css'));
tick(cp('bootstrap-icons/font/fonts/bootstrap-icons.woff2', 'bootstrap-icons/bootstrap-icons.woff2'));
tick(cp('bootstrap-icons/font/fonts/bootstrap-icons.woff', 'bootstrap-icons/bootstrap-icons.woff'));

// —— Vue 3 + Element Plus ——
tick(cp('vue/dist/vue.global.js', 'vue/3.5/vue.global.js'));
tick(cp('vue/dist/vue.global.prod.js', 'vue/3.5/vue.global.prod.js'));
tick(cp('element-plus/dist/index.full.js', 'element-plus/index.full.js'));
tick(cp('element-plus/dist/index.css', 'element-plus/index.css'));
tick(cp('@element-plus/icons-vue/dist/index.iife.min.js', 'element-plus/icons.min.js'));

// —— layer（弹窗）：须用 dist 浏览器包（含 window.layer）；根目录 layer.js 为 CommonJS，直引会报 layer 未定义
tick(cp('layui-layer/dist/layer.js', 'layer/3.5.1/layer.js'));
tick(cp('layui-layer/layer.css', 'layer/3.5.1/skin/default/layer.css'));
for (const img of ['icon.png', 'icon-ext.png', 'loading-0.gif', 'loading-1.gif', 'loading-2.gif']) {
    tick(cp('layui-layer/' + img, 'layer/3.5.1/skin/default/' + img));
}

// —— laydate（日期选择）：skins/ 子目录 ——
tick(cp('laydate/dist/laydate.js', 'laydate/laydate.js'));
tick(cpDir('laydate/laydate/skins', 'laydate/skins'));

// —— 富文本/图表/拖拽/代码编辑器 ——
tick(cpDir('tinymce', 'tinymce/5.9.1'));
tick(cp('chart.js/dist/chart.umd.min.js', 'chart/chart.min.js'));
tick(cp('sortablejs/Sortable.min.js', 'vuedraggable/sortable.min.js'));
tick(cp('vuedraggable/dist/vuedraggable.umd.min.js', 'vuedraggable/vuedraggable.umd.min.js'));
for (const d of ['lib', 'addon', 'mode', 'theme', 'keymap']) {
    tick(cpDir('codemirror/' + d, 'codemirror/' + d));
}

// —— 前台：swiper / photoswipe / video.js ——
tick(cp('swiper/swiper-bundle.min.js', 'swiper/swiper-bundle.min.js'));
tick(cp('swiper/swiper-bundle.min.css', 'swiper/swiper-bundle.min.css'));
tick(cp('photoswipe/dist/umd/photoswipe.umd.min.js', 'photoswipe/umd/photoswipe.umd.min.js'));
tick(cp('photoswipe/dist/umd/photoswipe-lightbox.umd.min.js', 'photoswipe/umd/photoswipe-lightbox.umd.min.js'));
tick(cp('photoswipe/dist/photoswipe.css', 'photoswipe/photoswipe.css'));
tick(cp('video.js/dist/video.min.js', 'video-js/video.min.js'));
tick(cp('video.js/dist/video-js.min.css', 'video-js/video-js.css'));

// —— github markdown / 代码高亮（插件说明页）——
tick(cp('github-markdown-css/github-markdown.css', 'github/github-markdown.min.css'));
tick(cp('highlight.js/styles/github.min.css', 'github/highlight-github.min.css'));

// —— 占位图 ——
const placeholder = path.join(out, 'NiceShoply/images/placeholder.png');
if (!fs.existsSync(placeholder)) {
    fs.mkdirSync(path.dirname(placeholder), { recursive: true });
    // 1x1 透明 PNG
    fs.writeFileSync(placeholder, Buffer.from(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        'base64'
    ));
}
ok++;

console.log(`\n✅ vendor 部署完成 → public/vendor（成功 ${ok}，缺失 ${miss}）`);
if (miss) process.exit(0); // 缺失不阻断构建，仅提示
