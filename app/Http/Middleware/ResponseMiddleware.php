<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if ($response->headers->get('Content-Type') === 'application/json' && $response->isSuccessful()) {
            $response->setContent(json_encode([
                'success' => true,
                'message' => 'Success',
                'data' => (json_decode($response->getContent())->meta ?? false) ? array_merge(['data'=> json_decode($response->getContent())->data],json_decode($response->getContent(),true)['meta']) : json_decode($response->getContent())->data ?? json_decode($response->getContent()),
            ]));

        } elseif ($response->headers->get('Content-Type') === 'application/json' && !$response->isSuccessful()) {
            $response->setContent(json_encode([
                'success' => false,
                'message' => json_decode($response->getContent())->message,
                'error' => json_decode($response->getContent())
            ]));
        }
        return $response;
    }
}
