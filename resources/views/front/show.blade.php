@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>Title/Name: {{ $page->title ?? $page->name }}</h3>
        <div><strong>Body Content/Description:</strong> {!! $page->content ?? $page->description !!}</div>
    </div>
@endsection
