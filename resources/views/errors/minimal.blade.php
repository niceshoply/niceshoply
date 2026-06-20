<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset=utf-8/>
  <meta name="generator" content="NiceShoply {{ niceshoply_version() }}">
  <title>@yield('title')</title>
  <style>
      * { margin: 0; padding: 0; box-sizing: border-box; }
      body {
          background-color: #f8f9fa;
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
          font-size: 14px;
          color: #3c3c3c;
          min-height: 100vh;
      }

      .error-page {
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          min-height: 100vh;
          padding: 40px 20px;
      }

      .error-code {
          font-size: 140px;
          font-weight: 700;
          line-height: 1;
          letter-spacing: 5px;
          color: #dee2e6;
          text-align: center;
          margin-bottom: 10px;
      }

      .error-message {
          text-align: center;
          color: #6c757d;
          font-size: 18px;
          line-height: 1.6;
          margin-bottom: 24px;
      }

      .btn-back-home {
          display: inline-block;
          padding: 10px 28px;
          text-align: center;
          background: #dc3545;
          color: #fff;
          text-decoration: none;
          border-radius: 6px;
          font-size: 14px;
          font-weight: 500;
          transition: all .2s ease;
      }

      .btn-back-home:hover {
          background: #c82333;
          transform: translateY(-1px);
          box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
      }

      .btn-back-home:active {
          transform: translateY(0);
          box-shadow: none;
      }

      /* Debug Panel Styles */
      .debug-panel {
          width: 100%;
          max-width: 1100px;
          margin: 30px auto 0;
          text-align: left;
      }

      .debug-toggle {
          display: inline-flex;
          align-items: center;
          gap: 8px;
          padding: 6px 14px;
          background: #fff3cd;
          border: 1px solid #ffc107;
          border-radius: 6px;
          color: #856404;
          font-size: 12px;
          font-weight: 600;
          cursor: pointer;
          margin-bottom: 16px;
          user-select: none;
          transition: all .2s;
      }

      .debug-toggle:hover { background: #ffe69c; }

      .debug-toggle .arrow {
          display: inline-block;
          transition: transform .2s;
          font-size: 10px;
      }

      .debug-toggle.open .arrow { transform: rotate(90deg); }

      .debug-card {
          background: #fff;
          border: 1px solid #e9ecef;
          border-radius: 8px;
          overflow: hidden;
          box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      }

      .debug-card-header {
          background: #f8f9fa;
          padding: 14px 20px;
          font-weight: 600;
          font-size: 14px;
          color: #495057;
          border-bottom: 1px solid #e9ecef;
          display: flex;
          align-items: center;
          gap: 8px;
      }

      .debug-card-header .badge {
          display: inline-block;
          padding: 2px 8px;
          border-radius: 4px;
          font-size: 11px;
          font-weight: 700;
          color: #fff;
      }

      .badge-danger { background: #dc3545; }
      .badge-warning { background: #ffc107; color: #856404; }
      .badge-info { background: #17a2b8; }

      .debug-section {
          padding: 16px 20px;
          border-bottom: 1px solid #f1f3f5;
      }

      .debug-section:last-child { border-bottom: none; }

      .debug-label {
          font-size: 11px;
          font-weight: 700;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          color: #adb5bd;
          margin-bottom: 6px;
      }

      .debug-value {
          font-family: 'SF Mono', 'Fira Code', 'Fira Mono', Menlo, Consolas, monospace;
          font-size: 13px;
          color: #212529;
          word-break: break-all;
      }

      .debug-value.error-msg { color: #dc3545; font-weight: 600; }

      .debug-trace {
          background: #1e1e2e;
          color: #cdd6f4;
          padding: 16px 20px;
          font-family: 'SF Mono', 'Fira Code', 'Fira Mono', Menlo, Consolas, monospace;
          font-size: 12px;
          line-height: 1.7;
          max-height: 500px;
          overflow: auto;
          white-space: pre-wrap;
          word-break: break-all;
          border-radius: 0 0 8px 8px;
      }

      .debug-trace .trace-line { color: #a6adc8; }
      .debug-trace .trace-line:hover { background: rgba(255,255,255,0.05); }
      .debug-trace .trace-file { color: #89b4fa; }
      .debug-trace .trace-num { color: #6c7086; min-width: 30px; display: inline-block; }

      .debug-env-badge {
          display: inline-block;
          padding: 2px 6px;
          border-radius: 3px;
          font-size: 10px;
          font-weight: 700;
          text-transform: uppercase;
          background: #fff3cd;
          color: #856404;
          border: 1px solid #ffc107;
          margin-left: auto;
      }
  </style>
</head>
<body>
<div class="error-page">
  @php
    $status = \Illuminate\Support\Facades\View::getSection('code');
    $digits = str_split($status ?: '500');
    $isDebug = config('app.debug')
        || request()->is('install')
        || request()->is('install/*');

    // Try multiple sources to get the real exception
    $debugException = null;
    if ($isDebug) {
        if (isset($exception) && $exception instanceof \Throwable) {
            $debugException = $exception;
        } elseif (app()->bound('_debug_exception')) {
            $debugException = app('_debug_exception');
        }
        // Unwrap HttpException to get the original exception with full trace
        if ($debugException && $debugException->getPrevious()) {
            $originalException = $debugException->getPrevious();
        } else {
            $originalException = $debugException;
        }
    }
  @endphp

  <div class="error-code">
    @foreach($digits as $digit){{ $digit }}@endforeach
  </div>

  <p class="error-message">@yield('message')</p>
  <a href="javascript:history.back()" class="btn-back-home">{{ __('front/common.back_page') }}</a>

  @if($isDebug && $debugException)
    <div class="debug-panel">
      <div class="debug-toggle open" onclick="var p=this.nextElementSibling;p.style.display=p.style.display==='none'?'block':'none';this.classList.toggle('open')">
        <span class="arrow">&#9654;</span>
        DEBUG MODE &mdash; Exception Details
        <span class="debug-env-badge">{{ app()->environment() }}</span>
      </div>
      <div class="debug-card">
        <div class="debug-card-header">
          <span class="badge badge-danger">{{ $status }}</span>
          {{ get_class($originalException ?? $debugException) }}
        </div>

        <div class="debug-section">
          <div class="debug-label">Error Message</div>
          <div class="debug-value error-msg">{{ ($originalException ?? $debugException)->getMessage() ?: 'No message' }}</div>
        </div>

        <div class="debug-section">
          <div class="debug-label">File &amp; Line</div>
          <div class="debug-value">{{ ($originalException ?? $debugException)->getFile() }}:{{ ($originalException ?? $debugException)->getLine() }}</div>
        </div>

        @if(($originalException ?? $debugException)->getCode())
          <div class="debug-section">
            <div class="debug-label">Exception Code</div>
            <div class="debug-value">{{ ($originalException ?? $debugException)->getCode() }}</div>
          </div>
        @endif

        @if($originalException && $originalException !== $debugException)
          <div class="debug-section">
            <div class="debug-label">Wrapped By</div>
            <div class="debug-value">{{ get_class($debugException) }}: {{ $debugException->getMessage() }}</div>
          </div>
        @endif

        <div class="debug-section">
          <div class="debug-label">Request</div>
          <div class="debug-value">{{ request()->method() }} {{ request()->fullUrl() }}</div>
        </div>

        <div class="debug-trace">@foreach(array_slice(($originalException ?? $debugException)->getTrace(), 0, 50) as $i => $frame)<div class="trace-line"><span class="trace-num">#{{ $i }}</span> <span class="trace-file">{{ $frame['file'] ?? 'internal' }}</span>:{{ $frame['line'] ?? '?' }} {{ $frame['class'] ?? '' }}{{ $frame['type'] ?? '' }}{{ $frame['function'] ?? '' }}()</div>@endforeach</div>
      </div>
    </div>
  @endif
</div>
</body>
</html>