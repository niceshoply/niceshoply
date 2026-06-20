{{--
===========================================================================
【文件说明】
  子分类导航区域局部模板，展示当前分类的直接子分类列表（网格卡片）。
  仅当存在活跃子分类时才渲染，位于分类简介（_intro）下方、商品控件（_controls）上方。
  由 categories/show.blade.php 通过 @include('categories.partials._subcategories') 引入。

【来源视图】
  categories/show.blade.php

【传入变量】
  $category  — Category 模型  当前分类对象
    $category->activeChildren : Collection  活跃子分类集合（Category 模型），
                                            通过 scope 或关联过滤已禁用/隐藏的分类
      $subcategory->url            : string      子分类详情页 URL
      $subcategory->fallbackName('name') : string  子分类名称（多语言 fallback）
      $subcategory->image          : string|null 子分类图片路径
      $subcategory->products_count : int|null    子分类下的商品数量（关联计数）

【辅助函数】
  image_resize($path, 48, 48) — 生成 48×48 子分类图标图 URL

【关键 CSS 类说明】
  .subcategories-section    — 子分类区块外层容器
  .subcategory-card         — 单个子分类卡片（flex 布局，含 hover 动效）
  .subcategory-image        — 子分类图标容器（48×48 圆角图，可选）
  .subcategory-name         — 子分类名称文本
  .subcategory-count        — 商品数量提示（products_count > 0 时显示）
  .hover-shadow             — 基础阴影类（transition: all 0.3s）
  .transition-all           — 过渡动画辅助类

【内联样式说明】
  .subcategory-card:hover — 鼠标悬停时上移 2px + 增大阴影 + 边框变为主色
  Bootstrap .col-6 col-md-4 col-lg-3 — 响应式列宽（移动端2列，平板3列，桌面4列）

【Sections / Blocks】
  无（包含内联 <style> 块，用于定义 hover 动效）

【插件钩子】
  无

【自定义建议】
  - 修改子分类列数：调整 col-6 col-md-4 col-lg-3 栅格类。
  - 隐藏商品数量：移除或注释 @if($subcategory->products_count > 0) 整块。
  - 修改图标尺寸：调整 image_resize 参数，同步调整 CSS 中的 width/height（当前 48px）。
  - 启用 eager loading：确保控制器中 Category::with(['activeChildren.media']) 预加载子分类图片，
    避免 N+1 查询。
  - 将内联 <style> 移入主题 CSS 文件，便于全局管理和缓存。
===========================================================================
--}}
{{-- Subcategories section --}}
@if($category->activeChildren && $category->activeChildren->count() > 0)
  <div class="subcategories-section mb-3">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-3">
        <h4 class="h6 fw-bold mb-2 text-secondary">
          <i class="bi bi-grid-3x3-gap me-2"></i>
          {{ __('front/category.subcategories') }}
        </h4>
        
        <div class="row g-2">
          @foreach($category->activeChildren as $subcategory)
            <div class="col-6 col-md-4 col-lg-3">
              <a href="{{ $subcategory->url }}" class="text-decoration-none">
                <div class="subcategory-card h-100 border rounded-2 p-2 hover-shadow transition-all d-flex align-items-center">
                  @if($subcategory->image)
                    <div class="subcategory-image me-3 flex-shrink-0">
                      <img src="{{ image_resize($subcategory->image, 48, 48) }}" 
                           alt="{{ $subcategory->fallbackName('name') }}"
                           class="img-fluid rounded-2" 
                           style="width: 48px; height: 48px; object-fit: cover;">
                    </div>
                  @endif
                  
                  <div class="subcategory-content flex-grow-1 {{ $subcategory->image ? 'text-start' : 'text-center' }}">
                    <div class="subcategory-name">
                      <span class="fw-medium text-dark" style="font-size: 0.85rem; line-height: 1.3;">{{ $subcategory->fallbackName('name') }}</span>
                    </div>
                    
                    @if($subcategory->products_count > 0)
                      <div class="subcategory-count mt-1">
                        <small class="text-muted" style="font-size: 0.75rem;">
                          <i class="bi bi-box-seam me-1" style="font-size: 0.7rem;"></i>
                          {{ $subcategory->products_count }} {{ __('front/category.products_count') }}
                        </small>
                      </div>
                    @endif
                  </div>
                </div>
              </a>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
@endif

<style>
.subcategory-card {
  transition: all 0.3s ease;
  background: #fff;
}

.subcategory-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
  border-color: var(--bs-primary) !important;
}

.hover-shadow {
  box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.transition-all {
  transition: all 0.3s ease;
}
</style>