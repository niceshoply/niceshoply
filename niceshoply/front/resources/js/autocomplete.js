/*
 * 【文件说明】
 *   搜索框自动补全插件文件，以 jQuery 插件形式（$.fn.autocomplete）实现
 *   输入关键词时实时拉取建议列表并以下拉菜单形式展示的功能。
 *
 * 【加载时机】
 *   由 app.js 导入，作用于所有前台页面。
 *   具体的搜索框需在对应模板（如 header 模板）中调用：
 *   $('input.search').autocomplete({ source: function(query, response) { ... }, select: function(item) { ... } })
 *
 * 【依赖】
 *   - jQuery / $（由 bootstrap.js 挂载到 window）
 *
 * 【导出/全局变量】
 *   - $.fn.autocomplete（jQuery 插件，挂载到 jQuery 原型链）
 *
 * 【关键功能】
 *   1. 防抖请求：用户输入后延迟 200ms 再调用 source 回调，减少接口调用频率
 *   2. 分类渲染：支持带 category 字段的数据，自动在下拉列表中插入分类标题
 *   3. 交互事件：focus 触发请求、blur 延迟 200ms 隐藏菜单（留出时间响应点击）、
 *      ESC 键直接隐藏菜单
 *   4. 位置计算：下拉菜单动态定位于输入框正下方，宽度自动继承输入框宽度
 *
 * 【使用方式（自定义主题参考）】
 *   $('input#searchBox').autocomplete({
 *     // source: 接收用户输入的关键词和响应回调，在此函数内调用搜索接口
 *     source: function(query, response) {
 *       axios.get('/api/search/suggest', { params: { q: query } }).then(res => {
 *         response(res.data); // data 格式：[{ value, label, category? }]
 *       });
 *     },
 *     // select: 用户点击某条建议时触发，item 为对应数据对象
 *     select: function(item) {
 *       window.location.href = '/search?q=' + item.value;
 *     }
 *   });
 */

$(function() {
  // 将 autocomplete 注册为 jQuery 插件，支持链式调用
  $.fn.autocomplete = function(option) {
    return this.each(function() {
      // timer: 防抖定时器句柄，避免每次按键都立即发请求
      this.timer = null;
      // items: 以 value 为键缓存所有补全项数据，用于点击时快速查找完整数据对象
      this.items = new Array();

      // 将 option 对象（source、select 等配置）合并到当前 input 元素实例上
      $.extend(this, option);

      // 禁用浏览器原生 autocomplete，避免与自定义下拉列表冲突
      $(this).attr('autocomplete', 'off');

      // 输入框获得焦点时，立即触发一次补全请求（可展示热门搜索词等）
      $(this).on('focus', function() {
        this.request();
      });

      // 输入框失去焦点时，延迟 200ms 隐藏下拉菜单
      // 延迟目的：等待用户点击下拉项事件先触发，避免菜单在点击前就消失
      $(this).on('blur', function() {
        setTimeout(function(object) {
          object.hide();
        }, 200, this);
      });

      // 监听键盘事件：ESC 键直接隐藏下拉菜单；其他按键触发新的补全请求
      $(this).on('keydown', function(event) {
        switch(event.keyCode) {
          case 27: // ESC 键：关闭下拉菜单
            this.hide();
            break;
          default:
            this.request();
            break;
        }
      });

      // 下拉菜单中某一项被点击时的处理函数
      // 从被点击的 <li> 元素上读取 data-value，查找缓存的完整数据对象，
      // 然后调用用户传入的 select 回调
      this.click = function(event) {
        event.preventDefault();

        let value = $(event.target).parent().attr('data-value');

        if (value && this.items[value]) {
          this.select(this.items[value]);
        }
      }

      // 将下拉菜单定位到输入框正下方并显示
      this.show = function() {
        var pos = $(this).position();

        $(this).siblings('ul.dropdown-menu').css({
          top: pos.top + $(this).outerHeight(),
          left: pos.left
        });

        $(this).siblings('ul.dropdown-menu').show();
      }

      // 隐藏下拉菜单
      this.hide = function() {
        $(this).siblings('ul.dropdown-menu').hide();
      }

      // 防抖请求：清除上次的定时器后，等待 200ms 再调用 source 函数获取补全数据
      // source 是用户在初始化插件时传入的数据获取函数，由主题开发者实现接口调用逻辑
      this.request = function() {
        clearTimeout(this.timer);

        this.timer = setTimeout(function(object) {
          // 将当前输入框的值和 response 回调传给 source，由 source 调用接口后回传数据
          object.source($(object).val(), $.proxy(object.response, object));
        }, 200, this);
      }

      // 处理 source 返回的 JSON 数据，构建下拉列表 HTML 并渲染
      // json 格式：[{ value, label, category? }, ...]
      //   value    - 条目唯一标识（提交值）
      //   label    - 展示文本
      //   category - 所属分类名（可选），有分类的项会按分类分组展示
      this.response = function(json) {
        // 若输入框已失去焦点，则不渲染结果（防止 blur 后数据异步返回导致菜单意外弹出）
        let hasFocus = $(this).is(':focus');
        if (!hasFocus) return;

        var html = '';

        if (json.length) {
          // 将所有数据对象以 value 为键缓存，供点击时查找完整对象
          for (var i = 0; i < json.length; i++) {
            this.items[json[i]['value']] = json[i];
          }

          // 优先渲染不带分类（category 为空）的条目，直接显示为普通列表项
          for (var i = 0; i < json.length; i++) {
            if (!json[i]['category']) {
              html += '<li data-value="' + json[i]['value'] + '"><a href="#" class="dropdown-item">' + json[i]['label'] + '</a></li>';
            }
          }

          // 将带分类的条目按 category 字段归组
          var category = new Array();

          for (var i = 0; i < json.length; i++) {
            if (json[i]['category']) {
              if (!category[json[i]['category']]) {
                category[json[i]['category']] = new Array();
                category[json[i]['category']]['name'] = json[i]['category'];
                category[json[i]['category']]['item'] = new Array();
              }

              category[json[i]['category']]['item'].push(json[i]);
            }
          }

          // 按分类渲染：先输出分类标题（dropdown-header），再输出该分类下的所有条目
          for (var i in category) {
            html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

            for (j = 0; j < category[i]['item'].length; j++) {
              html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
            }
          }
        }

        // 有数据时显示下拉菜单，无数据时隐藏（避免展示空菜单）
        if (html) {
          this.show();
        } else {
          this.hide();
        }

        // 将构建好的列表 HTML 填充到下拉菜单容器中
        $(this).siblings('ul.dropdown-menu').html(html);
      }

      // 在输入框后面插入空的下拉菜单容器
      $(this).after('<ul class="dropdown-menu"></ul>');
      // 使用事件委托绑定下拉菜单中所有 <a> 标签的点击事件，并将 this 绑定到 input 实例
      $(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));
    });
  }
});