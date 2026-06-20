{{--
  主题设置 - 顶部菜单配置 partial
  依赖父视图变量：$categories / $catalogs / $pages / $specials
  从 themes/settings.blade.php 拆分而来，便于复用与维护。
--}}
<div class="tab-pane fade show active" id="tab-setting-header-menu">
  <div class="row">
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('console/menu.categories') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($categories as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_header_categories[]"
                     value="{{ $item['id'] }}"
                     id="header-category-{{ $item['id'] }}" {{ in_array($item['id'], old('menu_header_categories', system_setting('menu_header_categories', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="header-category-{{ $item['id'] }}">{{ $item['name'] }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('console/setting.catalogs') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($catalogs as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_header_catalogs[]"
                     value="{{ $item->id }}"
                     id="header-catalog-{{ $item->id }}" {{ in_array($item->id, old('menu_header_catalogs', system_setting('menu_header_catalogs', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="header-catalog-{{ $item->id }}">{{ $item->fallbackName('title') }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('console/setting.page') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($pages as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_header_pages[]"
                     value="{{ $item->id }}"
                     id="header-page-{{ $item->id }}" {{ in_array($item->id, old('menu_header_pages', system_setting('menu_header_pages', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="header-page-{{ $item->id }}">{{ $item->translation->title }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-3">
      <div class="card">
        <div class="card-header">{{ __('console/setting.specials') }}</div>
        <div class="card-body hp-400 overflow-y-auto">
          @foreach ($specials as $item)
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="menu_header_specials[]"
                     value="{{ $item['type'] }}"
                     id="header-page-{{ $item['type'] }}" {{ in_array($item['type'], old('menu_header_specials', system_setting('menu_header_specials', []) ?: [])) ? 'checked' : '' }}>
              <label class="form-check ps-0"
                     for="header-page-{{ $item['type'] }}">{{ $item['title'] }}</label>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>
