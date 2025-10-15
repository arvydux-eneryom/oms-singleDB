<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Security\RequestValidator;

class VerifyTwilioWebhook
{
    /**
     * Handle an incoming request and verify it came from Twilio.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip verification in testing environment
        if (app()->environment('testing')) {
            return $next($request);
        }

        $authToken = config('services.twilio.token');

        if (!$authToken) {
            Log::error('Twilio auth token not configured');
            abort(500, 'SMS service configuration error');
        }

        $validator = new RequestValidator($authToken);

        // Get the signature from the request headers
        $signature = $request->header('X-Twilio-Signature');

        if (!$signature) {
            Log::warning('Twilio webhook request missing signature', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            abort(403, 'Forbidden');
        }

        // Get the full URL that Twilio sent the request to
        $url = $request->fullUrl();

        // Get all POST parameters
        $postData = $request->post();

        // Validate the signature
        $isValid = $validator->validate($signature, $url, $postData);

        if (!$isValid) {
            Log::warning('Invalid Twilio webhook signature', [
                'ip' => $request->ip(),
                'url' => $url,
                'signature' => $signature,
            ]);
            abort(403, 'Invalid signature');
        }

        // Signature is valid, proceed with the request
        return $next($request);
    }
}
