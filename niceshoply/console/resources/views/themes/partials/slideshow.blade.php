{{--
  主题设置 - 首页轮播图配置 partial
  依赖父视图：system_setting('slideshow') 与 locales()
  动态新增行逻辑见 settings.blade.php 中的 addSlide() 脚本。
--}}
<div class="tab-pane fade" id="tab-setting-slideshow">
  <table class="table table-bordered align-middle">
    <thead>
    <th>{{ __('console/common.image') }}</th>
    <th>{{ __('console/common.link') }}</th>
    <th class="text-end" width="100"></th>
    </thead>
    <tbody>
    @foreach (old('slideshow', system_setting('slideshow', [])) as $slide_index => $slide)
      <tr>
        <td>
          <div class="accordion accordion-sm" id="accordion-slideshow-{{ $slide_index }}">
            @foreach (locales() as $locale)
              <div class="accordion-item">
                <h2 class="accordion-header">
                  <button class="accordion-button collapsed" type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#data-locale-{{ $slide_index }}-{{ $locale->code }}"
                          aria-expanded="false"
                          aria-controls="data-locale-{{ $slide_index }}-{{ $locale->code }}">
                    <div class="wh-20 me-2"><img src="{{ image_origin($locale->image) }}"
                                                 class="img-fluid"></div>
                    {{ $locale->name }}
                  </button>
                </h2>
                <div id="data-locale-{{ $slide_index }}-{{ $locale->code }}"
                     class="accordion-collapse collapse"
                     data-bs-parent="#accordion-slideshow-{{ $slide_index }}">
                  <div class="accordion-body">
                    <x-common-form-image title=""
                                         name="slideshow[{{ $slide_index }}][image][{{ $locale->code }}]"
                                         value="{{ $slide['image'][$locale->code] ?? '' }}"/>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </td>
        <td>
          <input type="text" name="slideshow[{{ $slide_index }}][link]" value="{{ $slide['link'] }}"
                 class="form-control">
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">{{ __('console/common.delete') }}</button>
        </td>
      </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
      <td colspan="3" class="text-end">
        <button type="button" class="btn btn-primary" onclick="addSlide(this)">{{ __('console/common.add') }}</button>
      </td>
    </tr>
  </table>
</div>
