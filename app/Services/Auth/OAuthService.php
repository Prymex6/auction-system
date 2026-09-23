<?php

namespace App\Services\Auth;

use App\Models\OAuthAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class OAuthService
{
    /**
     * Handle OAuth user registration/login
     */
    public function handleOAuthUser(SocialiteUser $socialiteUser, string $provider): User
    {
        return DB::transaction(function () use ($socialiteUser, $provider) {
            // Check if OAuth account exists
            $oauthAccount = OAuthAccount::where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($oauthAccount) {
                return $oauthAccount->user;
            }

            // Check if user exists by email
            $user = User::where('email', $socialiteUser->getEmail())->first();

            if (! $user) {
                // Create new user
                $user = User::create([
                    'name' => $socialiteUser->getName(),
                    'email' => $socialiteUser->getEmail(),
                    'password' => bcrypt(uniqid()),
                    'email_verified_at' => now(),
                ]);
            }

            // Create OAuth account
            OAuthAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $socialiteUser->getId(),
                'email' => $socialiteUser->getEmail(),
                'name' => $socialiteUser->getName(),
                'avatar_url' => $socialiteUser->getAvatar(),
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken ?? null,
                'expires_at' => $socialiteUser->expiresIn ? now()->addSeconds($socialiteUser->expiresIn) : null,
            ]);

            return $user;
        });
    }

    /**
     * Check if user has OAuth account linked
     */
    public function hasOAuthProvider(User $user, string $provider): bool
    {
        return $user->oauthAccounts()
            ->where('provider', $provider)
            ->exists();
    }

    /**
     * Link OAuth account to existing user
     */
    public function linkOAuthAccount(User $user, SocialiteUser $socialiteUser, string $provider): OAuthAccount
    {
        return OAuthAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id' => $socialiteUser->getId(),
                'email' => $socialiteUser->getEmail(),
                'name' => $socialiteUser->getName(),
                'avatar_url' => $socialiteUser->getAvatar(),
                'access_token' => $socialiteUser->token,
                'refresh_token' => $socialiteUser->refreshToken ?? null,
                'expires_at' => $socialiteUser->expiresIn ? now()->addSeconds($socialiteUser->expiresIn) : null,
            ]
        );
    }

    /**
     * Unlink OAuth account from user
     */
    public function unlinkOAuthAccount(User $user, string $provider): bool
    {
        $user->oauthAccounts()
            ->where('provider', $provider)
            ->delete();

        return true;
    }
}
