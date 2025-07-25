<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $key = 'global', int $maxAttempts = 60, int $decayMinutes = 1): Response
    {
        // Create a unique key based on IP, user agent, and endpoint
        $uniqueKey = $this->resolveRequestSignature($request, $key);

        // Check if the rate limit has been exceeded
        if (RateLimiter::tooManyAttempts($uniqueKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($uniqueKey);
            
            return response()->json([
                'success' => false,
                'message' => 'Too many attempts. Please try again later.',
                'retry_after' => $retryAfter,
                'max_attempts' => $maxAttempts
            ], 429);
        }

        // Increment the counter
        RateLimiter::hit($uniqueKey, $decayMinutes * 60);

        $response = $next($request);

        // Add rate limit headers
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => RateLimiter::remaining($uniqueKey, $maxAttempts),
            'X-RateLimit-Reset' => RateLimiter::availableIn($uniqueKey) + time(),
        ]);

        return $response;
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, string $key): string
    {
        $user = $request->user();
        
        // If user is authenticated, use user ID + key
        if ($user) {
            return 'rate_limit:' . $key . ':user:' . $user->id;
        }

        // For unauthenticated requests, use IP + user agent + key
        return 'rate_limit:' . $key . ':' . sha1(
            $request->ip() . '|' . 
            $request->userAgent() . '|' . 
            $key
        );
    }

    /**
     * Get the rate limiting key for authentication attempts.
     */
    public static function getAuthAttemptKey(Request $request): string
    {
        return 'auth_attempt:' . sha1(
            $request->ip() . '|' . 
            $request->input('email', '') . '|' .
            $request->userAgent()
        );
    }

    /**
     * Check if authentication attempts should be blocked.
     */
    public static function checkAuthAttempts(Request $request, int $maxAttempts = 5, int $lockoutMinutes = 15): bool
    {
        $key = self::getAuthAttemptKey($request);
        
        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Record a failed authentication attempt.
     */
    public static function recordFailedAuthAttempt(Request $request, int $lockoutMinutes = 15): void
    {
        $key = self::getAuthAttemptKey($request);
        
        RateLimiter::hit($key, $lockoutMinutes * 60);
    }

    /**
     * Clear failed authentication attempts for a user.
     */
    public static function clearAuthAttempts(Request $request): void
    {
        $key = self::getAuthAttemptKey($request);
        
        RateLimiter::clear($key);
    }

    /**
     * Get remaining authentication attempts.
     */
    public static function getRemainingAuthAttempts(Request $request, int $maxAttempts = 5): int
    {
        $key = self::getAuthAttemptKey($request);
        
        return RateLimiter::remaining($key, $maxAttempts);
    }

    /**
     * Get time until authentication attempts are available again.
     */
    public static function getAuthAttemptsAvailableIn(Request $request): int
    {
        $key = self::getAuthAttemptKey($request);
        
        return RateLimiter::availableIn($key);
    }
} 