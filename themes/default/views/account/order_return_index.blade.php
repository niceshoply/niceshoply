{{--
  ============================================================
  【文件说明】
    用户中心 — 退货/退款申请列表页（RMA 列表）。
    以表格形式展示当前会员所有退货/退款申请记录，
    每条记录显示申请编号、关联订单号、商品名称、退货数量、申请时间、状态，
    并提供查看详情的链接。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.order_returns.index
    URL 示例：/{locale}/account/order-returns
    控制器：Front\Account\OrderReturnController@index

  【可用变量】
    $order_returns — Collection 或 Paginator，退货申请记录集合，每条含：
                       id            记录 ID（用于详情路由参数）
                       number        申请单编号
                       order_number  关联订单编号
                       product_name  退货商品名称
                       quantity      申请退货数量
                       created_at    申请时间
                       status_format 申请状态格式化文本

  【Sections】
    body-class → 'page-order'
    content    → 退货申请列表表格（无数据时显示 x-common-no-data 组件）

  【插件钩子】
    @hookinsert('account.order_return_index.top')    — 容器顶部
    @hookinsert('account.order_return_index.bottom') — 容器底部

  【自定义建议】
    - 若记录较多建议添加分页：$order_returns->links(...)
    - 可在列表中添加状态过滤 Tab（参考 order_index.blade.php 的实现方式）
    - 详情页路由：account_route('order_returns.show', ['order_return' => $item->id])
  ============================================================
--}}
{{--
  ============================================================
  【文件说明】
    用户中心 — 退货/退款申请列表页（RMA 列表）。
    以表格形式展示该会员提交的所有退货申请，包含申请编号、
    关联订单号、商品名、数量、提交时间、当前状态及查看详情链接。

  【访问权限】
    需要登录（由 CustomerAuthentication 中间件保护）。

  【对应路由/控制器】
    路由名称（GET）：account.order_returns.index
    URL 示例：/{locale}/account/order-returns
    控制器：Front\Account\OrderReturnController@index

  【可用变量】
    $order_returns — LengthAwarePaginator，退货申请集合，每条含：
                       number         申请编号
                       order_number   关联订单编号
                       product_name   退货商品名称
                       quantity       退货数量
                       created_at     申请时间
                       status_format  状态格式化文本（待处理/已批准/已拒绝等）

  【Sections】
    body-class → 'page-order'
    content    → 退货列表表格（无数据时显示 x-common-no-data）

  【插件钩子】
    @hookinsert('account.order_return_index.top')    — 容器顶部
    @hookinsert('account.order_return_index.bottom') — 容器底部

  【自定义建议】
    - 可在"查看"按钮后添加"撤销申请"等操作按钮
    - 如需分页，在表格后追加 $order_returns->links()
  ============================================================
--}}
@extends('layouts.app')
@section('body-class', 'page-order')

@section('content')
  <x-front-breadcrumb type="route" value="account.order_returns.index" title="{{ __('front/account.order_returns') }}" />

  @hookinsert('account.order_return_index.top')

    <div class="container">
     <div class="row">
       <div class="col-12 col-lg-3">
         @include('shared.account-sidebar')
       </div>
       <div class="col-12 col-lg-9">
         <div class="account-card-box order-box">
           <div class="account-card-title d-flex justify-content-between align-items-center">
             <span class="fw-bold">{{ __('front/account.order_returns') }}</span>
           </div>

           @if ($order_returns->count())
             <table class="table table-bordered ">
               <thead>
               <tr>
                 <th>{{ __('front/return.number') }}</th>
                 <th>{{ __('front/order.order_number') }}</th>
                 <th>{{ __('front/return.product_name') }}</th>
                 <th>{{ __('front/return.quantity') }}</th>
                 <th>{{ __('front/common.created_at') }}</th>
                 <th>{{ __('front/common.status') }}</th>
                 <th>{{ __('front/common.action') }}</th>
               </tr>
               </thead>
               <tbody>
               @foreach($order_returns as $item)
                 <tr>
                   <td class="align-middle" data-title="{{ __('front/return.return_number') }}">{{ $item->number }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_number') }}">{{ $item->order_number }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_date') }}">{{ $item->product_name }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.quantity') }}">{{ $item->quantity }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_date') }}">{{ $item->created_at }}</td>
                   <td class="align-middle" data-title="{{ __('front/return.return_status') }}">{{ $item->status_format }}</td>
                   <td class="align-middle" data-title="{{ __('front/common.action') }}">
                    <a  href="{{ account_route('order_returns.show', ['order_return'=>$item->id]) }}" class="btn btn-primary">{{ __('front/common.view') }}</a>
                   </td>
                 </tr>
               @endforeach
               </tbody>
             </table>
           @else
             <x-common-no-data />
           @endif
         </div>
       </div>
     </div>
   </div>

  @hookinsert('account.order_return_index.bottom')

@endsection