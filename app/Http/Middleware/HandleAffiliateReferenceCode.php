<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class HandleAffiliateReferenceCode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if 'cxd' parameter is present in the URL
        if ($request->has('cxd') && !empty($request->get('cxd'))) {
            $affiliateCode = $request->get('cxd');

            // Validate the affiliate code format (you can adjust this validation as needed)
            if (preg_match('/^\d+_\d+$/', $affiliateCode)) {
                // Queue the cookie to be set with the response
                Cookie::queue('cxd', $affiliateCode, 60 * 24 * 30); // 30 days
            }
        }

        return $next($request);
    }
}
