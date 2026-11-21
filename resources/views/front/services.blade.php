@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>Services</h3>
        <ul>
            @forelse($services as $service)
                <li>
                    <a href="{{ route('services.show', $service->slug) }}">{{ $service->title }}</a>
                    ({{ $service->category->name ?? 'Uncategorized' }})
                </li>
            @empty
                <li>No services published yet.</li>
            @endforelse
        </ul>
    </div>
@endsection
