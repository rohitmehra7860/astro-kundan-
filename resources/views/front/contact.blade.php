@extends('layouts.front')

@section('content')
    <div style="padding: 10px;">
        <h3>Contact Page: {{ $page->title }}</h3>
        <div>{!! $page->content !!}</div>
        
        <!-- Render Locations dynamically from Site Settings -->
        @php
            $locations = json_decode(\App\Models\Setting::get('contact_locations', '[]'), true);
        @endphp
        
        @if(!empty($locations))
            <h4 style="margin-top: 25px; color: #ff9800; border-bottom: 1px solid #444; padding-bottom: 5px;">Our Locations & Contact Details</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 15px;">
                @foreach($locations as $loc)
                    <div style="background-color: #1e1e1e; border: 1px solid #333; padding: 20px; border-radius: 5px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        <strong style="color: #00bcd4; font-size: 1.15em; display: block; margin-bottom: 8px;">{{ $loc['name'] }}</strong>
                        <p style="margin: 8px 0; color: #ccc; font-style: italic;">{{ $loc['address'] }}</p>
                        
                        @if(!empty($loc['phones']))
                            <div style="margin-top: 12px;">
                                <strong style="font-size: 0.9em; color: #ffeb3b;">Phone Numbers:</strong>
                                <ul style="margin: 5px 0 0 0; padding-left: 20px; color: #a5d6a7;">
                                    @foreach((array)$loc['phones'] as $phone)
                                        <li>{{ $phone }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if(!empty($loc['emails']))
                            <div style="margin-top: 12px;">
                                <strong style="font-size: 0.9em; color: #ffeb3b;">Email Addresses:</strong>
                                <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                                    @foreach((array)$loc['emails'] as $email)
                                        <li><a href="mailto:{{ $email }}" style="color: #90caf9; text-decoration: none;">{{ $email }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!empty($loc['map_iframe']))
                            <div style="margin-top: 15px; border-radius: 4px; overflow: hidden; border: 1px solid #444;">
                                <style>
                                    .map-container iframe {
                                        width: 100% !important;
                                        height: 200px !important;
                                        border: 0;
                                        display: block;
                                    }
                                </style>
                                <div class="map-container">
                                    {!! $loc['map_iframe'] !!}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
