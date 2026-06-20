@extends('console::layouts.app')

@section('title', __('Recharge::common.menu_plans'))

@section('content')
  <div class="card mb-3">
    <div class="card-body">
      <form id="plan-form" class="row g-2 align-items-end">
        <input type="hidden" name="id" id="plan-id">
        <div class="col-md-4">
          <label class="form-label">{{ __('Recharge::common.name') }}</label>
          <input name="name" id="plan-name" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('Recharge::common.amount') }}</label>
          <input name="amount" id="plan-amount" type="number" step="0.01" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('Recharge::common.bonus') }}</label>
          <input name="bonus" id="plan-bonus" type="number" step="0.01" class="form-control" value="0">
        </div>
        <div class="col-md-2">
          <label class="form-label">{{ __('Recharge::common.sort') }}</label>
          <input name="sort" id="plan-sort" type="number" class="form-control" value="0">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">{{ __('Recharge::common.submit') }}</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
          <thead>
          <tr>
            <th>#</th>
            <th>{{ __('Recharge::common.name') }}</th>
            <th>{{ __('Recharge::common.amount') }}</th>
            <th>{{ __('Recharge::common.bonus') }}</th>
            <th>{{ __('Recharge::common.is_active') }}</th>
            <th class="text-end">{{ __('Recharge::common.actions') }}</th>
          </tr>
          </thead>
          <tbody>
          @forelse($plans as $p)
            <tr>
              <td>{{ $p->id }}</td>
              <td>{{ $p->name }}</td>
              <td>{{ currency_format($p->amount) }}</td>
              <td>{{ currency_format($p->bonus) }}</td>
              <td>
                @if($p->is_active)<span class="badge bg-success">{{ __('Recharge::common.yes') }}</span>
                @else<span class="badge bg-secondary">{{ __('Recharge::common.no') }}</span>@endif
              </td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-primary btn-edit"
                        data-id="{{ $p->id }}" data-name="{{ $p->name }}" data-amount="{{ $p->amount }}"
                        data-bonus="{{ $p->bonus }}" data-sort="{{ $p->sort }}">{{ __('Recharge::common.edit') }}</button>
                <button class="btn btn-sm btn-outline-danger btn-del" data-id="{{ $p->id }}">{{ __('Recharge::common.delete') }}</button>
              </td>
            </tr>
          @empty
            <tr><td colspan="6" class="text-center text-muted py-4">{{ __('Recharge::common.no_data') }}</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $plans->links() }}</div>
    </div>
  </div>

  <script>
    const csrf = '{{ csrf_token() }}';
    const base = '{{ console_route('recharge.plans') }}';

    document.querySelectorAll('.btn-edit').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.getElementById('plan-id').value = this.dataset.id;
        document.getElementById('plan-name').value = this.dataset.name;
        document.getElementById('plan-amount').value = this.dataset.amount;
        document.getElementById('plan-bonus').value = this.dataset.bonus;
        document.getElementById('plan-sort').value = this.dataset.sort;
      });
    });

    document.getElementById('plan-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const id = document.getElementById('plan-id').value;
      const form = new FormData(this);
      form.set('is_active', '1');
      let url = base;
      if (id) { url = base + '/' + id; form.append('_method', 'PUT'); }
      fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: form
      }).then(r => r.json()).then(function (res) {
        if (res.success) location.reload(); else alert(res.message || 'error');
      });
    });

    document.querySelectorAll('.btn-del').forEach(function (btn) {
      btn.addEventListener('click', function () {
        if (!confirm('?')) return;
        const form = new FormData();
        form.append('_method', 'DELETE');
        fetch(base + '/' + this.dataset.id, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
          body: form
        }).then(r => r.json()).then(() => location.reload());
      });
    });
  </script>
@endsection
