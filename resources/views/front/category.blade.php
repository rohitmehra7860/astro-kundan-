@extends('layouts.front')
@section('content')
<section class="page-hero"><div class="container"><p class="eyebrow">{{ $page->type === 'service' ? 'ASTROLOGY SERVICES' : 'FROM THE JOURNAL' }}</p><h1>{{ $page->name }}</h1><p>{{ $page->description }}</p></div></section>
<section class="section-pad"><div class="container">@if($page->type === 'service')<div class="service-grid">@forelse($services as $service)@include('front.partials.service-card', ['service' => $service])@empty<p>No services in this category yet.</p>@endforelse</div>@else<div class="journal-grid">@forelse($posts as $post)@include('front.partials.blog-card', ['post' => $post])@empty<p>No articles in this category yet.</p>@endforelse</div>@endif</div></section>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
