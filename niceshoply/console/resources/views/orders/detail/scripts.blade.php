{{-- Scripts --}}
<script>
  $(document).ready(function() {
    // Admin comment input handler
    $('.admin-comment-input').on('keydown', function(event) {
      if (event.keyCode === 13) {
        event.preventDefault();
        var comment = $(this).val();
        var orderId = $(this).data('order-id');
        var apiUrl = `${urls.console_api}/orders/${orderId}/notes`;
        axios.post(apiUrl, {
            admin_note: comment,
          })
          .then(function(res) {
            inno.msg(res.message);
            $('.admin-comment-input').val(res.data.admin_note);
            window.location.reload()
          })
      }
    });

    // Add shipment button handler
    $('#addRow').click(function() {
      $('#editModal').modal('show');
    });

    // Copy-to-clipboard handler (address fields, etc.)
    $(document).on('click', '.address-copy', async function() {
      const value = $(this).attr('data-copy');
      if (!value) {
        return;
      }
      try {
        if (navigator.clipboard && window.isSecureContext) {
          await navigator.clipboard.writeText(value);
        } else {
          const textarea = document.createElement('textarea');
          textarea.value = value;
          textarea.style.position = 'fixed';
          textarea.style.opacity = '0';
          document.body.appendChild(textarea);
          textarea.select();
          document.execCommand('copy');
          document.body.removeChild(textarea);
        }
        inno.msg('{{ __('console/order.copy_success') }}');
      } catch (error) {
        inno.msg('{{ __('console/order.copy_failed') }}');
      }
    });

    // View shipment details function
    window.viewShipmentDetails = function(shipmentId) {
      axios.get(`${urls.console_api}/shipments/${shipmentId}/traces`)
        .then(function(response) {
          if (response.data && response.data.traces) {
            const tbody = $('#newShipmentModal .modal-body table tbody').last();
            tbody.empty();
            response.data.traces.forEach(trace => {
              const row = `<tr>
                        <td>${trace.time}</td>
                        <td>${trace.station}</td>
                     </tr>`;
              tbody.append(row);
            });
            var newShipmentModal = new bootstrap.Modal(document.getElementById('newShipmentModal'));
            newShipmentModal.show();
          }
        })
        .catch(function(error) {
          inno.msg('{{ __('console/order.no_logistics_information') }}');
        });
    }
  });

  // Submit comment function
  function submitComment() {
    let elment = $('.admin-comment-input');
    let comment = elment.val();
    let orderId = elment.data('order-id');
    let apiUrl = `${urls.console_api}/orders/${orderId}/notes`;
    axios.post(apiUrl, {
        admin_note: comment,
      })
      .then(function(res) {
        inno.msg(res.message);
        var admin_note = bootstrap.Modal.getInstance(document.getElementById('admin_note'));
        if (admin_note) {
          admin_note.hide();
        }
        $('.admin-comment-input').val(res.data.admin_note);
        window.location.reload();
      })
  }

  // Submit edit function
  function submitEdit() {
    const logisticsCompany = $('#logisticsCompany').val();
    const trackingNumber = $('#trackingNumber').val();
    const selectedCompanyName = $('#logisticsCompany option:selected').text();
    const orderId = {{ $order->id }};
    axios.post(`${urls.console_api}/orders/${orderId}/shipments`, {
      express_code: logisticsCompany,
      express_company: selectedCompanyName,
      express_number: trackingNumber,
    }).then(function(response) {
      inno.msg('{{ __('console/order.add_successfully') }}');
      $('#editModal').modal('hide');
      window.location.reload();
    }).catch(function(res) {
      inno.msg('{{ __('console/order.add_failed!') }}');
    });
  }

  // Delete shipment function
  function deleteShipment(shipmentId) {
    if (!window.confirm('{{ __('console/order.confirm_delete_shipment') }}')) {
      return;
    }
    const apiUrl = `${urls.console_api}/shipments/${shipmentId}`;
    axios.delete(apiUrl)
      .then(function(response) {
        inno.msg('{{ __('console/order.delete_successfully') }}');
        window.location.reload();
      })
  }

  // Vue.js status app
  const {
    createApp,
    ref
  } = Vue
  const api = @json(console_route('orders.change_status', $order));
  const warehouseEnabled = @json((bool) system_setting('warehouse_enabled', false));
  const expressCompaniesData = @json(is_array(system_setting('logistics', [])) ? array_values(system_setting('logistics', [])) : []);
  const statusApp = createApp({
    setup() {
      const statusDialog = ref(false)
      const comment = ref('')
      const needShipment = ref(false)
      const expressCode = ref('')
      const expressNumber = ref('')
      const expressCompanies = ref(expressCompaniesData)
      let status = '';

      const edit = (code) => {
        status = code
        // 发货状态（非多仓模式）时，引导填写物流单号，实现「填单即发货」
        needShipment.value = (code === 'shipped' && !warehouseEnabled)
        expressCode.value = ''
        expressNumber.value = ''
        comment.value = ''
        statusDialog.value = true
      }

      const submit = () => {
        const payload = {
          status: status,
          comment: comment.value,
        }
        if (needShipment.value && expressCode.value && expressNumber.value) {
          const company = expressCompanies.value.find(item => item.code === expressCode.value)
          payload.shipment = {
            express_code: expressCode.value,
            express_company: company ? company.company : expressCode.value,
            express_number: expressNumber.value,
          }
        }
        axios.put(api, payload).then(() => {
          statusDialog.value = false
          window.location.reload()
        })
      }

      return {
        edit,
        submit,
        comment,
        statusDialog,
        needShipment,
        expressCode,
        expressNumber,
        expressCompanies,
      }
    }
  })
  statusApp.use(ElementPlus);
  statusApp.mount('#status-app');
</script> 