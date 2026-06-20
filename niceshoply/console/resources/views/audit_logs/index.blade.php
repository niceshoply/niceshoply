@extends('console::layouts.app')
@section('body-class', '')

@section('title', __('console/menu.audit_logs'))

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0">{{ __('console/menu.audit_logs') }}</h4>
    </div>
  </div>

  <div class="card-body">
    <form method="GET" class="row g-3 mb-4">
      <div class="col-auto">
        <select name="log_name" class="form-select form-select-sm">
          <option value="">-- Log Name --</option>
          <option value="admin" {{ request('log_name') == 'admin' ? 'selected' : '' }}>Admin</option>
          <option value="order_status" {{ request('log_name') == 'order_status' ? 'selected' : '' }}>Order Status</option>
        </select>
      </div>
      <div class="col-auto">
        <input type="text" name="subject_type" class="form-control form-control-sm"
          placeholder="Subject Type" value="{{ request('subject_type') }}">
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('console/common.search') }}</button>
        <a href="{{ console_route('audit_logs.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('console/common.reset') }}</a>
      </div>
    </form>

    @if ($activities->count())
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>{{ __('console/common.created_at') }}</th>
            <th>Log</th>
            <th>Operator</th>
            <th>Description</th>
            <th>Subject</th>
            <th>Changes</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($activities as $activity)
          <tr>
            <td>{{ $activity->id }}</td>
            <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
            <td><span class="badge bg-secondary">{{ $activity->log_name }}</span></td>
            <td>{{ $activity->causer?->name ?? 'System' }}</td>
            <td>{{ $activity->description }}</td>
            <td>
              @if($activity->subject)
                {{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}
              @else
                -
              @endif
            </td>
            <td>
              @if($activity->properties->has('old'))
                <details>
                  <summary class="text-primary" style="cursor:pointer">View Changes</summary>
                  <div class="mt-2">
                    @foreach($activity->properties['attributes'] ?? [] as $key => $newVal)
                      @php $oldVal = $activity->properties['old'][$key] ?? '-'; @endphp
                      @if($oldVal != $newVal)
                      <div class="small mb-1">
                        <strong>{{ $key }}:</strong>
                        <span class="text-danger">{{ is_array($oldVal) ? json_encode($oldVal) : $oldVal }}</span>
                        &rarr;
                        <span class="text-success">{{ is_array($newVal) ? json_encode($newVal) : $newVal }}</span>
                      </div>
                      @endif
                    @endforeach
                  </div>
                </details>
              @elseif($activity->properties->count())
                <details>
                  <summary class="text-primary" style="cursor:pointer">View Details</summary>
                  <pre class="mt-2 small">{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
              @else
                -
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-3">
      {{ $activities->withQueryString()->links() }}
    </div>
    @else
    <x-common-no-data />
    @endif
  </div>
</div>
@endsection
