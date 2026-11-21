@extends('layouts.front')
@section('content')
<section class="page-hero"><div class="container"><p class="eyebrow">ABOUT ASTRO KUNDAN</p><h1>A thoughtful space for life’s questions</h1><p>Personal astrology conversations, shaped around what matters to you.</p></div></section>
<section class="section-pad"><div class="container about-page-grid"><img class="about-portrait" src="{{ asset('images/astrologer-hero.jpg') }}" alt="Astrologer Kundan"><div class="rich-content"><p class="eyebrow">A PERSONAL APPROACH</p><h2>Listen first. Explore your questions together.</h2><p>Astro Kundan offers personal astrology consultations for people navigating love, marriage, compatibility and other important questions. Each conversation begins with listening and makes room for the topics you choose to discuss.</p><p>Astrology is an interpretive tradition. The aim is to offer perspective for reflection while respecting your choices, boundaries, and lived experience.</p><a class="btn btn-plum" href="{{ route('pages.show', ['slug' => 'contact']) }}">Start a conversation ↗</a></div></div></section>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
