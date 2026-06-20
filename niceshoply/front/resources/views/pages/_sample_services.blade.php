{{--
  ============================================================
  【文件说明】
    CMS「服务介绍」页面的自定义示例模板。
    左侧配图，右侧展示服务总述；下方以六宫格展示各项具体服务
    （开源系统、插件市场、定制开发、安装维护、技术培训等）。
    此文件是内容片段，由控制器渲染后通过 pages/show.blade.php 的 {!! $result !!} 输出。

  【触发场景】
    后台将某个 CMS 页面（如「我们的服务」页）的模板路径配置为此文件时渲染。

  【对应关系】
    作为 $result 内容注入 pages/show.blade.php，不直接继承布局，不可单独访问。

  【可用变量】
    此模板为静态示例，无动态变量。
    如需动态化，可在控制器中传入：
      - $services — 服务对象集合（含 icon、title、description 等字段）

  【结构说明】
    .page-service-content — 根容器
    .service-icon         — 左侧服务展示图（asset 引入本地图片）
    .title-box            — 右侧标题区块（主标题 + 总描述）
    .service-item         — 单个服务卡片（图标 + 标题 + 描述）
    .service-row-2        — 第二行服务区（三列排布）

  【辅助函数】
    asset('images/front/service/bg-1.png') — 读取主题静态资源图片

  【自定义建议】
    - 替换静态服务内容为实际业务服务项目
    - 可将服务数据存入数据库循环渲染，支持后台动态管理
    - 图标可改为自定义 SVG 图标或彩色 Icon Font
    - 可为每个服务项增加「立即咨询」按钮，跳转联系我们页面
    - 左侧图片可替换为展示视频（video 标签）以增强视觉冲击力
  ============================================================
--}}
<div class="page-service-content">
  <div class="container">
    <div class="row">
      <div class="col-12 col-md-5">
        <div class="service-icon"><img src="{{ asset('images/front/service/bg-1.png') }}" class="img-fluid"></div>
      </div>
      <div class="col-12 col-md-7">
        <div class="row">
          <div class="col-12">
            <div class="title-box">
              <div class="title">我们的服务</div>
              <div class="sub-title">
                我们不仅提供定制化的解决方案，还以专业的技术知识、创新的思维方式和全方位的支持，确保您能够享受到卓越而高效的服务体验。我们承诺，无论您的需求如何变化，我们都能为您提供最匹配的专业服务。
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="service-item">
              <div class="icon"><i class="bi bi-house-door-fill"></i></div>
              <div class="title">开源系统</div>
              <div class="sub-title">
                致力于提供高度灵活和可定制的解决方案。利用开放源代码的优势，我们帮助企业构建可扩展的系统，同时确保透明度和社区支持。
              </div>
            </div>
          </div>
          <div class="col-12 col-md-6">
            <div class="service-item">
              <div class="icon"><i class="bi bi-house-door-fill"></i></div>
              <div class="title">插件市场</div>
              <div class="sub-title">
                通过我们的插件市场，用户可以轻松扩展其系统功能。我们提供丰富的插件选择，以满足不同的业务需求，让定制化服务触手可及
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12 col-md-1"></div>
      <div class="col-12 col-md-11 service-row-2">
        <div class="row">
          <div class="col-12 col-md-4">
            <div class="service-item">
              <div class="icon"><i class="bi bi-house-door-fill"></i></div>
              <div class="title">定制开发</div>
              <div class="sub-title">
                专注于根据您的具体需求，打造独一无二的软件解决方案。从概念到实现，我们与您紧密合作，确保最终产品超出您的期望。
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="service-item">
              <div class="icon"><i class="bi bi-house-door-fill"></i></div>
              <div class="title">安装维护</div>
              <div class="sub-title">
                我们的安装维护服务确保您的系统运行平稳，通过定期更新和故障排除，我们提供无忧的技术支持，让您专注于核心业务。
              </div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="service-item">
              <div class="icon"><i class="bi bi-house-door-fill"></i></div>
              <div class="title">技术培训</div>
              <div class="sub-title">
                通过我们的技术培训服务，您的团队将获得必要的技能和知识。我们的培训课程旨在提升效率，促进创新，并确保长期的技术自给自足。
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>