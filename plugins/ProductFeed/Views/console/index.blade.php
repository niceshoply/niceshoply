@extends('console::layouts.app')
@section('title', __('ProductFeed::common.title'))
@section('content')
<p class="text-muted small">{{ __('ProductFeed::common.tip') }}</p>
<div class="mb-3">
  <button class="btn btn-primary btn-sm me-1" data-ch="google">{{ __('ProductFeed::common.google') }}</button>
  <button class="btn btn-outline-primary btn-sm me-1" data-ch="meta">{{ __('ProductFeed::common.meta') }}</button>
  <button class="btn btn-outline-secondary btn-sm" data-ch="csv">{{ __('ProductFeed::common.csv') }}</button>
</div>
<div class="card"><div class="card-header">{{ __('ProductFeed::common.history') }}</div>
<div class="card-body table-responsive"><table class="table table-sm mb-0"><thead><tr><th>Channel</th><th>{{ __('ProductFeed::common.items') }}</th><th>{{ __('ProductFeed::common.file') }}</th><th>Time</th></tr></thead>
<tbody>@forelse($logs as $l)<tr><td>{{ $l->channel }}</td><td>{{ $l->item_count }}</td><td><a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($l->file_path) }}" target="_blank">{{ $l->file_path }}</a></td><td>{{ $l->created_at }}</td></tr>@empty<tr><td colspan="4" class="text-muted">{{ __('ProductFeed::common.no_data') }}</td></tr>@endforelse</tbody></table></div></div>
<script>
const csrf='{{ csrf_token() }}';
document.querySelectorAll('[data-ch]').forEach(btn=>btn.onclick=()=>fetch('{{ console_route('product_feed.generate') }}',{method:'POST',headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({channel:btn.dataset.ch})}).then(r=>r.json()).then(res=>{alert(res.message+(res.data?.url?'\n'+res.data.url:''));if(res.success)location.reload();}));
</script>
@endsection
