{{--
===========================================================================
【文件说明】
  商品视频播放器组件，支持两种视频类型：
    1. 自定义/本地视频（custom / local）：使用 Video.js 播放器渲染 <video> 标签
    2. iframe 内嵌视频（iframe）：动态注入 iframe HTML（适用于 YouTube / Bilibili 等）
  本组件作为绝对定位浮层覆盖在主图区之上（z-index: 1000），
  通过 .open-video 触发播放，.close-video 关闭并恢复主图。
  由 products/components/_images.blade.php 引入（桌面主图区和移动端各引入一次）。

【来源视图】
  products/components/_images.blade.php

【可用变量】（继承自父视图）
  $product       — Product 模型
    $product->video : array|null  视频配置数组，结构：
      [
        'type'   => 'custom' | 'iframe' | 'local',
        'custom' => '视频文件 URL（type=custom 时使用）',
        'iframe' => '<iframe ...> HTML 字符串（type=iframe 时使用）',
        'url'    => '视频文件 URL（type=local 时使用）',
      ]

【Sections / Blocks】
  footer — 追加（@push）Video.js 或 iframe 的初始化与控制脚本
           注意：此组件被引入两次（桌面+移动端），JS 中已做 typeof 检查防止重复初始化

【关键 CSS 类说明】
  .video-wrap       — 视频浮层容器（position: absolute，默认 .d-none 隐藏）
  #product-video    — Video.js 播放器元素 或 iframe 容器 <div>
  .close-video      — 关闭按钮（右上角 ×，默认 .d-none 隐藏）
  .open-video       — 播放触发元素（可出现在多处：缩略图悬浮层、移动端轮播）

【JS 全局函数（@push footer 中定义）】
  Video.js 模式（type != 'iframe'）:
    window.pVideo          — Video.js 播放器实例（全局，用于外部调用 pause/play）
    initVideoPlayer()      — 初始化 Video.js 并绑定事件
    bindVideoEvents()      — 绑定 .open-video 和 .close-video 的点击事件
    playVideo()            — 显示浮层并播放视频，处理 Promise 异常
    closeVideo()           — 暂停并隐藏视频浮层
    showPlayButton()       — 显示播放图标浮层
    hidePlayButton()       — 隐藏播放图标浮层
    showCloseButton()      — 显示关闭按钮
    hideCloseButton()      — 隐藏关闭按钮

  iframe 模式（type == 'iframe'）:
    window.iframeVideoContent — 存储 iframe HTML 字符串（防重复声明）
    initIframeVideo()         — 将 iframe HTML 注入 #product-video 并配置样式
    bindVideoEvents()         — 绑定播放/关闭事件
    closeIframeVideo()        — 清空 iframe 内容并隐藏浮层（避免视频后台继续播放）

【依赖资源】（在 products/show.blade.php 的 @push('header') 中加载）
  vendor/video-js/video.min.js
  vendor/video-js/video-js.css

【自定义建议】
  - 修改 Video.js 播放器皮肤：在父视图的 header 区域覆盖 video-js CSS 变量。
  - 支持更多视频类型（如腾讯视频）：在 @php switch($videoType) 中增加 case 分支并生成对应 iframe URL。
  - 修改视频浮层背景透明度：调整 .video-wrap 的 background 属性（当前 bg-dark）。
  - 如需自动播放：在 <video> 标签添加 autoplay 属性（注意浏览器策略需配合 muted）。
===========================================================================
--}}
@if ($product->video)
  @php
    $videoData = $product->video ?? [];
    $videoType = $videoData['type'] ?? 'custom';
    $videoUrl = '';
    $isIframe = false;
    
    switch($videoType) {
      case 'custom':
        $videoUrl = $videoData['custom'] ?? '';
        break;
      case 'iframe':
        $videoUrl = $videoData['iframe'] ?? '';
        $isIframe = true;
        break;
      case 'local':
        $videoUrl = $videoData['url'] ?? '';
        break;
      default:
        $videoUrl = $videoData['url'] ?? '';
        break;
    }
  @endphp

  @if($videoUrl)
    <div class="video-wrap position-absolute top-0 start-0 w-100 h-100 bg-dark d-none" style="z-index: 1000;">
      @if (!$isIframe)
      <video
        id="product-video"
        class="video-js vjs-big-play-centered w-100 h-100"
        controls loop muted
      >
        <source src="{{ $videoUrl }}" type="video/mp4" />
      </video>
      @else
      <div id="product-video" class="w-100 h-100 d-flex align-items-center justify-content-center"></div>
      @endif
      <div class="close-video position-absolute top-0 end-0 m-3 d-none">
        <i class="bi bi-x-circle fs-3 text-white bg-dark bg-opacity-50 rounded-circle p-2"></i>
      </div>

    </div>

    @push('footer')
    @if (!$isIframe)
      <script>
        // Video.js player instance
        if (typeof window.pVideo === 'undefined') {
          window.pVideo = null;
        }

        // Initialize video player and bind events
        $(function () {
          initVideoPlayer();
        });

        /**
         * Initialize video player and setup events
         */
        function initVideoPlayer() {
          if (!$('#product-video').length) {
            return;
          }
          
          window.pVideo = videojs("product-video");
          
          // Show play button when video metadata is loaded
          window.pVideo.on('loadedmetadata', function() {
            showPlayButton();
          });
          
          // Listen for pause event - keep video visible when paused
          window.pVideo.on('pause', function() {
            showPlayButton();
            // Keep close button visible
          });
          
          bindVideoEvents();
        }

        /**
         * Bind video control events
         */
        function bindVideoEvents() {
          $(document)
            .on('click', '.open-video', function(e) {
              playVideo();
            })
            .on('click', '.close-video', closeVideo);
        }

        /**
         * Start video playback
         */
        function playVideo() {
          // Show video container
          $('.video-wrap').removeClass('d-none');
          
          if (!window.pVideo) {
            initVideoPlayer();
            // Delay playback to wait for initialization
            setTimeout(function() {
              if (window.pVideo) {
                const playPromise = window.pVideo.play();
                
                if (playPromise !== undefined) {
                  playPromise.then(() => {
                    window.pVideo.currentTime(0);
                    hidePlayButton();
                    $('#product-video').fadeIn();
                    showCloseButton();
                  }).catch(error => {
                    showPlayButton();
                  });
                } else {
                  window.pVideo.currentTime(0);
                  hidePlayButton();
                  $('#product-video').fadeIn();
                  showCloseButton();
                }
              }
            }, 500);
            return;
          }
          
          // Try to play video and handle possible errors
          const playPromise = window.pVideo.play();
          
          if (playPromise !== undefined) {
            playPromise.then(() => {
              window.pVideo.currentTime(0);
              hidePlayButton();
              $('#product-video').fadeIn();
              showCloseButton();
            }).catch(error => {
              // If autoplay fails, show play button for manual click
              showPlayButton();
            });
          } else {
            // For older browsers that don't return a Promise from play()
            window.pVideo.currentTime(0);
            hidePlayButton();
            $('#product-video').fadeIn();
            showCloseButton();
          }
        }

        /**
         * Stop video playback and hide player
         */
        function closeVideo() {
          if (!window.pVideo) return;
          
          window.pVideo.pause();
          $('#product-video').fadeOut();
          $('.video-wrap').addClass('d-none');
          hideCloseButton();
          
          const isVideoActive = $('.thumbnail-item.active[data-is-video="true"]').length > 0;
          if (isVideoActive) {
            $('.main-product-img .video-play-overlay').removeClass('d-none');
          }
        }

        /**
         * Show video play button
         */
        function showPlayButton() {
          // Show mobile play button
          $('.video-wrap .open-video').removeClass('d-none');
          // Show desktop play button only when video is active
          const isVideoActive = $('.thumbnail-item.active[data-is-video="true"]').length > 0 || 
                               ($('#mobile-product-swiper .swiper-slide-active').find('[data-is-video="true"]').length > 0);
          
          if (isVideoActive) {
            $('.main-product-img .video-play-overlay').removeClass('d-none');
          }
          
          // Ensure desktop play button is visible
          const $desktopPlayButton = $('.main-product-img .video-play-overlay');
          if (isVideoActive && $desktopPlayButton.hasClass('d-none')) {
            $desktopPlayButton.removeClass('d-none');
          }
        }

        /**
         * Hide video play button
         */
        function hidePlayButton() {
          // Hide mobile play button
          $('.video-wrap .open-video').addClass('d-none');
          // Hide desktop play button
          $('.main-product-img .video-play-overlay').addClass('d-none');
        }

        /**
         * Show video close button
         */
        function showCloseButton() {
          $('.close-video').removeClass('d-none');
        }

        /**
         * Hide video close button
         */
        function hideCloseButton() {
          $('.close-video').addClass('d-none');
        }
      </script>
    @else
      <script>
        // Iframe video content - check if already declared
        if (typeof window.iframeVideoContent === 'undefined') {
          window.iframeVideoContent = '{!! $videoUrl !!}';
        }
        
        // Initialize iframe video player
        $(function() {
          initIframeVideo();
        });

        /**
         * Initialize iframe video player and setup events
         */
        function initIframeVideo() {
          $('#product-video').html(iframeVideoContent);
          
          // Auto-configure iframe styles and display
          const $iframe = $('#product-video iframe');
          $iframe.attr({
            width: '100%',
            height: '100%'
          }).css({
            'max-width': '100%',
            'max-height': '100%',
            'object-fit': 'contain'
          });
          
          // Show close button
          showCloseButton();
          bindVideoEvents();
        }

        /**
         * Bind video control events
         */
        function bindVideoEvents() {
          $(document)
            .on('click', '.open-video', function(e) {
              initIframeVideo();
              $('.video-wrap').removeClass('d-none');
              hidePlayButton();
              showCloseButton();
            })
            .on('click', '.close-video', closeIframeVideo);
        }

        /**
         * Stop iframe video playback and hide player
         */
        function closeIframeVideo() {
          $('#product-video').fadeOut();
          $('#product-video').html('');
          $('.video-wrap').addClass('d-none');
          hideCloseButton();
          showPlayButton();
        }

        /**
         * Show video play button
         */
        function showPlayButton() {
          // Show mobile play button
          $('.video-wrap .open-video').removeClass('d-none');
          // Show desktop play button only when video is active
          const isVideoActive = $('.thumbnail-item.active[data-is-video="true"]').length > 0 || 
                               ($('#mobile-product-swiper .swiper-slide-active').find('[data-is-video="true"]').length > 0);
          
          if (isVideoActive) {
            $('.main-product-img .video-play-overlay').removeClass('d-none');
          }
          
          // Ensure desktop play button is visible
          const $desktopPlayButton = $('.main-product-img .video-play-overlay');
          if (isVideoActive && $desktopPlayButton.hasClass('d-none')) {
            $desktopPlayButton.removeClass('d-none');
          }
        }

        /**
         * Hide video play button
         */
        function hidePlayButton() {
          // Hide mobile play button
          $('.video-wrap .open-video').addClass('d-none');
          // Hide desktop play button
          $('.main-product-img .video-play-overlay').addClass('d-none');
        }

        /**
         * Show video close button
         */
        function showCloseButton() {
          $('.close-video').removeClass('d-none');
        }

        /**
         * Hide video close button
         */
        function hideCloseButton() {
          $('.close-video').addClass('d-none');
        }
      </script>
    @endif
    @endpush
  @endif
@endif