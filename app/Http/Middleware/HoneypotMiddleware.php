<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HoneypotMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Honeypot protection uses two techniques:
     * 1. A hidden text field (hp_name) that should always be empty — bots auto-fill it.
     * 2. A timestamp field (hp_time) — if the form is submitted in under 2 seconds, it's likely a bot.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check 1: If the honeypot field is filled, it's a bot
        if ($request->filled('hp_name')) {
            return $this->rejectAsBot($request);
        }

        // Check 2: Time-based check — form submitted too fast (< 2 seconds)
        if ($request->has('hp_time')) {
            $submittedAt = (int) base64_decode($request->input('hp_time'));
            $elapsed = time() - $submittedAt;

            if ($elapsed < 2) {
                return $this->rejectAsBot($request);
            }
        }

        // Remove honeypot fields from request data so they don't interfere with validation
        $request->request->remove('hp_name');
        $request->request->remove('hp_time');

        return $next($request);
    }

    /**
     * Return an appropriate rejection response.
     */
    protected function rejectAsBot(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Request rejected.',
            ], 422);
        }

        // For web forms, redirect back with a generic error (don't reveal honeypot detection)
        return redirect()->back()->withErrors([
            'email' => 'Something went wrong. Please try again.',
        ]);
    }
}
