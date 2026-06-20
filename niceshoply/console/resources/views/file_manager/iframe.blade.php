@extends('console::layouts.blank')

@section('title', __('console/menu.file_manager'))

@prepend('header')
  <meta name="api-token" content="{{ session('console_api_token') }}">
  <script>
    window.fileManagerConfig = Object.freeze({
      driver: '{{ $config['driver'] }}',
      endpoint: '{{ $config['endpoint'] }}',
      bucket: '{{ $config['bucket'] }}',
      baseUrl: '{{ $config['baseUrl'] }}',
      multiple: {{ $multiple ? 'true' : 'false' }},
      type: '{{ $type }}',
      uploadMaxFileSize: '{{ $uploadMaxFileSize ?? "unknown" }}',
      postMaxSize: '{{ $postMaxSize ?? "unknown" }}'
    });
    console.log('File manager config initialized in iframe:', window.fileManagerConfig);
  </script>
@endprepend

@section('page-bottom-btns')
  <div class="page-bottom-btns" id="bottom-btns">
    <button class="btn btn-primary" @click="handleConfirm">{{ __('console/file_manager.select_submit') }}</button>
  </div>
@endsection

@push('header')
  {{-- iframe 选择器模式使用 blank 布局，需自行引入 Vue 3 + Element Plus（与主布局一致） --}}
  <link rel="stylesheet" href="{{ asset('vendor/element-plus/index.css') }}">
  <script src="{{ asset('vendor/vue/3.5/vue.global' . (config('app.debug') ? '' : '.prod') . '.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/index.full.js') }}"></script>
  <script src="{{ asset('vendor/element-plus/icons.min.js') }}"></script>
  <style>
    body {
      display: flex;
      flex-direction: column;
      height: 100vh;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }

    /* 主内容区域 */
    .content-wrapper {
      overflow: hidden;
      position: relative;
    }

    /* 文件管理器内容区域 */
    .file-manager {
      height: 100%;
      display: flex;
      flex-direction: column;
    }

    /* 文件列表区域 */
    .file-list {
      flex: 1;
      overflow-y: auto;
      padding: 20px;
    }

    /* 底部按钮固定在底部 */
    .page-bottom-btns {
      height: 60px;
      padding: 10px;
      background: #fff;
      border-top: 1px solid #EBEEF5;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      z-index: 10;
    }

    /* 左侧文件夹树 */
    .folder-tree {
      height: 100%;
      border-right: 1px solid #EBEEF5;
      overflow-y: auto;
    }

    /* 工具栏样式 */
    .file-toolbar {
      padding: 15px 20px;
      border-bottom: 1px solid #EBEEF5;
      background: #fff;
      position: relative;
      z-index: 10;
    }
  </style>
@endpush

@push('footer')
  <script>
    // 底部确认按钮（独立挂载于 #bottom-btns，调用主文件管理器实例的 confirmSelection）
    (function () {
      const btnApp = Vue.createApp({
        methods: {
          handleConfirm() {
            if (window.fileManagerApp && typeof window.fileManagerApp.confirmSelection === 'function') {
              window.fileManagerApp.confirmSelection();
            }
          }
        }
      });
      btnApp.use(ElementPlus);
      btnApp.mount('#bottom-btns');
    })();

    // 从父窗口获取 token
    window.getApiToken = () => {
      const token = window.parent?.document.querySelector('meta[name="api-token"]')?.getAttribute('content');
      console.log('Parent token:', token);
      return token;
    };
  </script>
@endpush

@section('content')
<div class="content-wrapper">
  @include('console::file_manager.main')
</div>
@endsection
