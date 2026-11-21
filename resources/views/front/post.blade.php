@extends('layouts.front')
@section('content')
<article class="article-wrap"><div class="container content-narrow"><a class="back-link" href="{{ route('pages.show', ['slug' => 'blog']) }}">← Back to journal</a><p class="eyebrow article-kicker">{{ $page->category?->name ?? 'ASTRO KUNDAN JOURNAL' }}</p><h1>{{ $page->title }}</h1><p class="article-meta">{{ $page->created_at?->format('F j, Y') }}@if(!empty($seo['reading_time_minutes'])) · {{ $seo['reading_time_minutes'] }} min read @endif</p>@if($featured_image_url)<img class="article-image" src="{{ $featured_image_url }}" alt="{{ $page->title }}">@endif<div class="rich-content article-content">{!! $page->content !!}</div><p class="disclaimer">This article is general information, not personalized advice or a guaranteed prediction.</p></div></article>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
