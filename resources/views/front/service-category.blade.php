@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>{{ $page->title }}</h3>
        <div>{!! $page->content !!}</div>

        <h4 style="margin-top: 25px; color: #ff9800; border-bottom: 1px solid #444; padding-bottom: 5px;">Service Categories</h4>
        @if(!empty($categories) && $categories->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 15px;">
                @foreach($categories as $cat)
                    <div style="background-color: #1e1e1e; border: 1px solid #333; padding: 20px; border-radius: 5px;">
                        <h5 style="margin: 0 0 10px 0;">
                            <a href="{{ url('/services/category/' . $cat->slug) }}" style="color: #00bcd4; text-decoration: none; font-size: 1.1em;">{{ $cat->name }}</a>
                        </h5>
                        <p style="color: #ccc; font-size: 0.95em; line-height: 1.4;">{{ $cat->description ?? 'No description provided.' }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: #aaa; font-style: italic; margin-top: 15px;">No service categories found.</p>
        @endif
    </div>
@endsection
