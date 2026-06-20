{{--
===========================================================================
【文件说明】
  商品规格选择器组件，展示多维度规格（如颜色×尺寸），点击规格值后自动：
    1. 切换 SKU（价格、货号、型号、库存、图片同步更新）
    2. 禁用无库存的规格值
    3. 更新自定义选项组件的基础价格（调用 window.updateBasePrice()）
    4. 使用 history.pushState 更新 URL 中的 sku_id 参数（不刷新页面）
  由 products/show.blade.php 通过 @include('products.components._variants') 引入。

【来源视图】
  products/show.blade.php

【可用变量】（继承自父视图 + 控制器注入）
  $variants   — array   规格维度数组，结构示例：
                [
                  0 => [
                    'name' => ['zh' => '颜色', 'en' => 'Color'],
                    'values' => [
                      0 => ['name' => ['zh' => '红色', ...], 'image' => 'path/to/img.jpg'],
                      1 => ['name' => ['zh' => '蓝色', ...], 'image' => null],
                    ]
                  ],
                  1 => ['name' => ['zh' => '尺寸', ...], 'values' => [...]]
                ]
  $skus       — array   全部 SKU 快照数组，每项结构：
                { id, code, model, price, price_format, origin_price_format,
                  quantity, variants: [0, 1, ...], origin_image_url }
                variants 为各维度选中值的索引数组，与 $variants 下标对应
  $sku        — array   当前激活的 SKU 快照（默认 SKU），同 $skus 单项结构

【辅助函数】
  front_locale_code()     — 获取前台当前语言 code（用于显示多语言规格名/值）
  setting_locale_code()   — 获取后台默认语言 code（前台语言无翻译时的 fallback）

【Sections / Blocks】
  footer — 追加（@push）规格切换脚本：SKU 匹配、库存状态更新、DOM 同步

【插件钩子】
  @hookupdate('front.products.show.variants.value') — 可整体替换规格值展示区块的 HTML 结构
                                                       （@hookupdate ... @endhookupdate 包裹）

【关键 CSS 类说明】
  .product-variant-box      — 所有规格的外层容器
  .product-variant          — 单个规格维度容器（如"颜色"行）
  .variant-title            — 规格维度名称
  .variant-values           — 规格值列表容器
  .variant-value-name       — 规格值单项（data-variant=维度索引, data-value=值索引）
                              状态类: .active（已选中）、.disabled（无库存）
  .variant-image-container  — 规格值图片容器（可选，有图时展示）
  .variant-value-image      — 规格值缩略图（30×30）

【JS 逻辑说明（@push footer 中定义）】
  skus                 — 全部 SKU 的 JSON 数据（由 @json($skus) 注入）
  masterSku            — 当前 SKU（初始化为 @json($sku)，点击后动态更新）
  updateVariantStatus() — 根据当前选择，遍历所有规格值，匹配 SKU 库存并切换 .disabled 状态
  click 事件           — 点击规格值：重新匹配 SKU → 更新价格/货号/型号/图片/库存状态 → 同步 URL

【自定义建议】
  - 规格展示样式：修改 .variant-value-name 的 CSS，可做成色块、图片选择器等。
  - 规格图片尺寸：修改 image_resize($value['image'], 30, 30) 中的宽高参数。
  - 若需在规格切换时触发其他逻辑（如价格动效），在 JS click 事件末尾添加自定义代码。
  - @hookupdate 可将整个 .product-variant-box 区块替换为完全自定义的渲染方式。
===========================================================================
--}}
@if (is_array($variants) && count($variants))
  <div class="product-variant-box">
    @hookupdate('front.products.show.variants.value')
    @foreach($variants as $key => $variant)
      <div class="product-variant">
        <div class="variant-title">
            {{ $variant['name'][front_locale_code()] ?? ($variant['name'][setting_locale_code()] ?? '-') }}</div>
          <div class="variant-values">
            @foreach ($variant['values'] as $vk => $value)
              <div class="variant-value-name" data-variant="{{ $key }}" data-value="{{ $vk }}">
                @if(isset($value['image']) && !empty($value['image']))
                  <div class="variant-image-container">
                    <img src="{{ image_resize($value['image'], 30, 30) }}" alt="{{ $value['name'][front_locale_code()] ?? ($value['name'][setting_locale_code()] ?? '-') }}" class="variant-value-image">
                  </div>
                @endif
                <span class="variant-text">{{ $value['name'][front_locale_code()] ?? ($value['name'][setting_locale_code()] ?? '-') }}</span>
              </div>
            @endforeach
        </div>
      </div>
    @endforeach
    @endhookupdate
  </div>
@endif
@push('footer')
  <script>
    let skus = @json($skus ?? []);

    if ($('.product-variant-box').length) {
      let masterSku = @json($sku);

      masterSku.variants.forEach((variant, i) => {
        $('.product-variant-box .product-variant').eq(i).find('.variant-values .variant-value-name').eq(variant).addClass('active');
      });

      updateVariantStatus()

      function updateVariantStatus() {
        $('.product-variant-box .product-variant').each((variant_index, el) => {
          $(el).find('.variant-values .variant-value-name').each((value_index, value) => {
            let masterSkuVariants = masterSku.variants.slice(0);
            masterSkuVariants[variant_index] = value_index;
            let sku = skus.find(sku => sku.variants.join('') === masterSkuVariants.join(''));
            if (sku && sku.quantity > 0) {
              $(value).removeClass('disabled');
            } else {
              $(value).addClass('disabled');
            }
          });
        });

        if (masterSku.quantity * 1 <= 0) {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom.quantity-wrap').addClass('disabled');
          $('.stock-wrap .in-stock').addClass('d-none').siblings('.out-stock').removeClass('d-none');
        } else {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom .quantity-wrap').removeClass('disabled');
          $('.stock-wrap .in-stock').removeClass('d-none').siblings('.out-stock').addClass('d-none');
        }
      }

      $('.product-variant-box .variant-value-name').click(function () {
        const variant = $(this).data('variant');
        const value = $(this).data('value');
        let variants = masterSku.variants.slice(0);
        variants[variant] = value;
        masterSku = skus.find(sku => sku.variants.toString() === variants.toString());

        $('.product-param .sku .value').text(masterSku.code);
        $('.product-param .model .value').text(masterSku.model);
        $('.product-price .price').text(masterSku.price_format);
        $('.product-price .old-price').text(masterSku.origin_price_format);
        $('.product-quantity').data('sku-id', masterSku.id)
        
        // Update option component base price and recalculate total price
        if (typeof window.updateBasePrice === 'function') {
          window.updateBasePrice(masterSku.price);
        }

        if (masterSku.origin_image_url) {
          $('.main-product-img img').attr('src', masterSku.origin_image_url);
        }
        history.pushState({}, '', inno.updateQueryStringParameter(window.location.href, 'sku_id', masterSku.id));

        if (masterSku.quantity * 1 <= 0) {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now,.product-info-bottom.quantity-wrap').addClass('disabled');
          $('.stock-wrap .in-stock').addClass('d-none').siblings('.out-stock').removeClass('d-none');
        } else {
          $('.product-info-bottom .add-cart, .product-info-bottom .buy-now, .product-info-bottom.quantity-wrap').removeClass('disabled');
          $('.stock-wrap .in-stock').removeClass('d-none').siblings('.out-stock').addClass('d-none');
        }

        $(this).addClass('active').siblings().removeClass('active');
        updateVariantStatus()
      });
    }
  </script>
@endpush
