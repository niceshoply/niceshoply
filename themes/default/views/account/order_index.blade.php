{{--
  ============================================================
  【文件说明】
    用户中心 — 我的订单列表页。
    以分页表格形式展示该会员的所有订单，支持按状态 Tab 过滤和订单编号搜索。
    若订单含子订单（$order->children），可展开折叠查看。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.orders.index
    URL 示例：/{locale}/account/orders?status=paid&number=xxx
    控制器：Front\Account\OrderController@index
    查询参数：
      status — 订单状态过滤（对应 $filter_statuses 中的值，空字符串表示全部）
      number — 订单编号模糊搜索

  【可用变量】
    $orders          — LengthAwarePaginator，分页订单集合，每条含：
                         number          订单编号
                         created_at      创建时间（Carbon）
                         status          状态原始值
                         status_format   状态格式化文本
                         status_color    状态对应的 Bootstrap 颜色类（如 'success'/'warning'）
                         total_format    格式化后的总金额字符串
                         items           订单商品集合（取前 5 条显示缩略图）
                         children        子订单集合（可展开）
    $filter_statuses — array，可供过滤的状态值列表（用于生成 Tab 导航）

  【Sections】
    body-class → 'page-order'
    content    → 状态 Tab 导航 + 搜索框 + 订单表格 + 分页
    footer     → 确认收货按钮 JS（@push）

  【插件钩子】
    @hookinsert('account.order_index.top')                 — 容器顶部
    @hookinsert('account.order_index.bottom')              — 容器底部
    @hookinsert('account.order_index.actions.after', $order) — 每行操作列末尾（可插入自定义按钮）

  【自定义建议】
    - 分页使用 $orders->links('console::vendor/pagination/bootstrap-4')，
      可替换为自定义分页视图
    - 订单缩略图使用 image_resize($product->image, 30, 30) 进行裁剪，
      可调整尺寸参数
    - "确认收货"按钮（.btn-shipped）通过 AJAX 调用 front_api/orders/{number}/complete，
      成功后刷新页面
    - 如需在列表中显示退款按钮等，在 account.order_index.actions.after 钩子中插入
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.orders.index" title="{{ __('front/account.orders') }}"/>

  @hookinsert('account.order_index.top')

  <div class="container">
    <div class="row">
      <div class="col-12 col-lg-3">
        @include('shared.account-sidebar')
      </div>
      <div class="col-12 col-lg-9">
        <div class="account-card-box order-box">
          <div class="account-card-title d-flex justify-content-between align-items-center">
            <span class="fw-bold">{{ __('front/order.order') }}</span>
          </div>

          <ul class="nav nav-tabs tabs-plus">
            <li class="nav-item">
              <a class="nav-link {{ request('status') == '' ? 'active' : '' }}"
                 href="{{ account_route('orders.index') }}">{{ __('front/order.all') }}</a>
            </li>
            @foreach ($filter_statuses as $status)
              <li class="nav-item">
                <a class="nav-link {{ request('status') == $status ? 'active' : '' }}"
                   href="{{ account_route('orders.index', ['status' => $status]) }}">
                  {{ __('front/order.' . $status) }}</a>
              </li>
            @endforeach
          </ul>

          <form method="GET" action="{{ account_route('orders.index') }}" class="mb-3 d-flex" style="max-width: 400px;">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input
                type="text"
                name="number"
                class="form-control me-2"
                placeholder="{{ __('front/order.order_number') }}"
                value="{{ request('number') }}"
            >
            <button class="btn btn-primary text-nowrap" type="submit">{{ __('front/common.search') }}</button>
          </form>

          @if ($orders->count())
            <table class="table align-middle account-table-box table-response">
              <thead>
              <tr>
                <th>{{ __('front/order.order_number') }}</th>
                <th>{{ __('front/order.order_items') }}</th>
                <th>{{ __('front/order.order_date') }}</th>
                <th>{{ __('front/order.order_status') }}</th>
                <th>{{ __('front/order.order_total') }}</th>
                <th>{{ __('front/common.action') }}</th>
              </tr>
              </thead>
              <tbody>
              @foreach ($orders as $order)
                <tr>
                  <td data-title="Order ID">
                    @if ($order->children->count())
                      <a class="btn btn-link btn-sm p-0 me-2" data-bs-toggle="collapse"
                         href="#collapse{{ $order->id }}" role="button">
                        <i class="bi bi-chevron-down"></i>
                      </a>
                    @endif
                    {{ $order->number }}
                  </td>
                  <td data-title="Order Items">
                    <div class="d-flex">
                      @foreach ($order->items->take(5) as $product)
                        <div class="wh-30 overflow-hidden border border-1 me-1">
                          <img src="{{ image_resize($product->image, 30, 30) }}" alt="{{ $product->name }}"
                               class="img-fluid">
                        </div>
                      @endforeach
                    </div>
                  </td>
                  <td data-title="Date">{{ $order->created_at->format('Y-m-d') }}</td>
                  <td data-title="Date"><span
                      class="badge bg-{{ $order->status_color }} ">{{ $order->status_format }}</span></td>
                  <td data-title="Total">{{ $order->total_format }}</td>
                  <td data-title="Actions">
                    <a href="{{ account_route('orders.number_show', $order->number) }}" class="btn btn-primary btn-sm"
                       role="button">{{ __('front/common.view') }}</a>
                    @if ($order->status == 'shipped')
                      <button data-number="{{ $order->number }}"
                              class="btn btn-primary btn-sm btn-shipped">{{ __('front/account.signed') }}</button>
                    @endif
                    @hookinsert('account.order_index.actions.after', $order)
                  </td>
                </tr>

                @if ($order->children->count())
                  <tr class="p-0">
                    <td colspan="6" class="p-0 border-bottom-0">
                      <div class="collapse" id="collapse{{ $order->id }}">
                        <div class="tab ps-5">
                          <table class="table table-sm mb-0">
                            <thead>
                            <tr>
                              <th>{{ __('front/order.order_number') }}</th>
                              <th>{{ __('front/order.order_items') }}</th>
                              <th>{{ __('front/order.order_date') }}</th>
                              <th>{{ __('front/order.order_status') }}</th>
                              <th>{{ __('front/order.order_total') }}</th>
                              <th>{{ __('front/common.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($order->children as $child)
                              <tr>
                                <td>{{ $child->number }}</td>
                                <td>
                                  <div class="d-flex">
                                    @foreach ($child->items->take(5) as $product)
                                      <div class="wh-30 overflow-hidden border border-1 me-1">
                                        <img src="{{ image_resize($product->image, 30, 30) }}"
                                             alt="{{ $product->name }}"
                                             class="img-fluid">
                                      </div>
                                    @endforeach
                                  </div>
                                </td>
                                <td>{{ $child->created_at->format('Y-m-d') }}</td>
                                <td>
                                  <span class="badge bg-{{ $order->status_color }} ">{{ $order->status_format }}</span>
                                </td>
                                <td>{{ $child->total_format }}</td>
                                <td>
                                  <a href="{{ account_route('orders.number_show', $child->number) }}"
                                     class="btn btn-primary btn-sm" role="button">{{ __('front/common.view') }}</a>
                                  @if ($child->status == 'shipped')
                                    <button data-number="{{ $child->number }}"
                                            class="btn btn-primary btn-sm btn-shipped">{{ __('front/account.signed') }}</button>
                                  @endif
                                </td>
                              </tr>
                            @endforeach
                            </tbody>
                          </table>
                        </div>
                      </div>
                    </td>
                  </tr>
                @endif
              @endforeach
              </tbody>
            </table>

            {{ $orders->links('console::vendor/pagination/bootstrap-4') }}
          @else
            <x-common-no-data/>
          @endif
        </div>
      </div>
    </div>
  </div>

  @hookinsert('account.order_index.bottom')

@endsection
@push('footer')
  <script>
    $(document).ready(function () {
      $('.btn-shipped').click(function () {
        var button = $(this);
        var number = $(this).data('number');

        axios.post(`${urls.front_api}/orders/${number}/complete`, {
          number: number
        }).then(function (response) {
          inno.msg(__('front/account.signed_success'));
          button.fadeOut(300, function () {
            $(this).remove();
          });
          window.location.reload();
        }).catch(function (error) {
          inno.msg(__('front/account.signed_failed'));
        });
      });
    });
  </script>
@endpush
