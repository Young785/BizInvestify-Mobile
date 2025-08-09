<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip for non-authenticated users (they'll be handled by auth middleware)
        if (!Auth::check()) {
            return $next($request);
        }

        /** @var User $user */
        $user = Auth::user();

        // Skip 2FA check for admin impersonation
        if ($request->header('X-Impersonating')) {
            return $next($request);
        }

        // Check if user has 2FA enabled but hasn't completed verification in this session
        if ($user->hasTwoFactorEnabled()) {
            // Check if 2FA was verified in this session
            $sessionKey = '2fa_verified_' . $user->id;
            
            // Skip 2FA verification for certain endpoints that are needed for the verification process itself
            $skipEndpoints = [
                'api/check-2fa-status',
                'api/verify-2fa-session',
                'api/me', // Allow user profile access for verification checks
                'api/analytics/seller', // Allow analytics access for testing
                'api/analytics/buyer',
                'api/analytics/overview',
                'api/analytics/realtime',
            ];
            
            // Check if the current path matches any skip endpoints (ignoring query parameters)
            $currentPath = $request->path();
            $shouldSkip = false;
            foreach ($skipEndpoints as $skipEndpoint) {
                if (strpos($currentPath, $skipEndpoint) === 0) {
                    $shouldSkip = true;
                    break;
                }
            }
            
            if ($shouldSkip) {
                return $next($request);
            }
            
            // For API requests, check if 2FA verification is required
            if ($request->expectsJson()) {
                // For API requests, we need to be more strict since sessions might not be available
                // We'll require 2FA verification for all API calls except the verification endpoints
                // Check if the current path matches any skip endpoints (ignoring query parameters)
                $currentPath = $request->path();
                $shouldSkip = false;
                foreach ($skipEndpoints as $skipEndpoint) {
                    if (strpos($currentPath, $skipEndpoint) === 0) {
                        $shouldSkip = true;
                        break;
                    }
                }
                
                if (!$shouldSkip) {
                    Log::info('2FA verification required for API access', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'endpoint' => $request->path(),
                        'ip' => $request->ip()
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Two-factor authentication verification required',
                        'data' => [
                            'requires_2fa_verification' => true,
                            'user_id' => $user->id,
                            'redirect_to' => '/auth/verify-2fa'
                        ]
                    ], 403);
                }
            } else {
                // For web requests, check session
                if (!$request->session()->has($sessionKey)) {
                    // For web requests, redirect to 2FA verification page
                    Log::info('2FA verification required for web access', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'path' => $request->path(),
                        'ip' => $request->ip()
                    ]);

                    return redirect()->route('auth.verify-2fa');
                }
            }
        }

        return $next($request);
    }
} 