<div class="header-box d-flex justify-content-between align-items-center">
  <div class="d-flex align-items-center">
    <div class="mb-menu d-lg-none me-2"><i class="bi bi-list"></i></div>
    {{-- 后台菜单全局搜索（IMP-08） --}}
    <div class="topbar-search d-none d-lg-flex position-relative" id="menu-search-box">
      <span class="btn p-0 border-0 bg-transparent text-secondary lh-1"><i class="bi bi-search"></i></span>
      <input type="search" id="menu-search-input" autocomplete="off"
             placeholder="{{ __('console/common.search') }} {{ __('console/menu.dashboard') }}…">
      <div id="menu-search-results" class="dropdown-menu shadow-sm w-100 mt-1" style="max-height:360px;overflow-y:auto;"></div>
    </div>
    <script>
      (function () {
        var input   = document.getElementById('menu-search-input');
        var results = document.getElementById('menu-search-results');
        var box     = document.getElementById('menu-search-box');
        if (!input) return;

        var endpoint = "{{ console_route('menu.search') }}";
        var timer    = null;

        function render(items) {
          if (!items || !items.length) {
            results.innerHTML = '<span class="dropdown-item-text text-muted small">{{ __('console/common.no_data') }}</span>';
          } else {
            results.innerHTML = items.slice(0, 30).map(function (it) {
              var kw = it.keywords ? '<span class="text-muted small ms-2">' + it.keywords + '</span>' : '';
              return '<a class="dropdown-item d-flex justify-content-between align-items-center" href="' + it.url + '">' +
                     '<span>' + it.title + '</span>' + kw + '</a>';
            }).join('');
          }
          results.classList.add('show');
        }

        function query(keyword) {
          fetch(endpoint + '?keyword=' + encodeURIComponent(keyword), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
          })
            .then(function (r) { return r.json(); })
            .then(function (res) { render(res.data || []); })
            .catch(function () { results.classList.remove('show'); });
        }

        input.addEventListener('input', function () {
          clearTimeout(timer);
          var kw = input.value.trim();
          timer = setTimeout(function () { query(kw); }, 200);
        });
        input.addEventListener('focus', function () { query(input.value.trim()); });

        document.addEventListener('click', function (e) {
          if (!box.contains(e.target)) results.classList.remove('show');
        });
      })();
    </script>
  </div>
  <div class="d-flex justify-content-end align-items-center right-tool">
    <!-- Language -->
    <div class="header-item dropdown d-none d-lg-flex align-items-center">
      <div class="wh-20 me-2"><img src="{{ image_origin('images/flag/'. console_locale_code().'.png') }}" class="img-fluid"></div>
      <span class="dropdown-toggle" data-bs-toggle="dropdown">
        <span>{{ current_console_locale()['name'] }}</span>
      </span>
      <ul class="dropdown-menu dropdown-menu-end locale-dropdown-menu">
        @foreach (console_locales() as $locale)
        <li>
          <a class="dropdown-item d-flex align-items-center" href="{{ console_route('locale.switch', ['code'=> $locale['code']]) }}">
            <div class="wh-20 me-2"><img src="{{ image_origin($locale['image']) }}" class="img-fluid border"></div>
            {{ $locale['name'] }}
          </a>
        </li>
        @endforeach
      </ul>
    </div>

    <!-- User -->
    <div class="header-item dropdown d-flex align-items-center">
      <span class="dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
        <div class="user-avatar me-2">
          <i class="bi bi-person-circle fs-5"></i>
        </div>
        <div class="user-info d-none d-lg-block">
          <div class="user-name">{{ current_admin()->name }}</div>
        </div>
      </span>
      <ul class="dropdown-menu dropdown-menu-end">
        <li class="dropdown-header">
          <div class="d-flex align-items-center">
            <div class="user-avatar me-2">
              <i class="bi bi-person-circle fs-4"></i>
            </div>
            <div>
              <div class="user-name">{{ current_admin()->name }}</div>
              <div class="user-email small text-muted">{{ current_admin()->email }}</div>
            </div>
          </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item d-flex align-items-center" href="{{ front_route('home.index') }}" target="_blank">
            <i class="bi bi-house me-2"></i>
            <span>{{ __('console/dashboard.frontend') }}</span>
          </a>
        </li>
        <li>
          <a class="dropdown-item d-flex align-items-center" href="{{ console_route('account.index') }}">
            <i class="bi bi-person me-2"></i>
            <span>{{ __('console/dashboard.profile') }}</span>
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item d-flex align-items-center text-danger" href="{{ console_route('logout.index') }}">
            <i class="bi bi-box-arrow-right me-2"></i>
            <span>{{ __('console/dashboard.sign_out') }}</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</div>