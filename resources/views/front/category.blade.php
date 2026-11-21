@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>Category Name: {{ $page->name }}</h3>
        <div><strong>Description:</strong> {{ $page->description }}</div>
    </div>
@endsection
