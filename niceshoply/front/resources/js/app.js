/*
 * 【文件说明】
 *   前台 JavaScript 主入口文件，负责汇总导入所有子模块，
 *   初始化全局 Axios 请求头，并绑定页面级通用交互事件。
 *
 * 【加载时机】
 *   在 layouts/app.blade.php 中通过 Vite/Mix 打包后统一引入，
 *   作用于所有前台页面。
 *
 * 【依赖】
 *   - bootstrap.js        （Axios、jQuery 初始化）
 *   - common.js           （全局工具函数集合）
 *   - bootstrap-validation.js（Bootstrap 表单验证）
 *   - footer.js           （底部交互）
 *   - header.js           （头部滚动效果）
 *   - autocomplete.js     （搜索自动补全插件）
 *   - cart.js             （购物车/心愿单页面接线）
 *   - 全局变量 urls       （各接口/页面地址）
 *   - 全局变量 config     （站点配置，如是否登录、货币信息）
 *   - layer.js            （弹窗/消息提示库，由后端模板引入）
 *
 * 【导出/全局变量】
 *   - window.inno         （挂载 common.js 导出对象，供全局调用）
 *
 * 【关键功能】
 *   1. 导入并整合所有功能模块
 *   2. 设置 Axios 鉴权头（Bearer Token）和 CSRF Token
 *   3. 页面加载完成后：获取购物车数量、绑定提示框关闭、
 *      收藏夹按钮、加购按钮、Bootstrap Tooltip 初始化、
 *      以及动态设置内容区最小高度
 */

// 初始化 Bootstrap 5、Axios、jQuery，并配置拦截器
import './bootstrap';

// 导入通用工具函数集合，并将其挂载到 window.inno，方便全局调用
import common from "./common";
window.inno = common;

// 导入 Bootstrap 表单验证增强逻辑
import './bootstrap-validation';
// 导入底部链接折叠/展开交互
import './footer';
// 导入头部导航栏滚动固定效果
import './header';
// 导入搜索框自动补全插件
import './autocomplete';
// 导入购物车 / 心愿单页面接线模块（依赖 window.inno，须在 common 挂载后导入）
import './cart';

// 从页面 <meta name="api-token"> 标签读取 API Token，用于接口鉴权
const apiToken = $('meta[name="api-token"]').attr('content');
// 为所有 Axios 请求统一附加 Authorization Bearer Token
axios.defaults.headers.common['Authorization'] = 'Bearer ' + apiToken;
// 为所有 Axios 请求统一附加 Laravel CSRF Token，防止跨站请求伪造
axios.defaults.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

$(function () {
  // 购物车数量徽标、加购按钮、心愿单按钮等接线已迁移至 cart.js 模块

  // 监听顶部浮动提示框（.is-alert）的关闭按钮点击事件
  // 关闭后重新计算并动画堆叠剩余提示框的位置，避免出现空隙
  $(document).on('click', '.is-alert .btn-close', function () {
    let top = 40;
    $('.is-alert').each(function () {
      $(this).animate({top}, 100);
      top += $(this).outerHeight() + 10;
    });
  })

  // 初始化页面中所有带 data-bs-toggle="tooltip" 属性的元素，启用 Bootstrap 提示框
  $(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();
  });

  // 计算并设置 #appContent 的最小高度，确保页脚始终位于屏幕底部（粘性底部效果）
  common.setAppContentMinHeight();
  // 窗口尺寸变化时重新计算最小高度
  $(window).on('resize', common.setAppContentMinHeight)
})
