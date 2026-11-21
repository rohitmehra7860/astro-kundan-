@extends('layouts.front')
@section('content')
<section class="page-hero"><div class="container"><p class="eyebrow">THE ASTRO KUNDAN BLOG</p><h1>Thoughts, questions & guidance</h1><p>Articles about astrology, relationships, compatibility, and the questions people bring to a consultation.</p></div></section>
<section class="section-pad"><div class="container"><div class="journal-grid">@forelse($posts as $post)@include('front.partials.blog-card', ['post' => $post])@empty<div class="empty-state">New blog articles are on the way.</div>@endforelse</div></div></section>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
