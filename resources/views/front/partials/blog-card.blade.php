@php
    $blogImages = [
        'what-to-expect-astrology-consultation' => 'consultation-expectations.jpg',
        'kundli-matching-compatibility-conversation' => 'kundli-compatibility.jpg',
        'thoughtful-relationship-choices' => 'relationship-reflection.jpg',
    ];
    $blogImage = $post->featuredImage?->getFirstMediaUrl('gallery')
        ?: (isset($blogImages[$post->slug]) ? asset('images/blog-assets/'.$blogImages[$post->slug]) : null);
@endphp
<article class="journal-card {{ $blogImage ? 'has-image' : '' }}">
    @if($blogImage)<a class="journal-image" href="{{ route('web.posts.show', $post->slug) }}"><img src="{{ $blogImage }}" alt="Illustration for {{ $post->title }}" loading="lazy"></a>@endif
    <p class="eyebrow">{{ $post->category?->name ?? 'ASTRO KUNDAN BLOG' }}</p>
    <h3><a href="{{ route('web.posts.show', $post->slug) }}">{{ $post->title }}</a></h3>
    <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 155) }}</p>
    <a class="text-link" href="{{ route('web.posts.show', $post->slug) }}">Read article <span>→</span></a>
</article>
