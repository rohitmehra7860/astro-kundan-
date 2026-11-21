@extends('layouts.front')
@section('content')
<section class="detail-hero"><div class="container detail-grid"><div><p class="eyebrow">ASTRO KUNDAN · PRIVATE CONSULTATION</p><h1>{{ $page->title }}</h1><p class="hero-lede">{{ $page->description }}</p><a class="btn btn-plum btn-lg" href="{{ route('pages.show', ['slug' => 'contact', 'service' => $page->title]) }}">Ask about this service <span>↗</span></a></div><div class="detail-art">@include('front.partials.service-card', ['service' => $page])</div></div></section>
<section class="section-pad"><div class="container content-narrow rich-content">{!! $page->content !!}<p class="disclaimer">Astrology guidance is interpretive and does not guarantee a specific result. Your choices and boundaries remain your own.</p></div></section>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
<section class="detail-cta"><div class="container"><h2>Would you like to talk it through?</h2><p>Send a private enquiry when you’re ready.</p><a class="btn btn-plum" href="{{ route('pages.show', ['slug' => 'contact', 'service' => $page->title]) }}">Get in touch ↗</a></div></section>
@endsection
