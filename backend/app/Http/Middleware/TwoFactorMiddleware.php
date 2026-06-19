<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\TwoFactorVerificationService;

class TwoFactorMiddleware
{
    /**
     * Endpoints that must remain reachable before session 2FA is complete.
     */
    private const SKIP_ENDPOINTS = [
        'api/check-2fa-status',
        'api/verify-2fa-session',
        'api/verify-2fa-recovery',
        'api/me',
        'api/logout',
        'api/setup-2fa',
        'api/confirm-2fa',
        'api/setup-email-2fa',
        'api/confirm-email-2fa',
        'api/skip-2fa',
        'api/verify-2fa',
        'api/disable-2fa',
    ];

    public function __construct(
        private readonly TwoFactorVerificationService $twoFactorVerificationService
    ) {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($request->header('X-Impersonating')) {
            return $next($request);
        }

        if (!$user->requiresPerSessionTwoFactorVerification()) {
            return $next($request);
        }

        if ($this->shouldSkipEndpoint($request->path())) {
            return $next($request);
        }

        if ($this->twoFactorVerificationService->isVerifiedForRequest($request, $user)) {
            return $next($request);
        }

        Log::info('2FA verification required', [
            'user_id' => $user->id,
            'email' => $user->email,
            'endpoint' => $request->path(),
            'ip' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Two-factor authentication verification required',
                'data' => [
                    'requires_2fa_verification' => true,
                    'user_id' => $user->id,
                    'redirect_to' => '/auth/verify-2fa',
                ],
            ], 403);
        }

        return redirect()->route('auth.verify-2fa');
    }

    private function shouldSkipEndpoint(string $path): bool
    {
        foreach (self::SKIP_ENDPOINTS as $skipEndpoint) {
            if (str_starts_with($path, $skipEndpoint)) {
                return true;
            }
        }

        return false;
    }
}
