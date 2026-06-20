@extends('console::layouts.app')

@section('title', __('ReturnLogistics::common.title'))

@section('content')
  <p class="text-muted small">{{ __('ReturnLogistics::common.tip') }}</p>

  <div class="row g-3">
    <div class="col-lg-5">
      <div class="card"><div class="card-header">{{ __('ReturnLogistics::common.add_address') }}</div>
        <div class="card-body">
          <form id="addr-form" class="row g-2">
            <div class="col-6"><input name="name" class="form-control" placeholder="{{ __('ReturnLogistics::common.name') }}" required></div>
            <div class="col-6"><input name="contact" class="form-control" placeholder="{{ __('ReturnLogistics::common.contact') }}"></div>
            <div class="col-6"><input name="phone" class="form-control" placeholder="{{ __('ReturnLogistics::common.phone') }}"></div>
            <div class="col-6"><input name="province" class="form-control" placeholder="{{ __('ReturnLogistics::common.province') }}"></div>
            <div class="col-6"><input name="city" class="form-control" placeholder="{{ __('ReturnLogistics::common.city') }}"></div>
            <div class="col-6"><input name="area" class="form-control" placeholder="{{ __('ReturnLogistics::common.area') }}"></div>
            <div class="col-12"><input name="address" class="form-control" placeholder="{{ __('ReturnLogistics::common.address') }}" required></div>
            <div class="col-12"><button class="btn btn-primary btn-sm">{{ __('ReturnLogistics::common.add_address') }}</button></div>
          </form>
          <hr>
          @forelse($addresses as $a)
            <div class="small mb-2"><strong>{{ $a->name }}</strong> {{ $a->contact }} {{ $a->phone }}<br>{{ $a->fullAddress() }}</div>
          @empty
            <span class="text-muted">{{ __('ReturnLogistics::common.no_data') }}</span>
          @endforelse
        </div>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card mb-3"><div class="card-body">
        <form id="ship-form" class="row g-2 align-items-end">
          <div class="col-md-8"><input name="aftersale_id" type="number" class="form-control" placeholder="{{ __('ReturnLogistics::common.aftersale_id') }}" required></div>
          <div class="col-md-4"><button class="btn btn-primary w-100">{{ __('ReturnLogistics::common.create_shipment') }}</button></div>
        </form>
      </div></div>
      <div class="card"><div class="card-header">{{ __('ReturnLogistics::common.shipments') }}</div>
        <div class="card-body table-responsive">
          <table class="table table-sm table-bordered mb-0">
            <thead><tr>
              <th>{{ __('ReturnLogistics::common.aftersale_id') }}</th><th>{{ __('ReturnLogistics::common.order_number') }}</th>
              <th>{{ __('ReturnLogistics::common.tracking_no') }}</th><th>{{ __('ReturnLogistics::common.status') }}</th><th></th>
            </tr></thead>
            <tbody>
            @forelse($shipments as $s)
              <tr>
                <td>{{ $s->aftersale_id }}</td><td>{{ $s->order_number }}</td>
                <td>{{ $s->shipper_code }} {{ $s->tracking_no }}</td><td>{{ $s->status }}</td>
                <td class="text-end">@if($s->status !== 'received')<button class="btn btn-sm btn-outline-success rcv-btn" data-id="{{ $s->id }}">{{ __('ReturnLogistics::common.mark_received') }}</button>@endif</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">{{ __('ReturnLogistics::common.no_data') }}</td></tr>
            @endforelse
            </tbody>
          </table>
        </div><div class="card-footer">{{ $shipments->links() }}</div>
      </div>
      <div class="card mt-3"><div class="card-header">{{ __('ReturnLogistics::common.api_title') }}</div>
        <div class="card-body small"><pre class="bg-light p-2 mb-0">GET  /return-logistics/{aftersaleId}
POST /return-logistics/{aftersaleId}/tracking</pre></div>
      </div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('addr-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('return_logistics.address.store') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    });
    document.getElementById('ship-form').addEventListener('submit', e => {
      e.preventDefault();
      fetch('{{ console_route('return_logistics.shipment.create') }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body:new FormData(e.target) })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); else alert(res.message); });
    });
    const rcvBase = '{{ console_route('return_logistics.received', ['id' => '__ID__']) }}';
    document.querySelectorAll('.rcv-btn').forEach(b => b.addEventListener('click', function() {
      fetch(rcvBase.replace('__ID__', this.dataset.id), { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'} })
        .then(r=>r.json()).then(res=>{ if(res.success) location.reload(); });
    }));
  </script>
@endsection
