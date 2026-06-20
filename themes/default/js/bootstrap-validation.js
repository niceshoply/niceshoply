/*
 * 【文件说明】
 *   Bootstrap 5 表单验证增强文件，在原生 Bootstrap 验证基础上扩展了以下能力：
 *   - 自动弹出全局错误提示框
 *   - 兼容 Element UI 输入组件的错误高亮
 *   - 自动切换到含错误字段的 Tab 选项卡
 *   - 自动滚动页面到第一个错误字段位置
 *
 * 【加载时机】
 *   由 app.js 导入，作用于所有前台页面。
 *   对页面中所有带 .needs-validation 类的 <form> 元素自动生效，无需额外初始化。
 *
 * 【依赖】
 *   - jQuery / $    （由 bootstrap.js 挂载到 window）
 *   - window.inno   （来自 common.js，用于调用 inno.alert 弹出错误提示）
 *
 * 【导出/全局变量】
 *   无，所有逻辑在 DOM 就绪时直接执行，通过事件监听自动工作。
 *
 * 【关键功能】
 *   1. 拦截 .needs-validation 表单的 submit 事件，调用 HTML5 原生 checkValidity() 验证
 *   2. 处理自定义组件（非标准 input）的错误提示显示逻辑
 *   3. 若错误字段位于 Bootstrap Tab 页签内，自动切换到对应 Tab 并高亮 Tab 标题
 *   4. 自动滚动 #content 容器到第一个错误字段位置，方便用户定位问题
 *
 * 【使用方式（自定义主题参考）】
 *   在表单元素上添加 class="needs-validation" 即可自动接入此验证逻辑：
 *   <form class="needs-validation" novalidate>
 *     <input type="text" required>
 *     <div class="invalid-feedback">此项为必填</div>
 *     <button type="submit">提交</button>
 *   </form>
 */

$(function () {
  // 查找页面中所有需要验证的表单（带 .needs-validation 类）
  const forms = document.querySelectorAll(".needs-validation");

  // 遍历每个表单，分别绑定 submit 事件监听器
  Array.prototype.slice.call(forms).forEach(function (form) {
    form.addEventListener(
      "submit",
      function (event) {
        // 若表单未通过 HTML5 原生验证（有 required 字段为空或格式不正确），
        // 阻止默认表单提交并停止事件冒泡
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        // 添加 was-validated 类，触发 Bootstrap 验证样式（红框、绿框、错误文案）
        form.classList.add("was-validated");
        // 先清除上一次验证留下的所有 Tab 错误高亮、invalid 样式和错误提示显示状态
        $('.nav-link, .nav-item').removeClass('error-invalid');
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').removeClass('d-block');

        // 对所有带 required 属性的输入控件进行检查
        // 目的是处理自定义组件（如 Element UI）导致 .invalid-feedback 位置不标准的情况
        const requiredInputs = document.querySelectorAll('input[required], textarea[required], select[required]');
        requiredInputs.forEach((el) => {
          if (!$(el).val()) {
            // 若该输入框的紧邻兄弟元素没有 .invalid-feedback，
            // 则尝试查找父元素的下一个兄弟中的 .invalid-feedback 并强制显示
            if (!$(el).next('.invalid-feedback').length) {
              $(el).parent().next('.invalid-feedback').addClass('d-block');
            }
          }
        });

        // 遍历所有当前可见的错误提示元素，执行增强交互逻辑
        $('.invalid-feedback').each(function (index, el) {
          if ($(el).css('display') == 'block') {
            // 仅对第一个错误弹出全局提示框，避免同时弹出多个弹窗
            if (index == 0) {
              inno.alert({msg: '请检查表单是否填写正确', type: 'danger'});
            }

            // 兼容 Element UI：若错误提示旁边存在 el-* 组件，
            // 为其内部 input 添加红框样式（el-input__inner.error-invalid-input）
            if ($(el).siblings('div[class^="el-"]')) {
              $(el).siblings('div[class^="el-"]').find('.el-input__inner').addClass('error-invalid-input')
            }

            // 若错误字段位于 Bootstrap Tab 的 .tab-pane 容器内，
            // 自动切换到对应 Tab 并为 Tab 标题添加 error-invalid 高亮样式，
            // 方便用户识别哪个 Tab 页签存在填写错误
            if ($(el).parents('.tab-pane')) {
              $(el).parents('.tab-pane').each(function (index, el) {
                const id = $(el).prop('id');
                // 同时兼容 <a href="#id"> 和 <button data-bs-target="#id"> 两种 Tab 触发方式
                $(`a[href="#${id}"], button[data-bs-target="#${id}"]`).addClass('error-invalid')[0].click();
              })
            }

            // 将页面滚动到第一个错误字段的位置，偏移 70px（预留固定头部高度）
            // 使用 data-scroll 标志位确保只自动滚动一次，防止多个错误字段触发多次滚动
            if ($('#content').data('scroll') != 1) {
              $('#content').data('scroll', 1);
              setTimeout(() => {
                $('#content').animate({
                  scrollTop: $(el).offset().top - 70
                }, 200, () => {
                  // 滚动完成后重置标志位，允许下次提交时再次触发滚动
                  $('#content').data('scroll', 0);
                });
              }, 200);
            }
          }
        });
      },
      false
    );
  });
});
