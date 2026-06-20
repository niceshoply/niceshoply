@extends('layouts.app')
@section('body-class', 'page-legal')

@section('title', $document->translation->title ?? __('front/legal.document'))

@section('content')
<div class="container py-4">
  <div class="card">
    <div class="card-body">
      <h1 class="h4 mb-3">{{ $document->translation->title ?? '' }}</h1>
      <div class="text-muted small mb-3">{{ __('front/legal.version') }}: {{ $document->version }}</div>
      <div class="legal-content">{!! $document->translation->content ?? '' !!}</div>
    </div>
  </div>
</div>
@endsection
