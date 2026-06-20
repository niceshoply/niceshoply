@extends('console::layouts.app')

@section('title', __('AiImageStudio::common.title'))

@section('content')
  <div class="card mb-3"><div class="card-body">
    <p class="text-muted small">{{ __('AiImageStudio::common.tip') }}</p>
    <form id="gen-form" class="row g-2 align-items-end">
      <div class="col-md-8">
        <label class="form-label">{{ __('AiImageStudio::common.prompt') }}</label>
        <textarea name="prompt" class="form-control" rows="2" required placeholder="white sneaker product photo on a clean studio background, soft light"></textarea>
      </div>
      <div class="col-md-2">
        <label class="form-label">{{ __('AiImageStudio::common.count') }}</label>
        <input name="count" type="number" class="form-control" value="1" min="1" max="4">
      </div>
      <div class="col-md-2">
        <button class="btn btn-primary w-100" id="gen-btn">{{ __('AiImageStudio::common.generate') }}</button>
      </div>
    </form>
    <div id="gen-status" class="mt-2 small text-muted"></div>
  </div></div>

  <div class="card"><div class="card-header">{{ __('AiImageStudio::common.gallery') }}</div>
    <div class="card-body">
      <div class="row g-3" id="gallery">
        @forelse($images as $img)
          <div class="col-6 col-md-3">
            <div class="card h-100">
              <img src="{{ $img->url }}" class="card-img-top" style="aspect-ratio:1;object-fit:cover" loading="lazy">
              <div class="card-body p-2">
                <p class="small text-muted text-truncate mb-2" title="{{ $img->prompt }}">{{ $img->prompt }}</p>
                <div class="d-flex gap-1">
                  <button class="btn btn-sm btn-outline-primary copy-btn flex-fill" data-url="{{ $img->url }}">{{ __('AiImageStudio::common.copy_url') }}</button>
                  <button class="btn btn-sm btn-outline-danger del-btn" data-id="{{ $img->id }}">{{ __('AiImageStudio::common.del') }}</button>
                </div>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12 text-center text-muted py-4">{{ __('AiImageStudio::common.no_data') }}</div>
        @endforelse
      </div>
    </div>
    <div class="card-footer">{{ $images->links() }}</div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    document.getElementById('gen-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const btn = document.getElementById('gen-btn'); btn.disabled = true;
      document.getElementById('gen-status').textContent = '...';
      fetch('{{ console_route('ai_image_studio.generate') }}', {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: new FormData(this)
      }).then(r => r.json()).then(res => {
        btn.disabled = false;
        document.getElementById('gen-status').textContent = res.message || '';
        if (res.success) location.reload();
      }).catch(() => { btn.disabled = false; document.getElementById('gen-status').textContent = 'error'; });
    });
    document.querySelectorAll('.copy-btn').forEach(b => b.addEventListener('click', function () {
      navigator.clipboard.writeText(this.dataset.url).then(() => { this.textContent = '✓'; });
    }));
    const delBase = '{{ console_route('ai_image_studio.destroy', ['id' => '__ID__']) }}';
    document.querySelectorAll('.del-btn').forEach(b => b.addEventListener('click', function () {
      if (!confirm('{{ __('AiImageStudio::common.confirm_del') }}')) return;
      fetch(delBase.replace('__ID__', this.dataset.id), {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      }).then(r => r.json()).then(res => { if (res.success) location.reload(); });
    }));
  </script>
@endsection
