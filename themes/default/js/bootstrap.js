/*
 * 【文件说明】
 *   前台基础依赖初始化文件，负责引入并全局挂载 Axios（HTTP 请求库）
 *   和 jQuery，同时配置 Axios 的默认请求头及全局响应拦截器。
 *
 * 【加载时机】
 *   由 app.js 第一行 import，早于所有其他模块执行，
 *   是整个前台 JS 的最底层依赖。
 *
 * 【依赖】
 *   - axios    （通过 npm 安装的 HTTP 请求库）
 *   - jquery   （通过 npm 安装的 DOM 操作库）
 *   - 全局变量 inno（来自 common.js，用于拦截器中显示错误提示；
 *                  注意：bootstrap.js 先于 common.js 执行，
 *                  此处拦截器在运行时调用 inno，而非声明时，故不会出错）
 *
 * 【导出/全局变量】
 *   - window.axios   （全局 Axios 实例）
 *   - window.$       （全局 jQuery 简写）
 *   - window.jquery  （全局 jQuery 简写，兼容）
 *   - window.jQuery  （全局 jQuery 完整写法，兼容第三方插件）
 *
 * 【关键功能】
 *   1. 将 Axios 和 jQuery 挂载到 window，供全局及第三方脚本使用
 *   2. 设置 Axios 默认请求头（XMLHttpRequest 标识、CSRF Token）
 *   3. 配置响应拦截器：成功时直接返回 response.data（省去每次 .data 取值），
 *      失败时自动弹出后端返回的错误消息
 */

// 引入 Axios 并挂载到 window，使全局脚本均可直接使用 axios.get/post 等方法
import axios from 'axios';
window.axios = axios;

// 引入 jQuery 并以多种方式挂载到 window，兼容直接使用 $ 或 jQuery 的第三方插件
import $ from 'jquery';
window.$ = window.jquery = $;
window.jQuery = require('jquery');

// 为所有 Axios 请求添加 X-Requested-With 头，后端可以此识别 Ajax 请求
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// 为所有 Axios 请求添加 Laravel CSRF Token，防止跨站请求伪造攻击
window.axios.defaults.headers.common['CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

// 配置 Axios 全局响应拦截器
axios.interceptors.response.use(function (response) {
  // 请求成功时，直接返回 response.data，省去业务代码中每次手动 .data 取值的步骤
  return response.data;
}, function (error) {
  // 请求失败时，若后端返回了 message 字段，则通过 inno.alert 弹出红色错误提示框
  if (error.response && error.response.data && error.response.data.message) {
    inno.alert({msg: error.response.data.message, type: 'danger'});
  }
  // 继续向上抛出错误，业务代码中的 .catch() 仍可捕获并做个性化处理
  return Promise.reject(error);
});
