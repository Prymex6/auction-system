<?php

namespace App\Services\Auth;

use App\Models\TwoFactorAuth;
use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Generate 2FA secret
     */
    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    /**
     * Create 2FA setup for user
     */
    public function setupTwoFactor(User $user): array
    {
        $secret = $this->generateSecret();
        $backupCodes = $this->generateBackupCodes();

        TwoFactorAuth::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $secret,
                'backup_codes' => $backupCodes,
                'verified' => false,
            ]
        );

        return [
            'secret' => $secret,
            'backup_codes' => $backupCodes,
            'qr_code' => $this->generateQRCode($user, $secret),
        ];
    }

    /**
     * Verify OTP token
     */
    public function verifyToken(User $user, string $token): bool
    {
        $twoFactorAuth = $user->twoFactorAuth;

        if (! $twoFactorAuth) {
            return false;
        }

        // Check backup codes first
        $backupCodes = $twoFactorAuth->backup_codes ?? [];
        if (in_array($token, $backupCodes)) {
            // Remove used backup code
            $backupCodes = array_diff($backupCodes, [$token]);
            $twoFactorAuth->update(['backup_codes' => array_values($backupCodes)]);

            return true;
        }

        // Verify OTP token (allow 1 minute window)
        $isValid = $this->google2fa->verifyKey($twoFactorAuth->secret, $token, 1);

        if ($isValid) {
            $twoFactorAuth->update(['last_used_at' => now()]);
        }

        return $isValid;
    }

    /**
     * Confirm 2FA setup
     */
    public function confirmSetup(User $user, string $token): bool
    {
        $twoFactorAuth = $user->twoFactorAuth;

        if (! $twoFactorAuth) {
            return false;
        }

        if ($this->google2fa->verifyKey($twoFactorAuth->secret, $token, 1)) {
            $twoFactorAuth->update([
                'verified' => true,
                'verified_at' => now(),
            ]);

            $user->update(['two_factor_enabled' => true]);

            return true;
        }

        return false;
    }

    /**
     * Disable 2FA for user
     */
    public function disableTwoFactor(User $user): bool
    {
        $user->update(['two_factor_enabled' => false]);
        $user->twoFactorAuth?->delete();

        return true;
    }

    /**
     * Generate QR code for authenticator app
     */
    public function generateQRCode(User $user, string $secret): string
    {
        return $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );
    }

    /**
     * Generate backup codes
     */
    protected function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        return $codes;
    }
}
