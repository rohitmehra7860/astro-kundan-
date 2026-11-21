@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>{{ $page->title }}</h3>
        <div>{!! $page->content !!}</div>

        <h4 style="margin-top: 25px; color: #ff9800; border-bottom: 1px solid #444; padding-bottom: 5px;">Recent Blog Posts</h4>
        @if(!empty($posts) && $posts->count() > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 15px;">
                @foreach($posts as $post)
                    <div style="background-color: #1e1e1e; border: 1px solid #333; padding: 20px; border-radius: 5px;">
                        <h5 style="margin: 0 0 10px 0;">
                            <a href="{{ url('/blog/' . $post->slug) }}" style="color: #00bcd4; text-decoration: none; font-size: 1.1em;">{{ $post->title }}</a>
                        </h5>
                        <p style="margin: 5px 0; font-size: 0.85em; color: #888;">
                            Status: <span style="color: #a5d6a7;">{{ $post->status }}</span> | Category: <strong style="color: #90caf9;">{{ $post->category?->name ?? 'None' }}</strong>
                        </p>
                        <p style="color: #ccc; font-size: 0.95em; line-height: 1.4;">{{ Str::limit(strip_tags($post->content), 150) }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color: #aaa; font-style: italic; margin-top: 15px;">No blog posts found.</p>
        @endif
    </div>
@endsection
