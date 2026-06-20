@if(system_setting('cookie_banner_enabled', true) && !request()->cookie('niceshoply_cookie_consent'))
<div id="cookie-consent-banner" class="cookie-consent-banner position-fixed bottom-0 start-0 end-0 bg-dark text-white p-3 shadow-lg" style="z-index: 9999;">
  <div class="container">
    <div class="row align-items-center g-3">
      <div class="col-md-8">
        <div class="fw-semibold mb-1">{{ __('front/cookie.title') }}</div>
        <div class="small opacity-75">{{ __('front/cookie.message') }}
          <a href="{{ front_route('legal.cookie') }}" class="text-white">{{ __('front/cookie.policy_link') }}</a>
        </div>
        <div class="form-check form-check-inline mt-2 small">
          <input class="form-check-input" type="checkbox" checked disabled id="cookie-essential">
          <label class="form-check-label" for="cookie-essential">{{ __('front/cookie.essential') }}</label>
        </div>
        <div class="form-check form-check-inline mt-2 small">
          <input class="form-check-input" type="checkbox" id="cookie-analytics">
          <label class="form-check-label" for="cookie-analytics">{{ __('front/cookie.analytics') }}</label>
        </div>
        <div class="form-check form-check-inline mt-2 small">
          <input class="form-check-input" type="checkbox" id="cookie-marketing">
          <label class="form-check-label" for="cookie-marketing">{{ __('front/cookie.marketing') }}</label>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <button type="button" class="btn btn-outline-light btn-sm me-2" id="cookie-reject-all">{{ __('front/cookie.reject_all') }}</button>
        <button type="button" class="btn btn-light btn-sm" id="cookie-accept-all">{{ __('front/cookie.accept_all') }}</button>
      </div>
    </div>
  </div>
</div>
@push('footer')
<script>
(function () {
  const banner = document.getElementById('cookie-consent-banner');
  if (!banner) return;

  function saveConsent(analytics, marketing) {
    axios.post('{{ front_route('cookie_consent.store') }}', {
      analytics: analytics ? 1 : 0,
      marketing: marketing ? 1 : 0,
    }).then(function () {
      banner.remove();
      @if(current_customer())
      axios.post('{{ front_route('cookie_consent.sync') }}').catch(function () {});
      @endif
    });
  }

  document.getElementById('cookie-accept-all').addEventListener('click', function () {
    saveConsent(true, true);
  });
  document.getElementById('cookie-reject-all').addEventListener('click', function () {
    saveConsent(false, false);
  });
})();
</script>
@endpush
@endif
