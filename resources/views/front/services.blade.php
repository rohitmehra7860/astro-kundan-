@extends('layouts.front')
@section('content')
<section class="page-hero"><div class="container"><p class="eyebrow">PERSONAL ASTROLOGY CONSULTATIONS</p><h1>Guidance for life’s questions</h1><p>Explore a service and find the right place to begin your conversation.</p></div></section>
<section class="section-pad"><div class="container"><div class="service-grid">@forelse($services as $service)@include('front.partials.service-card', ['service' => $service])@empty<div class="empty-state">Services will appear here soon. Please <a href="{{ route('pages.show', ['slug' => 'contact']) }}">contact us</a> with your question.</div>@endforelse</div></div></section>
@include('front.partials.faq', ['faqs' => $faqs ?? []])
@endsection
