@extends('layouts.front')
@section('content')
<section class="page-hero"><div class="container"><p class="eyebrow">ASTRO KUNDAN</p><h1>{{ $page->title ?? $page->name }}</h1><p>{{ $page->seoMetadata?->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($page->content ?? $page->description ?? ''), 180) }}</p></div></section>
<section class="section-pad"><div class="container content-narrow rich-content">{!! $page->content ?? $page->description !!}</div></section>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
