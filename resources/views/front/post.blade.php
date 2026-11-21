@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>Post Title: {{ $page->title }}</h3>
        <div><strong>Category:</strong> {{ $page->category->name ?? 'Uncategorized' }}</div>
        <div><strong>Body Content:</strong> {!! $page->content !!}</div>
    </div>
@endsection
