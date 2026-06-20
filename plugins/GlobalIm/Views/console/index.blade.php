@extends('console::layouts.app')
@section('title', __('GlobalIm::common.title'))
@section('content')
<p class="text-muted small">{{ __('GlobalIm::common.tip') }}</p>
<div class="card mb-3"><div class="card-body">
  <form id="send-form" class="row g-2">
    <div class="col-md-2"><select name="channel" class="form-select"><option value="telegram">Telegram</option><option value="whatsapp">WhatsApp</option></select></div>
    <div class="col-md-3"><input name="peer_id" class="form-control" placeholder="{{ __('GlobalIm::common.peer') }}" required></div>
    <div class="col-md-5"><input name="body" class="form-control" placeholder="{{ __('GlobalIm::common.body') }}" required></div>
    <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('GlobalIm::common.send') }}</button></div>
  </form>
</div></div>
<div class="card"><div class="card-body table-responsive">
  <table class="table table-sm mb-0"><thead><tr><th>Channel</th><th>Dir</th><th>Peer</th><th>Body</th><th>Time</th></tr></thead>
  <tbody>@forelse($messages as $m)<tr><td>{{ $m->channel }}</td><td>{{ $m->direction }}</td><td>{{ $m->peer_id }}</td><td>{{ mb_strlen($m->body ?? '') > 80 ? mb_substr($m->body, 0, 80).'…' : $m->body }}</td><td>{{ $m->created_at }}</td></tr>@empty<tr><td colspan="5" class="text-muted">{{ __('GlobalIm::common.no_data') }}</td></tr>@endforelse</tbody></table>
</div></div>
<script>
const csrf='{{ csrf_token() }}';
document.getElementById('send-form').onsubmit=e=>{e.preventDefault();fetch('{{ console_route('global_im.send') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:new FormData(e.target)}).then(r=>r.json()).then(res=>{alert(res.message);if(res.success)location.reload();});};
</script>
@endsection
