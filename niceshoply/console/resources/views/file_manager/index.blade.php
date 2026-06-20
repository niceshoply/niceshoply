@extends(request()->header('X-Iframe') ? 'console::layouts.blank' : 'console::layouts.app')

@section('title', __('console/file_manager.title'))

@if(!request()->header('X-Iframe'))
    <x-console::form.right-btns/>
@endif

{{-- @include 内的 @section 不会进入父布局，须在 index 中声明 content --}}
@section('content')
  @include('console::file_manager.main')
@endsection
