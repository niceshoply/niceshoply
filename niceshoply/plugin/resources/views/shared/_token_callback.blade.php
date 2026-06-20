<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('console/plugin.token_callback_title') }}</title>
    <style>
        :root { color-scheme: light dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #eef2ff 0%, #ffffff 100%);
            color: #111827;
        }
        @media (prefers-color-scheme: dark) {
            body { background: linear-gradient(135deg, #1f2937 0%, #111827 100%); color: #f9fafb; }
        }
        .card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 32px 36px;
            max-width: 440px;
            width: calc(100% - 32px);
            text-align: center;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
        }
        @media (prefers-color-scheme: dark) {
            .card { background: #1f2937; border-color: #374151; box-shadow: none; }
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            margin: 0 auto 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            line-height: 1;
        }
        .icon.success { background: #ecfdf5; color: #059669; }
        .icon.error   { background: #fef2f2; color: #dc2626; }
        h1 { font-size: 18px; margin: 0 0 8px; font-weight: 600; }
        p  { margin: 0 0 16px; color: #6b7280; font-size: 14px; line-height: 1.5; }
        @media (prefers-color-scheme: dark) {
            p { color: #9ca3af; }
        }
        code {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            background: #f3f4f6;
            font-size: 12px;
        }
        @media (prefers-color-scheme: dark) {
            code { background: #374151; }
        }
        .hint { font-size: 12px; color: #9ca3af; margin-top: 12px; }
    </style>
</head>
<body>
<div class="card">
    @if($success)
        <div class="icon success">&#10003;</div>
        <h1>
            @if((int) $existed === 1)
                {{ __('console/plugin.token_callback_updated') }}
            @else
                {{ __('console/plugin.token_callback_created') }}
            @endif
        </h1>
        @if(!empty($domain))
            <p>{{ __('console/plugin.token_callback_domain') }}: <code>{{ $domain }}</code></p>
        @endif
        <p class="hint">{{ __('console/plugin.token_callback_closing') }}</p>
    @else
        <div class="icon error">!</div>
        <h1>{{ __('console/plugin.token_callback_failed') }}</h1>
        <p>{{ $error ?: __('console/common.error') }}</p>
        <p class="hint">{{ __('console/plugin.token_callback_closing') }}</p>
    @endif
</div>
<script>
    (function () {
        var payload = {
            type:    'niceshoply:domain-token',
            success: @json($success),
            token:   @json($token),
            existed: @json((int) $existed === 1),
            domain:  @json($domain),
            error:   @json($error)
        };

        try {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(payload, '*');
            }
        } catch (e) {}

        setTimeout(function () {
            try { window.close(); } catch (e) {}
        }, 800);
    })();
</script>
</body>
</html>
