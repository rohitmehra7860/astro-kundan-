@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>About Page: {{ $page->title }}</h3>
        <div>{!! $page->content !!}</div>
    </div>
@endsection
