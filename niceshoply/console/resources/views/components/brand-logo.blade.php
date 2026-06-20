@php
  $logoUrl = console_logo_url();
  $brandName = system_setting('name', config('app.name', 'NiceShoply'));
@endphp
<span {{ $attributes->merge(['class' => 'brand-logo' . ($logoUrl ? ' brand-logo--image' : '')]) }}>
  @if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $brandName }}">
  @else
    {{ strtoupper(mb_substr($brandName, 0, 1)) }}
  @endif
</span>
