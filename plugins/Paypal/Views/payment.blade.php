{{-- PayPal 跳转支付：点击按钮后向后端创建 PayPal 订单，再重定向到 PayPal 审批页 --}}
<div class="paypal-payment card w-max-700 m-auto">
  <div class="card-body text-center">
    <div class="fs-5 mb-3">{{ __('Paypal::common.pay_with_paypal') }}</div>
    <p class="text-muted mb-4">{{ __('Paypal::common.redirect_tip') }}</p>
    <button type="button" id="paypal-checkout-button" class="btn btn-primary">
      {{ __('front/order.continue_pay') }}
    </button>
    <div id="paypal-error" class="text-danger mt-3" style="display:none;"></div>
  </div>
</div>

<script>
  (function () {
    const button = document.getElementById('paypal-checkout-button');
    const errorBox = document.getElementById('paypal-error');

    button.addEventListener('click', function () {
      button.disabled = true;
      errorBox.style.display = 'none';

      axios.post("{{ front_route('paypal_create_order') }}", {
        order_number: @json($order->number ?? ''),
      }).then(function (res) {
        const approveUrl = res.data && res.data.data ? res.data.data.approve_url : '';
        if (approveUrl) {
          // 跳转到 PayPal 审批页完成授权
          window.location.href = approveUrl;
        } else {
          throw new Error(res.data && res.data.message ? res.data.message : 'No approve url');
        }
      }).catch(function (err) {
        button.disabled = false;
        const msg = err.response && err.response.data && err.response.data.message
          ? err.response.data.message
          : err.message;
        errorBox.textContent = msg;
        errorBox.style.display = 'block';
      });
    });
  })();
</script>
