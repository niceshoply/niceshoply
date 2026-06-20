{{-- Status change app --}}
<script>
  // Submit refund
  function submitRefund() {
    const amount = parseFloat($('#refundAmount').val());
    const type = $('#refundType').val();
    const comment = $('#refundComment').val();
    if (!(amount > 0)) {
      inno.msg('{{ __('console/order_return.refund_amount_invalid') }}');
      return;
    }
    axios.post('{{ console_route('order_returns.refund', $order_return) }}', {
      amount: amount,
      type: type,
      comment: comment,
    }).then(function(res) {
      inno.msg(res.message || '{{ __('console/common.updated_success') }}');
      const modal = bootstrap.Modal.getInstance(document.getElementById('refundModal'));
      if (modal) {
        modal.hide();
      }
      window.location.reload();
    }).catch(function(err) {
      const msg = err && err.response && err.response.data ? err.response.data.message : '';
      inno.msg(msg || '{{ __('console/common.operation_failed') }}');
    });
  }
</script>
<script>
  const {
    createApp,
    ref
  } = Vue
  const api = @json(console_route('order_returns.change_status', $order_return));
  const nextStatuses = @json($next_statuses ?? []);
  const statusApp = createApp({
    setup() {
      const statusDialog = ref(false)
      const comment = ref('')
      const statusName = ref('')
      let status = '';

      const edit = (code) => {
        status = code
        const matched = nextStatuses.find(item => item.status === code)
        statusName.value = matched ? matched.name : code
        comment.value = ''
        statusDialog.value = true
      }

      const submit = () => {
        axios.put(api, {
          status: status,
          comment: comment.value,
        }).then((res) => {
          inno.msg(res.message || '{{ __('console/common.updated_success') }}')
          statusDialog.value = false
          window.location.reload()
        }).catch((err) => {
          const msg = err && err.response && err.response.data ? err.response.data.message : ''
          inno.msg(msg || '{{ __('console/common.operation_failed') }}')
        })
      }

      return {
        edit,
        submit,
        comment,
        statusName,
        statusDialog,
      }
    }
  })
  statusApp.use(ElementPlus);
  statusApp.mount('#status-app');
</script>
