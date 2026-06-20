@extends('console::layouts.app')
@section('title', __('MultiCurrency::common.title'))
@section('content')
<p class="text-muted small">{{ __('MultiCurrency::common.tip') }}</p>
<button id="refresh-btn" class="btn btn-outline-primary btn-sm mb-3">{{ __('MultiCurrency::common.refresh') }}</button>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="card"><div class="card-header">{{ __('MultiCurrency::common.currency') }}</div>
      <div class="card-body table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Code</th><th>Name</th><th>{{ __('MultiCurrency::common.rate') }}</th></tr></thead>
        <tbody>@foreach($currencies as $c)<tr><td>{{ $c['code'] }}</td><td>{{ $c['name'] }}</td><td>{{ $c['rate'] }}</td></tr>@endforeach</tbody></table></div>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card mb-3"><div class="card-header">{{ __('MultiCurrency::common.regions') }}</div><div class="card-body">
      <form id="rg-form" class="row g-2"><div class="col-5"><input name="country_code" class="form-control" placeholder="US/CN" required></div>
        <div class="col-5"><input name="currency_code" class="form-control" placeholder="usd/cny" required></div>
        <div class="col-2"><button class="btn btn-primary w-100">+</button></div></form>
      <hr>@forelse($regions as $r)<div class="small">{{ $r->country_code }} → {{ $r->currency_code }}</div>@empty<span class="text-muted">{{ __('MultiCurrency::common.no_data') }}</span>@endforelse
    </div></div>
    <div class="card"><div class="card-body small"><pre class="bg-light p-2 mb-0">GET /currency/list
POST /currency/switch {code}
GET /currency/convert?amount=&from=&to=</pre></div></div>
  </div>
</div>
<script>
const csrf='{{ csrf_token() }}';
document.getElementById('refresh-btn').onclick=()=>fetch('{{ console_route('multi_currency.refresh') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}}).then(r=>r.json()).then(res=>{alert(res.message);if(res.success)location.reload();});
document.getElementById('rg-form').onsubmit=e=>{e.preventDefault();fetch('{{ console_route('multi_currency.region.store') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:new FormData(e.target)}).then(r=>r.json()).then(res=>{if(res.success)location.reload();});};
</script>
@endsection
