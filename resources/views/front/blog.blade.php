@extends('layouts.front')
@section('content')
<section class="page-hero"><div class="container"><p class="eyebrow">THE ASTRO KUNDAN JOURNAL</p><h1>Thoughts, questions & guidance</h1><p>General articles about astrology, relationships, compatibility, and the questions people bring to a consultation.</p></div></section>
<section class="section-pad"><div class="container"><div class="journal-grid">@forelse($posts as $post)<article class="journal-card"><p class="eyebrow">{{ $post->category?->name ?? 'ASTROLOGY JOURNAL' }}</p><h3><a href="{{ route('web.posts.show', $post->slug) }}">{{ $post->title }}</a></h3><p>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 170) }}</p><a class="text-link" href="{{ route('web.posts.show', $post->slug) }}">Read article <span>→</span></a></article>@empty<div class="empty-state">New journal articles are on the way.</div>@endforelse</div></div></section>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
