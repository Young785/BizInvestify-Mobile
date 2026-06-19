<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;

class TwoFactorVerificationService
{
    public const CACHE_PREFIX = '2fa_verified_token_';

    public const DEFAULT_TTL_HOURS = 24;

    public function sessionKey(int $userId): string
    {
        return '2fa_verified_' . $userId;
    }

    public function cacheKey(int $tokenId): string
    {
        return self::CACHE_PREFIX . $tokenId;
    }

    public function markVerified(Request $request, User $user): void
    {
        $sessionKey = $this->sessionKey($user->id);

        if ($request->hasSession()) {
            $request->session()->put($sessionKey, true);
        }

        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            Cache::put(
                $this->cacheKey($token->id),
                true,
                now()->addHours(self::DEFAULT_TTL_HOURS)
            );
        }
    }

    public function markTokenVerified(PersonalAccessToken $token): void
    {
        Cache::put(
            $this->cacheKey($token->id),
            true,
            now()->addHours(self::DEFAULT_TTL_HOURS)
        );
    }

    public function clearForToken(?PersonalAccessToken $token): void
    {
        if ($token) {
            Cache::forget($this->cacheKey($token->id));
        }
    }

    public function isVerifiedForRequest(Request $request, User $user): bool
    {
        $sessionKey = $this->sessionKey($user->id);

        if ($request->hasSession() && $request->session()->has($sessionKey)) {
            return true;
        }

        $token = $user->currentAccessToken();
        if ($token instanceof PersonalAccessToken) {
            return Cache::has($this->cacheKey($token->id));
        }

        return false;
    }
}
