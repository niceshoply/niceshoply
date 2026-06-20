{{--
  ============================================================
  【文件说明】
    CMS「关于我们」页面的自定义示例模板。
    这是一个独立的页面内容片段（非继承 layouts.app），由控制器渲染为 HTML 字符串后
    通过 pages/show.blade.php 中的 {!! $result !!} 输出。
    包含：左图右文的团队介绍区块 + 底部联系方式栏。

  【触发场景】
    后台将某个 CMS 页面的模板路径配置为此文件时，
    用户访问该页面（如 /about）即渲染此内容。

  【对应关系】
    此文件作为 $result 内容注入 pages/show.blade.php，
    不直接继承布局，不可单独访问。

  【可用变量】
    此模板无专属动态变量，内容为静态示例文本。
    如需动态化，可在控制器中向视图传入额外变量，例如：
      - $company  — 公司信息对象（电话、邮箱、微信等）
      - $teamMembers — 团队成员列表

  【结构说明】
    .page-about-content  — 根容器，可在主题 CSS 中定义整体样式
    .about-img           — 左侧企业图片区块
    .about-text          — 右侧文字区块（含多个 about-text-item）
    .about-text-item     — 单条特色介绍（图标 + 标题 + 说明文字）
    .home-contact        — 底部联系方式栏（电话、邮箱、微信三列）

  【辅助函数】
    asset('images/front/about/bg-2.png') — 读取主题静态资源图片

  【自定义建议】
    - 将示例文字替换为实际公司介绍文案
    - 联系电话/邮箱/微信可改为从后台系统配置（system_config()）动态读取
    - 可添加 AOS 动画属性（data-aos="fade-up"）为图片和文字加入滚动入场动画
    - 可增加团队成员卡片网格区块
  ============================================================
--}}
<div class="page-about-content">
  <div class="container">
    <div class="row">
      <div class="col-12 col-md-6">
        <div class="about-img">
          <img src="{{ asset('images/front/about/bg-2.png') }}" class="img-fluid">
        </div>
      </div>
      <div class="col-12 col-md-6">
        <div class="about-text">
          <div class="main-title">创新驱动，专业团队，卓越技术，共创未来。</div>
          <div class="about-text-item">
            <div class="left"><i class="bi bi-check-circle"></i></div>
            <div class="right">
              <div class="title">我们的团队</div>
              <div class="sub-title">
                我们的团队由一群充满激情和创造力的专业人士组成，他们来自不同的背景，但共同拥有对技术的热情和对卓越的追求。我们鼓励团队成员之间的协作与交流，以促进创新思维的碰撞和知识的共享。
              </div>
            </div>
          </div>
          <div class="about-text-item">
            <div class="left"><i class="bi bi-check-circle"></i></div>
            <div class="right">
              <div class="title">办公环境</div>
              <div class="sub-title">
                我们的办公空间设计现代而舒适，旨在激发员工的创造力和提高工作效率。开放式的工作区域促进了团队成员之间的沟通与合作，同时，我们也提供了安静的休息区，供员工在紧张的工作之余放松身心。
              </div>
            </div>
          </div>
          <div class="about-text-item">
            <div class="left"><i class="bi bi-check-circle"></i></div>
            <div class="right">
              <div class="title">技术能力</div>
              <div class="sub-title">
                我们拥有强大的技术实力，团队成员不仅精通最新的编程语言和开发工具，还对人工智能、机器学习、数据分析等前沿技术有着深入的理解和实践经验。我们致力于利用这些技术为用户创造高效、智能的解决方案。
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="home-contact">
    <div class="container">
      <div class="title">如果您需要与我们取得联系，以下是我们的联系方式</div>
      <div class="contact-icon">
        <img src="{{ asset('images/front/home/home-3.png') }}" class="img-fluid" data-aos="fade-up" data-aos-duration="2000">
      </div>
      <div class="row">
        <div class="col-12 col-lg-4">
          <div class="contact-item" data-aos="fade-up" data-aos-duration="2000">
            <div class="icon"><i class="bi bi-telephone-fill"></i></div>
            <div class="right">
              <div class="text-1">联系电话</div>
              <div class="text-2"><i class="bi bi-telephone-fill text-primary"></i> 17828469818</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="contact-item" data-aos="fade-up" data-aos-duration="2000">
            <div class="icon"><i class="bi bi-envelope-fill"></i></div>
            <div class="right">
              <div class="text-1">联系邮箱</div>
              <div class="text-2"><i class="bi bi-envelope-fill text-primary"></i> team@niceshoply.com</div>
            </div>
          </div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="contact-item" data-aos="fade-up" data-aos-duration="2000">
            <div class="icon"><i class="bi bi-wechat"></i></div>
            <div class="right">
              <div class="text-1">微信联系</div>
              <div class="text-2"><i class="bi bi-wechat text-primary"></i> NiceShoply666</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>