<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('layouts.partials.head')
</head>
<body style="font-family: monospace; background-color: #121212; color: #e0e0e0; padding: 20px; line-height: 1.5;">
    @include('layouts.partials.body-scripts')

    <h1 style="color: #ff9800; border-bottom: 2px solid #ff9800; padding-bottom: 10px; margin-bottom: 20px;">Frontend Layout (front.blade.php)</h1>

    <div style="margin-bottom: 25px; background-color: #1e1e1e; border: 1px solid #333; padding: 15px; border-radius: 5px;">
        <h2 style="margin-top: 0; color: #00bcd4;">Incoming Metadata & Settings Checklist</h2>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 1px solid #444; color: #ffeb3b;">
                    <th style="padding: 8px;">Metadata Field / Setting</th>
                    <th style="padding: 8px;">Current Loaded Value</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Site Name</td>
                    <td style="padding: 8px; color: #a5d6a7;">{{ \App\Models\Setting::get('site_name', 'Not Configured') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Google Verification ID</td>
                    <td style="padding: 8px; color: #a5d6a7;">{{ \App\Models\Setting::get('google_site_verification', 'None') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Google Analytics (GA4) ID</td>
                    <td style="padding: 8px; color: #a5d6a7;">{{ \App\Models\Setting::get('google_analytics_id', 'None') }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Model Type Loaded</td>
                    <td style="padding: 8px; color: #90caf9;">{{ isset($page) ? get_class($page) : 'No Model Loaded' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Page/Resource Title</td>
                    <td style="padding: 8px; color: #90caf9;">{{ $page->title ?? $page->name ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Meta Title (Rendered)</td>
                    <td style="padding: 8px; color: #ce93d8;">{{ $seo['meta_title'] ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Meta Description</td>
                    <td style="padding: 8px; color: #ce93d8;">{{ $seo['meta_description'] ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Robots Instructions</td>
                    <td style="padding: 8px; color: #ce93d8;">{{ $seo['robots_instructions'] ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Canonical URL</td>
                    <td style="padding: 8px; color: #ce93d8;">{{ $seo['canonical_url'] ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold;">Schema Type</td>
                    <td style="padding: 8px; color: #ffab91;">{{ $page->seoMetadata->schema_type ?? 'None' }}</td>
                </tr>
                @if(!empty($seo['structured_schema_json_ld']))
                <tr style="border-bottom: 1px solid #222;">
                    <td style="padding: 8px; font-weight: bold; vertical-align: top;">Structured Schema Payload</td>
                    <td style="padding: 8px; color: #ffab91;">
                        <pre style="margin: 0; background: #000; padding: 10px; border-radius: 4px; overflow-x: auto;">{{ json_encode($seo['structured_schema_json_ld'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </td>
                </tr>
                @endif
                <tr style="border-bottom: 1px solid #222; background-color: #263238;">
                    <td style="padding: 8px; font-weight: bold; color: #81c784;">OG Type</td>
                    <td style="padding: 8px; color: #81c784;">{{ $seo['og_type'] ?? 'website' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222; background-color: #263238;">
                    <td style="padding: 8px; font-weight: bold; color: #81c784;">OG Title</td>
                    <td style="padding: 8px; color: #81c784;">{{ $seo['og_title'] ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222; background-color: #263238;">
                    <td style="padding: 8px; font-weight: bold; color: #81c784;">OG Description</td>
                    <td style="padding: 8px; color: #81c784;">{{ $seo['og_description'] ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222; background-color: #263238;">
                    <td style="padding: 8px; font-weight: bold; color: #81c784;">OG Image URL</td>
                    <td style="padding: 8px; color: #81c784;">{{ $seo['og_image_url'] ?? 'None' }}</td>
                </tr>
                <tr style="border-bottom: 1px solid #222; background-color: #263238;">
                    <td style="padding: 8px; font-weight: bold; color: #81c784;">OG Locale</td>
                    <td style="padding: 8px; color: #81c784;">{{ $seo['og_locale'] ?? 'en_US' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-bottom: 25px; background-color: #1e1e1e; border: 1px solid #333; padding: 15px; border-radius: 5px;">
        <h2 style="margin-top: 0; color: #00bcd4;">Page Content</h2>
        @yield('content')
    </div>

    @include('layouts.partials.footer')
</body>
</html>
