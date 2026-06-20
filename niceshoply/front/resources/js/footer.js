/*
 * 【文件说明】
 *   底部（Footer）交互逻辑文件，实现移动端底部链接区块的折叠/展开功能。
 *
 * 【加载时机】
 *   由 app.js 导入，作用于所有使用 layouts/app.blade.php 布局的前台页面。
 *   HTML 结构依赖底部模板中的 .footer-link-title 和 .footer-link-icon 元素。
 *
 * 【依赖】
 *   - jQuery / $（由 bootstrap.js 挂载到 window）
 *
 * 【导出/全局变量】
 *   无，所有逻辑在 DOM 就绪时直接执行。
 *
 * 【关键功能】
 *   为底部每个链接分组的标题行中的箭头图标（.footer-link-icon）绑定点击事件：
 *   - 切换图标的 active 状态（CSS 可据此旋转箭头方向）
 *   - 通过 slideToggle 动画展开或折叠该标题下方的链接列表
 *   此功能主要用于移动端，将底部多个链接分组做成手风琴式交互，节省屏幕空间。
 */

$(function () {
  // 点击底部链接分组标题栏内的折叠图标时，切换对应列表的显示/隐藏状态
  // toggleClass('active') 用于切换箭头旋转方向（通过 CSS transform 控制）
  // parent().next() 定位到紧随标题容器之后的链接列表容器，执行 slideToggle 动画
  $('.footer-link-title .footer-link-icon').on('click', function () {
    $(this).toggleClass('active');
    $(this).parent().next().slideToggle();
  })
})