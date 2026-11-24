<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitors
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && ! $request->is('up', 'favicon.ico', 'css/*', 'js/*', 'images/*', 'uploads/*')) {
            try {
                if (Schema::hasTable('visitor_logs')) {
                    $ua = (string) $request->userAgent();
                    $device = preg_match('/bot|crawl|spider/i', $ua)
                        ? 'bot'
                        : (preg_match('/mobile|android|iphone|ipad/i', $ua) ? 'mobile' : 'desktop');

                    VisitorLog::create([
                        'user_id' => $request->user()?->id,
                        'ip_address' => (string) ($request->ip() ?? '0.0.0.0'),
                        'method' => $request->method(),
                        'path' => '/'.ltrim($request->path(), '/'),
                        'referer' => $request->headers->get('referer') ? substr((string) $request->headers->get('referer'), 0, 250) : null,
                        'device_type' => $device,
                        'user_agent' => substr($ua, 0, 250),
                        'visited_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // Keep requests resilient
            }
        }

        return $response;
    }
}
