<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body>
    @include('layouts.partials.body-scripts')
    <div class="topbar"><div class="container topbar-inner"><span>Personal astrology guidance, with care and discretion</span><a href="{{ route('pages.show', ['slug' => 'contact']) }}">Start a conversation <span aria-hidden="true">↗</span></a></div></div>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg container" aria-label="Main navigation">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">✦</span><span>Astro Kundan<small>ASTROLOGY & GUIDANCE</small></span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav ms-auto align-items-lg-center gap-lg-3">
                <li><a class="nav-link" href="{{ route('home') }}">Home</a></li><li><a class="nav-link" href="{{ route('pages.show', ['slug' => 'about']) }}">About</a></li>
                <li><a class="nav-link" href="{{ route('services.index') }}">Services</a></li><li><a class="nav-link" href="{{ route('pages.show', ['slug' => 'blog']) }}">Blog</a></li>
                <li><a class="btn btn-plum nav-cta" href="{{ route('pages.show', ['slug' => 'contact']) }}">Book a consultation <span aria-hidden="true">↗</span></a></li>
            </ul></div>
        </nav>
    </header>
    <main>@yield('content')</main>
    @include('layouts.partials.footer')
</body>
</html>
