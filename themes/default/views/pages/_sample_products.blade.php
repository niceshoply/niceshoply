{{--
  ============================================================
  【文件说明】
    CMS「产品介绍」页面的自定义示例模板。
    以网格卡片形式展示公司旗下产品线（如 NiceShoply、Pro 版、小程序、APP），
    每个产品含图标、名称和描述文字。
    此文件是内容片段，由控制器渲染后通过 pages/show.blade.php 的 {!! $result !!} 输出。

  【触发场景】
    后台将某个 CMS 页面（如「我们的产品」页）的模板路径配置为此文件时渲染。

  【对应关系】
    作为 $result 内容注入 pages/show.blade.php，不直接继承布局，不可单独访问。

  【可用变量】
    此模板为静态示例，无动态变量。
    如需动态化，可在控制器中传入：
      - $products — 产品对象集合（含 name、icon、description 等字段）

  【结构说明】
    .page-product-content — 根容器
    .title-box            — 标题区块（主标题 + 副标题）
    .product-item         — 单个产品卡片（图标 + 名称 + 内容描述）
    .top                  — 卡片头部（Bootstrap Icon + 产品名）
    .content              — 产品描述文字区

  【自定义建议】
    - 将静态产品内容替换为实际业务产品信息
    - 可将产品数据存入 CMS 自定义字段或数据库，循环渲染
    - 可为每个产品卡片增加「了解更多」链接（跳转到具体产品页）
    - 图标可替换为自定义 SVG 或产品截图
  ============================================================
--}}
<div class="page-product-content">
  <div class="container">
    <div class="title-box">
      <div class="title">我们的产品</div>
      <div class="sub-title">Our Product Range</div>
    </div>
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="product-item">
          <div class="top">
            <div class="left"><i class="bi bi-box-seam-fill"></i></div>
            <div class="name">NiceShoply</div>
          </div>
          <div class="content">
            NiceShoply是一款面向中小企业的电子商务平台，提供一站式在线商店解决方案。它以用户友好的界面和强大的后台管理功能著称，帮助商家轻松管理商品、订单和客户关系。NiceShoply支持多种支付方式，并集成了社交媒体营销工具，助力商家扩大市场影响力。
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="product-item">
          <div class="top">
            <div class="left"><i class="bi bi-box-seam-fill"></i></div>
            <div class="name">NiceShoply Pro</div>
          </div>
          <div class="content">
            NiceShoply Pro是NiceShoply的高级版本，专为需要更高级功能和定制服务的企业设计。除了基础版所有功能外，Pro版本提供高级数据分析、个性化推荐引擎和API集成，以满足更复杂的业务需求。它还包含专业的客户支持和优先更新服务，确保商家能够充分利用平台潜力。
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="product-item">
          <div class="top">
            <div class="left"><i class="bi bi-wechat"></i></div>
            <div class="name">小程序</div>
          </div>
          <div class="content">
            我们的小程序为移动用户提供了便捷的购物体验。它轻量级、易于访问，特别适合快速浏览和购买。小程序与主流社交媒体和通讯工具无缝集成，支持一键分享和邀请朋友，通过社交网络快速传播，增加用户粘性和品牌曝光度。
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="product-item">
          <div class="top">
            <div class="left"><i class="bi bi-phone-fill"></i></div>
            <div class="name">APP</div>
          </div>
          <div class="content">
            我们的App是一款为移动设备优化的应用程序，提供更加丰富和个性化的用户体验。它不仅包含了小程序的所有功能，还增加了个性化推送、增强的搜索功能和更高级的用户互动元素。App的设计注重流畅性和互动性，确保用户在移动设备上也能享受到优质的购物和服务体验。
          </div>
        </div>
      </div>
    </div>
  </div>
</div>