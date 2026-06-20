/*
 * 【文件说明】
 *   前台购物车 / 心愿单的页面级交互模块（DOM 接线层）。
 *   购物车 API（加购、取数）封装在 common.js（inno.addCart / inno.getCarts），
 *   本文件只负责把页面元素事件接到这些 API 上，保持「API 层 / DOM 接线层」分离，
 *   与 header.js / footer.js 的模块约定一致。
 *
 * 【加载时机】
 *   由 app.js 导入，作用于所有使用 layouts/app.blade.php 布局的前台页面。
 *
 * 【依赖】
 *   - jQuery / $        （由 bootstrap.js 挂载到 window）
 *   - window.inno       （common.js 导出的工具对象，提供 addCart/getCarts/addWishlist）
 *
 * 【导出/全局变量】
 *   无，所有逻辑在 DOM 就绪时直接执行。
 *
 * 【关键功能】
 *   1. 页面加载时刷新头部购物车数量徽标（inno.getCarts）
 *   2. 商品列表卡片「加入购物车」快速按钮（.btn-add-cart）→ inno.addCart
 *   3. 收藏夹（心愿单）按钮（.add-wishlist）→ inno.addWishlist
 */

$(function () {
  // 页面初始化时请求迷你购物车接口，更新头部购物车图标上的商品数量徽标
  // window.inno 由 common.js 在 app.js 中挂载，故此处直接使用全局对象
  if (window.inno && typeof window.inno.getCarts === 'function') {
    window.inno.getCarts();
  }

  // 绑定「加入购物车」快速按钮点击事件（商品列表页卡片上的按钮）
  // 读取 SKU ID 后调用 inno.addCart 发起加购请求
  $('.btn-add-cart').on('click', function () {
    const skuId = $(this).data('sku-id');
    window.inno.addCart({ skuId }, this);
  });

  // 绑定收藏夹（心愿单）按钮点击事件
  // 读取商品 ID 和当前收藏状态，调用 inno.addWishlist 执行添加/取消收藏
  $('.add-wishlist').on('click', function () {
    const id = $(this).attr('data-id');
    const isWishlist = $(this).attr('data-in-wishlist') * 1;
    window.inno.addWishlist(id, isWishlist, this);
  });
});
