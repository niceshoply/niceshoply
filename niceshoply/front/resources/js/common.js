/*
 * 【文件说明】
 *   全局通用工具函数集合（Common Utilities）。
 *   封装了前台页面中高频使用的业务逻辑与 UI 工具方法，
 *   通过 app.js 挂载到 window.inno，全局可调用。
 *
 * 【加载时机】
 *   由 app.js 通过 ES Module import 引入，在所有页面均有效。
 *
 * 【依赖】
 *   - axios          （HTTP 请求，由 bootstrap.js 挂载到 window）
 *   - jQuery / $     （DOM 操作，由 bootstrap.js 挂载到 window）
 *   - layer.js       （弹窗/消息提示库，由后端模板 <script> 引入）
 *   - 全局变量 urls  （各接口/页面 URL，由 layouts/app.blade.php 内联注入）
 *   - 全局变量 config（站点配置：登录状态、货币信息，由 layouts/app.blade.php 内联注入）
 *
 * 【导出/全局变量】
 *   通过 export default 导出对象，由 app.js 挂载为 window.inno：
 *   - inno.updateQueryStringParameter  更新 URL 查询参数
 *   - inno.removeURLParameters         删除 URL 查询参数
 *   - inno.getQueryString              获取 URL 查询参数值
 *   - inno.addCart                     加入购物车
 *   - inno.addWishlist                 添加/取消收藏
 *   - inno.getCarts                    获取购物车数量
 *   - inno.serializedToObj             序列化字符串转对象
 *   - inno.msg                         消息轻提示
 *   - inno.alert                       带图标的弹窗提示
 *   - inno.validateAndSubmitForm       表单验证并提交
 *   - inno.openLogin                   打开登录弹窗
 *   - inno.getBase                     获取页面 base URL
 *   - inno.formatCurrency              货币格式化（支持汇率换算）
 *   - inno.currencyFormat              货币格式化（旧版，向后兼容）
 *   - inno.setAppContentMinHeight      设置内容区最小高度（粘性底部）
 *
 * 【关键功能】
 *   1. URL 参数增删改查
 *   2. 购物车与收藏夹接口调用及按钮状态管理
 *   3. 统一的消息提示与弹窗封装（基于 layer.js）
 *   4. 货币金额格式化，支持全局汇率配置
 *   5. Bootstrap 表单验证并触发提交回调
 *   6. 自适应内容区高度，确保页脚始终贴底
 */

export default {
  // ─── URL 工具 ────────────────────────────────────────────────────────────────

  // 使用正则表达式更新 URL 中指定查询参数的值；
  // 若参数已存在则替换，不存在则追加到末尾
  updateQueryStringParameter(uri, key, value) {
    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
      return uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
      return uri + separator + key + "=" + value;
    }
  },

  // 删除 URL 中指定的一个或多个查询参数，返回处理后的新 URL 字符串
  // 示例：removeURLParameters('https://example.com?a=1&b=2', 'a') => 'https://example.com?b=2'
  removeURLParameters(url, ...parameters) {
    const parsed = new URL(url);
    parameters.forEach(e => parsed.searchParams.delete(e))
    return parsed.toString()
  },

  // 通过正则从 URL 字符串中读取指定查询参数的值，默认从当前页面 URL 中读取
  // 返回参数值字符串，不存在则返回 null
  getQueryString(name, url = window.location.href) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = url.split('?')[1] ? url.split('?')[1].match(reg) : null;
    if (r != null) return unescape(r[2]);
    return null;
  },

  // ─── 购物车 & 收藏夹 ─────────────────────────────────────────────────────────

  // 发起"加入购物车"请求
  // 参数说明：
  //   skuId    - 商品 SKU ID（必填）
  //   quantity - 加购数量，默认为 1
  //   isBuyNow - 是否为"立即购买"模式（true 时不弹成功提示，直接跳转结算）
  //   options  - 附加选项（如定制内容），可选
  //   event    - 触发点击的按钮 DOM 元素，用于显示/隐藏加载状态
  //   callback - 请求成功后的回调函数，接收后端响应数据
  addCart({skuId, quantity = 1, isBuyNow = false, options = {}}, event, callback) {
    const $btn = $(event);
    // 按钮置为禁用并显示 loading 旋转图标，防止重复提交
    $btn.addClass('disabled').prepend('<span class="spinner-border spinner-border-sm me-1"></span>');
    // 移除页面上可能残留的 tooltip 弹出层，避免视觉干扰
    $(document).find('.tooltip').remove();

    const requestData = {
      sku_id: skuId, 
      quantity, 
      buy_now: isBuyNow
    };

    // 若存在附加选项（如礼品包装、刻字等），将其一并提交给后端
    if (options && Object.keys(options).length > 0) {
      requestData.options = options;
    }

    // 调用加购接口（urls.front_cart_add），成功后更新头部购物车数量
    axios.post(urls.front_cart_add, requestData).then((res) => {
      // 非"立即购买"模式下，弹出成功消息提示（如"已加入购物车"）
      if (!isBuyNow) {
        layer.msg(res.message)
      }
      // 更新头部购物车图标上的商品总数
      $('.header-cart-icon .icon-quantity').text(res.data.total_format)

      if (callback) {
        callback(res)
      }
    }).finally(() => {
      // 无论成功或失败，最终都恢复按钮可用状态并移除 loading 图标
      $btn.removeClass('disabled').find('.spinner-border').remove();
    })
  },

  // 添加或取消收藏（心愿单）
  // 参数说明：
  //   id         - 商品 ID
  //   isWishlist - 当前是否已收藏（1=已收藏，0=未收藏），决定执行添加还是取消操作
  //   event      - 触发点击的收藏按钮 DOM 元素
  //   callback   - 操作成功后的回调函数
  addWishlist(id, isWishlist, event, callback) {
    // 未登录时弹出登录弹窗，中断操作
    if (!config.isLogin) {
      this.openLogin()
      return;
    }
    const $btn = $(event);
    // 保存按钮原始 HTML，操作完成后恢复（包含图标等结构）
    const btnHtml = $btn.html();
    const loadHtml = '<span class="spinner-border spinner-border-sm"></span>';
    $(document).find('.tooltip').remove();

    if (isWishlist) {
      // 当前已收藏 → 执行取消收藏操作
      $btn.html(loadHtml).prop('disabled', true);
      axios.post(`${urls.front_favorite_cancel}`, {product_id: id}).then((res) => {
        layer.msg(res.message)
        // 更新按钮的 data-in-wishlist 属性为 0（已取消收藏）
        $btn.attr('data-in-wishlist', 0);
        if (callback) {
          callback(res)
        }
      }).finally((e) => {
        // 恢复按钮内容，并将心形图标切换为空心（bi-heart）表示未收藏
        $btn.html(btnHtml).prop('disabled', false).find('i.bi').prop('class', 'bi bi-heart')
      })
    } else {
      // 当前未收藏 → 执行添加收藏操作
      $btn.html(loadHtml).prop('disabled', true);
      axios.post(`${urls.front_favorites}`, {product_id: id}).then((res) => {
        layer.msg(res.message)
        // 更新按钮的 data-in-wishlist 属性为 1（已收藏）
        $btn.attr('data-in-wishlist', 1);
        // 恢复按钮内容，并将心形图标切换为实心（bi-heart-fill）表示已收藏
        $btn.html(btnHtml).prop('disabled', false).find('i.bi').prop('class', 'bi bi-heart-fill')
        if (callback) {
          callback(res)
        }
      }).catch((e) => {
        // 添加收藏失败时，仅恢复按钮状态，错误提示由全局拦截器处理
        $btn.html(btnHtml).prop('disabled', false)
      })
    }
  },

  // 请求迷你购物车接口，获取当前购物车中的商品总数，
  // 并更新头部购物车图标上的数字徽标（.icon-quantity）
  getCarts() {
    axios.get(urls.front_cart_mini).then((res) => {
      $('.header-cart-icon .icon-quantity').text(res.data.total_format)
    })
  },

  // ─── 工具函数 ─────────────────────────────────────────────────────────────────

  // 将 jQuery serialize() 生成的查询字符串转换为普通 JS 对象
  // 若同名参数出现多次（如多选框），则将其值合并为数组
  // 示例：'a=1&b=2&b=3' => { a: '1', b: ['2', '3'] }
  serializedToObj(serializedStr) {
    const obj = {};
    const pairs = serializedStr.split('&');
    pairs.forEach(function(pair) {
      const [key, value] = pair.split('=').map(decodeURIComponent);
      if (obj[key]) {
        if (Array.isArray(obj[key])) {
          obj[key].push(value);
        } else {
          obj[key] = [obj[key], value];
        }
      } else {
        obj[key] = value;
      }
    });
    return obj;
  },

  // ─── UI 提示 ──────────────────────────────────────────────────────────────────

  // 显示轻量级消息提示（基于 layer.msg）
  // 参数：params 可以是字符串（直接显示），或对象 { msg, time }
  //   msg  - 提示内容
  //   time - 自动消失时间（毫秒），默认 2000ms
  msg(params = {}, callback = null) {
    let msg = typeof params === 'string' ? params : params.msg || '';
    let time = params.time || 2000;
    layer.msg(msg, {time}, callback);
  },

  // 显示带图标的弹窗提示（基于 layer.msg，带半透明遮罩）
  // 参数：params 可以是字符串，或对象 { msg, type }
  //   type - 'success'（绿色对勾图标）或 'danger'（红色叉号图标），默认 'success'
  // 自动 5 秒后关闭，点击遮罩也可关闭
  alert(params = {}, callback = null) {
    let msg = typeof params === 'string' ? params : params.msg || '';
    let type = params.type || 'success';
    // layer 图标：1 = 成功（对勾），2 = 失败（叉号）
    let icon = type === 'success' ? 1 : 2;
    
    layer.msg(msg, {
      icon: icon,
      shade: 0.3,
      shadeClose: true,
      time: 5000
    }, callback);
  },

  // ─── 表单 & 登录 ──────────────────────────────────────────────────────────────

  // Bootstrap 表单验证封装：监听指定表单的提交按钮点击事件
  // 参数：
  //   form     - 表单选择器字符串，如 '#loginForm'
  //   callback - 验证通过后的回调，接收 jQuery serialize() 序列化字符串
  // 额外支持在表单输入框内按 Enter 键触发提交（等同于点击提交按钮）
  validateAndSubmitForm(form, callback) {
    $(document).on('click', `${form} .form-submit`, function(event) {
      // 若表单未通过 HTML5 原生验证，阻止默认提交行为并停止冒泡
      if ($(form)[0].checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
      }
      // 添加 was-validated 类，触发 Bootstrap 验证样式（红框、错误文案显示）
      $(form).addClass('was-validated');

      // 仅在验证通过时执行回调，将表单数据序列化为字符串传入
      if ($(form)[0].checkValidity() === true) {
        callback($(form).serialize());
      }
    })

    // 支持在输入框内按 Enter 键触发提交按钮点击，提升表单操作便捷性
    $(document).on('keypress', `${form} input`, function(event) {
      if (event.keyCode === 13) {
        $(`${form} .form-submit`).trigger('click');
      }
    })
  },

  // 使用 layer.open（type: 2 = iframe 模式）弹出登录页弹窗
  // 移动端（宽度 < 768px）时弹窗宽度为 94%，桌面端固定 500px
  // 弹窗内容为登录页面（附带 ?iframe=true 参数，后端据此返回精简版页面）
  // 弹窗打开后自动调整高度和垂直居中位置
  openLogin() {
    var area = window.innerWidth < 768 ? '94%' : '500px';

    layer.open({
      type: 2,
      title: '',
      area: area,
      // 加载登录页，通过 iframe=true 参数告知后端渲染无头部/底部的精简布局
      content: `${urls.front_login}?iframe=true`,
      success: function(layero, index) {
        var iframe = $(layero).find('iframe');
        // 根据登录页 iframe 内容实际高度动态调整弹窗高度（+20 为缓冲间距）
        iframe.css('height', iframe[0].contentDocument.body.offsetHeight + 20);
        // 重新计算并设置弹窗垂直居中位置
        $(layero).css('top', (window.innerHeight - iframe[0].offsetHeight) / 2);
      }
    });
  },

  // ─── 其他工具 ────────────────────────────────────────────────────────────────

  // 读取 HTML <base> 标签的 href 属性，返回去掉末尾斜杠的基础 URL
  // 用于在 JS 中动态拼接绝对路径，避免硬编码域名
  getBase() {
    let url = document.querySelector('base').href;
    if (url.endsWith('/')) {
      url = url.slice(0, -1);
    }
    return url;
  },

  // 货币金额格式化（新版，支持全局汇率换算）
  // 参数：
  //   amount         - 原始金额（以基准货币计，通常为 USD）
  //   currencyConfig - 货币配置对象，默认读取全局 config.currency：
  //                    { symbol_left, symbol_right, decimal_place, rate }
  // 计算逻辑：price = amount × rate，再按 decimal_place 保留小数，
  //           最终拼接左侧符号 + 金额 + 右侧符号
  // 示例：formatCurrency(9.9) => '$9.90'
  formatCurrency(amount, currencyConfig = null) {
    // 若未传入货币配置，则优先使用全局 config.currency，最终回退到 USD 默认值
    const currency = currencyConfig || (config && config.currency) || {
      symbol_left: '$',
      symbol_right: '',
      decimal_place: 2,
      rate: 1
    };
    
    const price = parseFloat(amount) * currency.rate;
    const formattedAmount = price.toFixed(currency.decimal_place);
    
    let result = '';
    if (currency.symbol_left) {
      result += currency.symbol_left;
    }
    result += formattedAmount;
    if (currency.symbol_right) {
      result += ' ' + currency.symbol_right;
    }
    
    return result;
  },

  // 货币金额格式化（旧版，保留以向后兼容旧代码）
  // 仅支持简单的"符号 + 保留小数"，不支持汇率换算
  // 新开发代码请使用 formatCurrency() 代替此方法
  currencyFormat(amount, symbol = '$', decimals = 2) {
    const num = parseFloat(amount) || 0;
    return symbol + num.toFixed(decimals);
  },

  // 计算并设置 #appContent 的最小高度，实现"粘性底部"布局：
  // 保证内容区高度 = 视口高度 - 头部高度 - 底部高度 - 48px（上下边距补偿）
  // 由 app.js 在 DOM 就绪时和窗口 resize 时调用
  setAppContentMinHeight(){
    let appHeaderHeight = $('#appHeader').outerHeight();
    let appFooterHeight = $('#appFooter').outerHeight(true);
    let windowHeight = $(window).outerHeight();
    $('#appContent').css('min-height', (windowHeight - appHeaderHeight - appFooterHeight - 48) + 'px');
  }
};
