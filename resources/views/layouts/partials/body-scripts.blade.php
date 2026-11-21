<!-- Google Tag Manager (Noscript Body Fallback) -->
@if($gtmId = \App\Models\Setting::get('google_tag_manager_id'))
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
@endif

<!-- Meta Pixel (Noscript Fallback) -->
@if($pixelId = \App\Models\Setting::get('meta_pixel_id'))
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
    /></noscript>
@endif

<!-- Custom Body Scripts from Site Settings (immediately after body starts) -->
@if($customBody = \App\Models\Setting::get('custom_body_scripts'))
    {!! $customBody !!}
@endif
