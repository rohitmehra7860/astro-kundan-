<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Get all public site settings (no auth required).
     */
    public function index()
    {
        try {
            $settings = Setting::allGrouped();

            // Sanitize settings output
            if (isset($settings['google_analytics_id'])) {
                $settings['google_analytics_id'] = htmlentities(strip_tags($settings['google_analytics_id']));
            }
            if (isset($settings['google_tag_manager_id'])) {
                $settings['google_tag_manager_id'] = htmlentities(strip_tags($settings['google_tag_manager_id']));
            }
            if (isset($settings['meta_pixel_id'])) {
                $settings['meta_pixel_id'] = htmlentities(strip_tags($settings['meta_pixel_id']));
            }
            if (isset($settings['custom_head_scripts'])) {
                $settings['custom_head_scripts'] = strip_tags($settings['custom_head_scripts'], '<script><style><noscript>');
            }
            if (isset($settings['custom_body_scripts'])) {
                $settings['custom_body_scripts'] = strip_tags($settings['custom_body_scripts'], '<script><style><noscript>');
            }

            // Resolve logo URLs if light_logo_id / dark_logo_id exist
            if (!empty($settings['light_logo_id'])) {
                $lightLogo = \App\Models\MediaUpload::find($settings['light_logo_id']);
                $settings['light_logo_url'] = $lightLogo ? $lightLogo->getFirstMediaUrl('gallery') : null;
            } else {
                $settings['light_logo_url'] = null;
            }

            if (!empty($settings['dark_logo_id'])) {
                $darkLogo = \App\Models\MediaUpload::find($settings['dark_logo_id']);
                $settings['dark_logo_url'] = $darkLogo ? $darkLogo->getFirstMediaUrl('gallery') : null;
            } else {
                $settings['dark_logo_url'] = null;
            }

            // Resolve fallback OG image
            if (!empty($settings['fallback_og_image_id'])) {
                $fallbackOg = \App\Models\MediaUpload::find($settings['fallback_og_image_id']);
                $settings['fallback_og_image_url'] = $fallbackOg ? $fallbackOg->getFirstMediaUrl('gallery') : null;
            } else {
                $settings['fallback_og_image_url'] = null;
            }

            // Decode contact locations
            $locations = [];
            if (!empty($settings['contact_locations'])) {
                $locations = json_decode($settings['contact_locations'], true);
            }
            $settings['contact_locations'] = $locations;

            // Map first location back to legacy keys for compatibility
            if (!empty($locations)) {
                $firstLoc = $locations[0];
                $settings['site_address'] = $firstLoc['address'] ?? '';
                $settings['site_phone'] = !empty($firstLoc['phones']) ? implode(', ', (array) $firstLoc['phones']) : '';
                $settings['site_email'] = !empty($firstLoc['emails']) ? implode(', ', (array) $firstLoc['emails']) : '';
            } else {
                $settings['site_address'] = $settings['site_address'] ?? '';
                $settings['site_phone'] = $settings['site_phone'] ?? '';
                $settings['site_email'] = $settings['site_email'] ?? '';
            }

            // Decode social links and resolve custom icon URLs
            $socialLinks = [];
            if (!empty($settings['social_links'])) {
                $socialLinks = json_decode($settings['social_links'], true);
            }

            foreach ($socialLinks as $index => $link) {
                $iconUrl = null;
                if (!empty($link['icon_id'])) {
                    $media = \App\Models\MediaUpload::find($link['icon_id']);
                    $iconUrl = $media ? $media->getFirstMediaUrl('gallery') : null;
                }
                $socialLinks[$index]['icon_url'] = $iconUrl;

                // Backward compatibility: map dynamic social link URLs back to legacy keys ($settings['social_facebook'], etc.)
                $platform = $link['platform'] ?? '';
                $url = $link['url'] ?? '';
                if ($platform && $url) {
                    if ($platform === 'other') {
                        $platformName = $link['custom_platform'] ?? 'custom';
                        $cleanPlatform = preg_replace('/[^a-z0-9]/', '_', strtolower($platformName));
                        $settings["social_{$cleanPlatform}"] = $url;
                    } else {
                        $settings["social_{$platform}"] = $url;
                    }
                }
            }
            $settings['social_links'] = $socialLinks;

            // Build Organization JSON-LD schema
            $siteName  = $settings['site_name']  ?? config('app.name', '');
            $siteUrl   = rtrim(env('FRONTEND_URL', config('app.url')), '/');
            $orgSchema = [
                '@context' => 'https://schema.org',
                '@type'    => 'Organization',
                'name'     => $siteName,
                'url'      => $siteUrl,
            ];
            if (!empty($settings['site_email'])) {
                $orgSchema['email'] = $settings['site_email'];
            }
            if (!empty($settings['site_phone'])) {
                $orgSchema['telephone'] = $settings['site_phone'];
            }
            if (!empty($settings['site_address'])) {
                $orgSchema['address'] = $settings['site_address'];
            }
            if (!empty($settings['fallback_og_image_url'])) {
                $orgSchema['logo'] = $settings['fallback_og_image_url'];
            }
            
            // Build social profiles list for JSON-LD sameAs
            $socialProfiles = array_filter([
                $settings['social_facebook']  ?? null,
                $settings['social_twitter']   ?? null,
                $settings['social_instagram'] ?? null,
                $settings['social_linkedin']  ?? null,
                $settings['social_youtube']   ?? null,
            ]);
            
            foreach ($socialLinks as $link) {
                if (!empty($link['url'])) {
                    $socialProfiles[] = $link['url'];
                }
            }
            $socialProfiles = array_values(array_unique(array_filter($socialProfiles)));
            
            if (!empty($socialProfiles)) {
                $orgSchema['sameAs'] = $socialProfiles;
            }

            // SearchAction — enables Google Sitelinks Searchbox
            $searchUrl = $settings['site_search_url'] ?? null;
            if (!empty($searchUrl)) {
                $orgSchema['potentialAction'] = [
                    '@type'       => 'SearchAction',
                    'target'      => [
                        '@type'       => 'EntryPoint',
                        'urlTemplate' => rtrim($searchUrl, '=') . '={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ];
            }

            $settings['organization_schema'] = $orgSchema;

            return response()->json([
                'settings' => $settings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SettingsController@index: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while loading settings.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }

    /**
     * Get ready-to-inject tracking scripts for the frontend.
     * Returns head_scripts and body_scripts as raw HTML strings.
     */
    public function tracking()
    {
        try {
            $gaId = htmlentities(strip_tags(Setting::get('google_analytics_id', '')));
            $gtmId = htmlentities(strip_tags(Setting::get('google_tag_manager_id', '')));
            $pixelId = htmlentities(strip_tags(Setting::get('meta_pixel_id', '')));
            $customHead = strip_tags(Setting::get('custom_head_scripts', ''), '<script><style><noscript>');
            $customBody = strip_tags(Setting::get('custom_body_scripts', ''), '<script><style><noscript>');

            $headScripts = '';
            $bodyScripts = '';

            // Google Tag Manager — <head> snippet
            if ($gtmId) {
                $headScripts .= "<!-- Google Tag Manager -->\n";
                $headScripts .= "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n";
                $headScripts .= "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n";
                $headScripts .= "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n";
                $headScripts .= "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n";
                $headScripts .= "})(window,document,'script','dataLayer','{$gtmId}');</script>\n";
                $headScripts .= "<!-- End Google Tag Manager -->\n\n";
            }

            // Google Analytics 4 (GA4) — <head> snippet
            if ($gaId) {
                $headScripts .= "<!-- Google Analytics 4 -->\n";
                $headScripts .= "<script async src=\"https://www.googletagmanager.com/gtag/js?id={$gaId}\"></script>\n";
                $headScripts .= "<script>\nwindow.dataLayer = window.dataLayer || [];\n";
                $headScripts .= "function gtag(){dataLayer.push(arguments);}\n";
                $headScripts .= "gtag('js', new Date());\ngtag('config', '{$gaId}');\n</script>\n";
                $headScripts .= "<!-- End Google Analytics 4 -->\n\n";
            }

            // Meta (Facebook) Pixel — <head> snippet
            if ($pixelId) {
                $headScripts .= "<!-- Meta Pixel -->\n";
                $headScripts .= "<script>\n!function(f,b,e,v,n,t,s)\n";
                $headScripts .= "{if(f.fbq)return;n=f.fbq=function(){n.callMethod?\n";
                $headScripts .= "n.callMethod.apply(n,arguments):n.queue.push(arguments)};\n";
                $headScripts .= "if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';\n";
                $headScripts .= "n.queue=[];t=b.createElement(e);t.async=!0;\n";
                $headScripts .= "t.src=v;s=b.getElementsByTagName(e)[0];\n";
                $headScripts .= "s.parentNode.insertBefore(t,s)}(window, document,'script',\n";
                $headScripts .= "'https://connect.facebook.net/en_US/fbevents.js');\n";
                $headScripts .= "fbq('init', '{$pixelId}');\nfbq('track', 'PageView');\n</script>\n";
                $headScripts .= "<noscript><img height=\"1\" width=\"1\" style=\"display:none\" ";
                $headScripts .= "src=\"https://www.facebook.com/tr?id={$pixelId}&ev=PageView&noscript=1\"/></noscript>\n";
                $headScripts .= "<!-- End Meta Pixel -->\n\n";
            }

            // Custom head scripts
            if ($customHead) {
                $headScripts .= "<!-- Custom Head Scripts -->\n{$customHead}\n<!-- End Custom Head Scripts -->\n\n";
            }

            // Google Tag Manager — <body> noscript fallback
            if ($gtmId) {
                $bodyScripts .= "<!-- Google Tag Manager (noscript) -->\n";
                $bodyScripts .= "<noscript><iframe src=\"https://www.googletagmanager.com/ns.html?id={$gtmId}\" ";
                $bodyScripts .= "height=\"0\" width=\"0\" style=\"display:none;visibility:hidden\"></iframe></noscript>\n";
                $bodyScripts .= "<!-- End Google Tag Manager (noscript) -->\n\n";
            }

            // Custom body scripts
            if ($customBody) {
                $bodyScripts .= "<!-- Custom Body Scripts -->\n{$customBody}\n<!-- End Custom Body Scripts -->\n\n";
            }

            // Google Search Console verification meta tag
            $googleVerification = htmlentities(strip_tags(Setting::get('google_site_verification', '')));
            if ($googleVerification) {
                $headScripts .= "<!-- Google Search Console Verification -->\n";
                $headScripts .= "<meta name=\"google-site-verification\" content=\"{$googleVerification}\">\n";
                $headScripts .= "<!-- End Google Search Console Verification -->\n\n";
            }

            // Bing Webmaster Tools verification meta tag
            $bingVerification = htmlentities(strip_tags(Setting::get('bing_site_verification', '')));
            if ($bingVerification) {
                $headScripts .= "<!-- Bing Webmaster Tools Verification -->\n";
                $headScripts .= "<meta name=\"msvalidate.01\" content=\"{$bingVerification}\">\n";
                $headScripts .= "<!-- End Bing Webmaster Tools Verification -->\n\n";
            }

            // Robots noindex — injected when public indexing is disabled
            $publicIndexing = Setting::get('public_indexing', '1');
            if (empty($publicIndexing) || $publicIndexing === '0' || $publicIndexing === false) {
                $headScripts .= "<!-- Robots: Indexing Disabled -->\n";
                $headScripts .= "<meta name=\"robots\" content=\"noindex, nofollow\">\n";
                $headScripts .= "<!-- End Robots: Indexing Disabled -->\n\n";
            }

            return response()->json([
                'head_scripts' => $headScripts,
                'body_scripts' => $bodyScripts,
                'ids' => [
                    'google_analytics_id' => $gaId,
                    'google_tag_manager_id' => $gtmId,
                    'meta_pixel_id' => $pixelId,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in SettingsController@tracking: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred while loading tracking scripts.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
            ], 500);
        }
    }
}
