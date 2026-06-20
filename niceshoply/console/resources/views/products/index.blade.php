@extends('console::layouts.app')
@section('body-class', 'page-product')
@section('title', __('console/menu.products'))
@section('page-eyebrow', __('console/menu.products'))
@section('page-subtitle', __('console/product.list_subtitle'))

@section('page-title-right')
  <a href="{{ console_route('products.create') }}" class="btn btn-primary"><i class="bi bi-plus-square"></i> {{
  __('console/common.create') }}</a>
@endsection

@section('content')
  @isset($stats)
    <div class="stat-strip">
      <div class="stat-card">
        <div class="stat-label">{{ __('console/product.stat_total') }}</div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
        <div class="stat-trend muted">SPU</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">{{ __('console/product.stat_active') }}</div>
        <div class="stat-value">{{ number_format($stats['active']) }}</div>
        <div class="stat-trend">{{ $stats['total'] ? round($stats['active'] / $stats['total'] * 100) : 0 }}%</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">{{ __('console/product.stat_inactive') }}</div>
        <div class="stat-value">{{ number_format($stats['inactive']) }}</div>
        <div class="stat-trend down">{{ $stats['total'] ? round($stats['inactive'] / $stats['total'] * 100) : 0 }}%</div>
      </div>
      <div class="stat-card">
        <div class="stat-label">{{ __('console/product.stat_sales') }}</div>
        <div class="stat-value">{{ number_format($stats['sales']) }}</div>
        <div class="stat-trend muted">{{ __('console/product.sales') }}</div>
      </div>
    </div>
  @endisset

  <div class="card h-min-600" id="app">
    <div class="card-body">

      <x-console-data-criteria :criteria="$criteria ?? []" :action="console_route('products.index')"/>

      <div class="mb-3 p-3 bg-light rounded border" id="products-toolbar">
        <div class="d-flex d-md-flex flex-column flex-md-row justify-content-md-between align-items-start gap-3">
          @include('console::products.bulk.actions')

          <div class="toolbar-right d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2 gap-md-3">
            <x-console-data-info :paginator="$products ?? null"/>
            <x-console-data-sorter :options="$sortOptions ?? []"/>
          </div>
        </div>
      </div>

      @if ($products->count())
        <div class="table-responsive">
          <table class="table align-middle">
            <thead>
            <tr>
              <th><input class="form-check-input" @click="checkAll" type="checkbox" ref="checkAllBox"></th>
              <th>{{ __('console/common.id') }}</th>
              <th class="wp-100">{{ __('console/common.image') }}</th>
              <th>{{ __('console/common.name') }}</th>
              <th>{{ __('console/product.price') }}</th>
              <th>{{ __('console/product.quantity') }}</th>
              <th>{{ __('console/common.created_and_updated') }}</th>
              <th>{{ __('console/common.active') }}</th>
              @hookinsert('console.product.list.table.header.after')
              <th>{{ __('console/common.actions') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
              <tr>
                <td><input class="form-check-input" type="checkbox" :value="{{ $product->id }}" v-model="checkedIds">
                </td>
                <td>{{ $product->id }}</td>
                <td>
                  <div class="d-flex align-items-center justify-content-center wh-50 border">
                    <a href="{{ $product->url }}" target="_blank">
                      <img src="{{ $product->image_url }}" class="img-fluid"
                           alt="{{ $product->fallbackName() }}">
                    </a>
                  </div>
                </td>
                <td>
                  <a href="{{ $product->url }}" class="text-decoration-none" target="_blank" data-bs-toggle="tooltip"
                     title="{{ $product->fallbackName() }}">
                    {{ sub_string($product->fallbackName(),20) }}
                  </a>
                  @if($product->isMultiple())
                    &nbsp;<span class="text-bg-success px-1">M</span>
                  @endif
                </td>
                <td>{{ currency_format($product->masterSku->price ?? 0) }}</td>
                <td>{{ $product->totalQuantity() }}</td>
                <td>
                    <div class="d-flex flex-column">
                        <div class="small">{{ \Carbon\Carbon::parse($product->created_at)->format('Y-m-d') }}</div>
                        <div class="small text-muted">{{ \Carbon\Carbon::parse($product->updated_at)->format('Y-m-d') }}</div>
                    </div>
                </td>
                <td>@include('console::shared.list_switch', ['value' => $product->active, 'url' =>console_route('products.active', $product->id)])</td>
                @hookinsert('console.product.list.table.row.after', $product)
                <td>
                  <div class="d-flex gap-2">
                    <div>
                      <a href="{{ console_route('products.edit', [$product->id]) }}">
                        <el-button size="small" plain type="primary">{{
                        __('console/common.edit')}}</el-button>
                      </a>
                    </div>
                    <div>
                      <a href="{{ console_route('products.copy', [$product->id]) }}">
                        <el-button size="small" plain type="warning">{{
                        __('console/common.copy')}}</el-button>
                      </a>
                    </div>
                    <div>
                      <form ref="deleteForm" action="{{ console_route('products.destroy', [$product->id]) }}"
                            method="POST"
                            class="d-inline">
                        @csrf
                        @method('DELETE')
                        <el-button size="small" type="danger" plain @click="open({{$product->id}})">{{
                        __('console/common.delete')}}</el-button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $products->withQueryString()->links('console::vendor/pagination/bootstrap-4') }}
      @else
        <x-common-no-data/>
      @endif

      {{-- Bulk Actions Modals --}}
      @include('console::products.bulk.modals')

    </div>
  @endsection

@push('footer')

  <script>
    const {createApp, ref, reactive, watch} = Vue;
    const {ElMessageBox, ElMessage, ElLoading} = ElementPlus;

    const app = createApp({
      setup() {
        const deleteForm = ref(null);
        const checkedIds = ref([]);
        const checkAllBox = ref(null);
        const categoryCascaderOptions = @json($categoryOptions);

        const dialogVisible = ref({
          price: false,
          categories: false,
          quantity: false,
          publish: false,
          unpublish: false,
        });

        const bulkFormData = ref({
          price: {mode: 'reset', value: null},
          categories: [],
          quantity: {mode: 'reset', value: null},
        });

        const open = (index) => {
          ElMessageBox.confirm(
            '{{ __("common/base.hint_delete") }}',
            '{{ __("common/base.cancel") }}',
            {
              confirmButtonText: '{{ __("common/base.confirm")}}',
              cancelButtonText: '{{ __("common/base.cancel")}}',
              type: 'warning',
            }
          ).then(() => {
            deleteForm.value.action = urls.console_base + '/products/' + index;
            deleteForm.value.submit();
          }).catch(() => {
          });
        };

        const deleteAll = () => {
          if (checkedIds.value.length === 0) {
            ElMessage({
              type: 'warning',
              message: '{{ __("console/common.select_items") }}',
            });
            return;
          }

          ElMessageBox.confirm(
            `{{ __("console/product.bulk_delete_confirm") }}（${checkedIds.value.length} {{ __("console/product.items") }}）`,
            '{{ __("common/base.hint_delete") }}',
            {
              confirmButtonText: '{{ __("common/base.confirm")}}',
              cancelButtonText: '{{ __("common/base.cancel")}}',
              type: 'warning',
              dangerouslyUseHTMLString: true,
            }
          ).then(() => {
            const loading = ElLoading.service({
              lock: true,
              text: '{{ __("console/product.deleting") }}',
              background: 'rgba(0, 0, 0, 0.7)'
            });

            axios.delete("{{ console_route('products.destroy.batch') }}", {
              data: {
                ids: checkedIds.value
              }
            }).then(response => {
              ElMessage({
                type: 'success',
                message: response.message,
              });
              setTimeout(() => {
                window.location.reload();
              }, 1000);
            }).catch(error => {
              let errorMessage = '{{ __("common/base.error") }}';
              
              // Standard axios error handling
              if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
              } else if (error.message) {
                errorMessage = error.message;
              }
              
              ElMessage({
                type: 'error',
                message: errorMessage,
                duration: 5000
              });
            }).finally(() => {
              loading.close();
            });
          }).catch(() => {
            // User cancelled deletion
          });
        };

        // Debounce function
        const debounce = (func, wait) => {
          let timeout;
          return function executedFunction(...args) {
            const later = () => {
              clearTimeout(timeout);
              func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
          };
        };

        const bulkAction = debounce((action) => {
          if (checkedIds.value.length === 0) {
            ElMessage({
              type: 'warning',
              message: '{{ __("console/common.select_items") }}',
            });
            return;
          }

          if (dialogVisible.value.hasOwnProperty(action)) {
            dialogVisible.value[action] = true;
          } else {
            console.log('Action not implemented:', action);
          }
        }, 300);

        const submitBulkUpdate = (action) => {
          // Basic validation
          if (checkedIds.value.length === 0) {
            ElMessage({
              type: 'warning',
              message: '{{ __("console/common.select_items") }}',
            });
            return;
          }

          // Validate operations with numeric input
          if (['price', 'quantity'].includes(action)) {
            const formData = bulkFormData.value[action];
            if (!formData.value || formData.value <= 0) {
              ElMessage({
                type: 'warning',
                message: '{{ __("console/product.enter_valid_value") }}',
              });
              return;
            }
          }

          const payload = {
            action: action,
            ids: checkedIds.value,
            data: bulkFormData.value[action] || {}
          };

          // Show loading state
          const loading = ElLoading.service({
            lock: true,
            text: '{{ __("console/product.processing") }}',
            background: 'rgba(0, 0, 0, 0.7)'
          });

          axios.post("{{ console_route('products.bulk.update') }}", payload).then(response => {
              if (response && response.success) {
                ElMessage({
                  type: 'success', 
                  message: response.message
                });
                dialogVisible.value[action] = false;
                
                // Reset form data
                if (bulkFormData.value[action] && typeof bulkFormData.value[action] === 'object') {
                  if (bulkFormData.value[action].mode) {
                    bulkFormData.value[action].mode = 'reset';
                  }
                  if (bulkFormData.value[action].value) {
                    bulkFormData.value[action].value = null;
                  }
                }
                setTimeout(() => {
                  window.location.reload();
                }, 1000);
              } else {
                throw new Error(response?.message || '响应格式错误');
              }
            }).catch(error => {
              let errorMessage = '{{ __("common/base.error") }}';
              
              // Standard axios error handling
              if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
              } else if (error.message) {
                errorMessage = error.message;
              }
              
              ElMessage({
                type: 'error',
                message: errorMessage,
                duration: 5000
              });
            })
            .finally(() => {
              loading.close();
            });
        };

        const checkAll = () => {
          if (checkAllBox.value.checked) {
            checkedIds.value = Array.from(document.querySelectorAll('input[type="checkbox"][value]')).map(el => parseInt(el.value));
          } else {
            checkedIds.value = [];
          }
        };

        watch(checkedIds, (newVal) => {
          const allCheckboxes = document.querySelectorAll('input[type="checkbox"][value]');
          if (!allCheckboxes.length) return;

          if (newVal.length === allCheckboxes.length) {
            checkAllBox.value.checked = true;
            checkAllBox.value.indeterminate = false;
          } else if (newVal.length > 0) {
            checkAllBox.value.indeterminate = true;
            checkAllBox.value.checked = false;
          } else {
            checkAllBox.value.indeterminate = false;
            checkAllBox.value.checked = false;
          }
        });

        return {
          open,
          deleteForm,
          checkAll,
          checkedIds,
          checkAllBox,
          deleteAll,
          bulkAction,
          dialogVisible,
          bulkFormData,
          submitBulkUpdate,
          categoryCascaderOptions
        };
      }
    });
    app.use(ElementPlus);
    app.mount('#app');
    
    // Initialize Bootstrap tooltips
    document.addEventListener('DOMContentLoaded', function() {
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
      });
    });
  </script>

@endpush
