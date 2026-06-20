/*
 * 【文件说明】
 *   头部导航栏交互逻辑文件，实现滚动时头部固定吸顶（sticky header）效果。
 *
 * 【加载时机】
 *   由 app.js 导入，作用于所有使用 layouts/app.blade.php 布局的前台页面。
 *   HTML 结构依赖 layouts/app.blade.php 中的 .header-box 元素。
 *
 * 【依赖】
 *   - jQuery / $（由 bootstrap.js 挂载到 window）
 *
 * 【导出/全局变量】
 *   无，所有逻辑在 DOM 就绪时直接执行。
 *
 * 【关键功能】
 *   监听页面滚动事件：
 *   - 当滚动距离超过 100px 时，为 .header-box 添加 header-fixed 类（CSS 实现固定定位吸顶）
 *   - 非首页（body 无 .page-home 类）时，同步插入等高占位元素 .header-placeholder，
 *     防止头部脱离文档流后内容区发生跳动
 *   - 滚动回到顶部时，移除固定类和占位元素，头部恢复正常文档流位置
 */

$(function () {
  // 记录头部区块的初始高度（含外边距），用于后续创建等高占位元素
  const headerContentHeight = $('.header-box').outerHeight(true);

  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      // 滚动超过 100px：为头部添加 header-fixed 类，触发 CSS 固定定位（position: fixed）
      $('.header-box').addClass('header-fixed');
      // 非首页且尚未插入占位元素时，在头部前插入等高 div，
      // 避免头部固定后内容区突然上移造成跳动感
      if (!$('body').hasClass('page-home') && !$('.header-placeholder').length) {
        $('.header-box').before('<div class="header-placeholder" style="height:' + headerContentHeight + 'px"></div>');
      }
    } else {
      // 滚动回顶部（<=100px）：移除固定类，头部恢复正常文档流
      $('.header-box').removeClass('header-fixed');
      // 同步移除占位元素，防止页面出现多余空白间距
      $('.header-placeholder').remove();
    }
  });
});
