<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RedirectRule;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo(); // Always starts with / and excludes query parameters
        $normalizedPath = RedirectRule::normalizePath($path);

        try {
            // Check if settings table exists to avoid errors during migrations or bootstrapping
            if (\Illuminate\Support\Facades\Schema::hasTable('redirect_rules')) {
                $rule = RedirectRule::where('is_active', true)
                    ->where('old_path', $normalizedPath)
                    ->first();

                if ($rule) {
                    $newUrl = $rule->new_path;

                    // Preserve original query parameters (UTM codes, parameters, etc.)
                    $query = $request->getQueryString();
                    if ($query) {
                        $separator = str_contains($newUrl, '?') ? '&' : '?';
                        $newUrl .= $separator . $query;
                    }

                    return redirect()->to($newUrl, $rule->status_code);
                }
            }
        } catch (\Exception $e) {
            // Fallback to let the request proceed if database is unavailable or tables do not exist
        }

        return $next($request);
    }
}
