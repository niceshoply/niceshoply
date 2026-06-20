{{--
================================================================================
【文件说明】
  空数据占位符局部模板 —— 当列表页、收藏夹、订单列表等区域没有任何数据时，
  渲染一个居中的空状态插图 + 提示文字，替代空白区域，提升用户体验。

【引用方式】
  @include('shared.no-data')
  @include('shared.no-data', ['text' => '暂无订单记录'])
  @include('shared.no-data', ['text' => __('front/product.no_products')])

【可用变量】
  可选传入（不传时使用默认值）：
    $text                   — 空状态提示文字字符串。
                              不传时默认显示 '没有数据 ~'。
                              支持任意字符串，包括多语言翻译结果。

【输出内容】
  垂直居中 Flex 容器，包含：
  - 空状态插图（no-data-3.svg），最大宽度 300px（.wp-300）
  - 提示文字（.fs-4 .text-secondary），灰色中等字体大小

  插图路径：public/images/icons/no-data-3.svg
  （通过 asset() 函数生成完整 URL，确保多语言子目录下路径正确）

【自定义建议】
  1. 如需更换插图，修改 asset('images/icons/no-data-3.svg') 的路径，
     或传入 $icon 参数让调用方自定义图标：
       {{ asset($icon ?? 'images/icons/no-data-3.svg') }}
  2. 如需为不同场景使用不同插图，可通过传入 $icon 变量区分，
     例如：@include('shared.no-data', ['icon' => 'no-orders.svg', 'text' => '暂无订单'])
  3. 提示文字样式（字体大小、颜色）由 .fs-4 和 .text-secondary 控制，
     可在主题 CSS 中重写这两个类，或改为内联样式。
  4. 该模板已被 shared/articles.blade.php 使用：
       @include('shared.no-data', ['text' => '没有数据 ~'])
================================================================================
--}}
<div class="d-flex align-items-center flex-column py-4">
  <img src="{{ asset('images/icons/no-data-3.svg') }}" class="img-fluid wp-300">
  <span class="fs-4 text-secondary">{{ $text ?? '没有数据 ~' }}</span>
</div>
