<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\BrokenLink;
use App\Models\RedirectRule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

class LogBrokenLinks
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if the response is a 404
        if ($response->getStatusCode() === 404) {
            try {
                $path = $request->getPathInfo();
                $normalizedPath = RedirectRule::normalizePath($path, true);

                // Skip common static files and system assets
                if ($this->shouldSkipLogging($normalizedPath)) {
                    return $response;
                }

                // Check if table exists to avoid errors during migrations
                if (Schema::hasTable('broken_links')) {
                    $brokenLink = BrokenLink::where('path', $normalizedPath)->first();
                    if ($brokenLink) {
                        $brokenLink->increment('hits_count', 1, [
                            'referrer' => $request->headers->get('referer'),
                            'user_agent' => $request->userAgent(),
                            'ip_address' => $request->ip(),
                            'last_seen_at' => Carbon::now(),
                        ]);
                    } else {
                        BrokenLink::create([
                            'path' => $normalizedPath,
                            'referrer' => $request->headers->get('referer'),
                            'user_agent' => $request->userAgent(),
                            'ip_address' => $request->ip(),
                            'hits_count' => 1,
                            'last_seen_at' => Carbon::now(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Silently handle errors so user request is never interrupted
                report($e);
            }
        }

        return $response;
    }

    /**
     * Determine if logging should be skipped for this path.
     */
    protected function shouldSkipLogging(string $path): bool
    {
        // Skip common static asset extensions
        if (preg_match('/\.(?:png|jpe?g|gif|css|js|ico|svg|woff2?|ttf|eot|map|webmanifest|json|txt|xml)$/i', $path)) {
            return true;
        }

        // Skip Livewire system paths or debugbar
        if (str_starts_with($path, '/livewire') || str_starts_with($path, '/_debugbar')) {
            return true;
        }

        return false;
    }
}
