@extends('console::layouts.app')
@section('title', __('TaxEngine::common.title'))
@section('content')
<p class="text-muted small">{{ __('TaxEngine::common.tip') }}</p>
<div class="card mb-3"><div class="card-body">
  <form id="rule-form" class="row g-2">
    <div class="col-md-2"><input name="country_code" class="form-control" placeholder="US" required></div>
    <div class="col-md-2"><input name="region_code" class="form-control" placeholder="CA"></div>
    <div class="col-md-2"><input name="name" class="form-control" placeholder="VAT" required></div>
    <div class="col-md-2"><select name="tax_type" class="form-select"><option value="vat">VAT</option><option value="gst">GST</option><option value="sales">Sales</option></select></div>
    <div class="col-md-2"><input name="rate" type="number" step="0.01" class="form-control" placeholder="20" required></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('TaxEngine::common.add') }}</button></div>
  </form>
</div></div>
<div class="card"><div class="card-body table-responsive">
  <table class="table table-sm"><thead><tr><th>{{ __('TaxEngine::common.country') }}</th><th>{{ __('TaxEngine::common.region') }}</th><th>{{ __('TaxEngine::common.name') }}</th><th>{{ __('TaxEngine::common.type') }}</th><th>{{ __('TaxEngine::common.rate') }}</th><th></th></tr></thead>
  <tbody>@forelse($rules as $r)<tr><td>{{ $r->country_code }}</td><td>{{ $r->region_code }}</td><td>{{ $r->name }}</td><td>{{ $r->tax_type }}</td><td>{{ $r->rate }}%</td>
    <td><button class="btn btn-sm btn-outline-danger" onclick="delRule({{ $r->id }})">×</button></td></tr>@empty<tr><td colspan="6" class="text-muted">{{ __('TaxEngine::common.no_data') }}</td></tr>@endforelse</tbody></table>
</div></div>
<script>
const csrf='{{ csrf_token() }}';
const delBase='{{ console_route('tax_engine.destroy', ['id' => '__ID__']) }}';
document.getElementById('rule-form').onsubmit=e=>{e.preventDefault();fetch('{{ console_route('tax_engine.store') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:new FormData(e.target)}).then(r=>r.json()).then(res=>{if(res.success)location.reload();});};
function delRule(id){if(!confirm('?'))return;fetch(delBase.replace('__ID__',id),{method:'DELETE',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'}}).then(r=>r.json()).then(res=>{if(res.success)location.reload();});}
</script>
@endsection
