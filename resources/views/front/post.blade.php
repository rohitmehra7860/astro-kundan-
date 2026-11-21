@extends('layouts.front')
@section('content')
@php
    $blogImages = ['what-to-expect-astrology-consultation' => 'consultation-expectations.jpg', 'kundli-matching-compatibility-conversation' => 'kundli-compatibility.jpg', 'thoughtful-relationship-choices' => 'relationship-reflection.jpg'];
    $articleImage = $featured_image_url ?: (isset($blogImages[$page->slug]) ? asset('images/blog-assets/'.$blogImages[$page->slug]) : null);
@endphp
<article class="article-wrap"><div class="container content-narrow"><a class="back-link" href="{{ route('pages.show', ['slug' => 'blog']) }}">← Back to blog</a><p class="eyebrow article-kicker">{{ $page->category?->name ?? 'ASTRO KUNDAN BLOG' }}</p><h1>{{ $page->title }}</h1><p class="article-meta">{{ $page->created_at?->format('F j, Y') }}@if(!empty($seo['reading_time_minutes'])) · {{ $seo['reading_time_minutes'] }} min read @endif</p>@if($articleImage)<img class="article-image" src="{{ $articleImage }}" alt="Illustration for {{ $page->title }}">@endif<div class="rich-content article-content">{!! $page->content !!}</div><p class="disclaimer">This article is general information, not personalized advice or a guaranteed prediction.</p></div></article>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
