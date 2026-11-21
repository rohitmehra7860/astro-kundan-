@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>Title: {{ $page->title }}</h3>
        <div><strong>Body Content:</strong> {!! $page->content !!}</div>
    </div>
@endsection
