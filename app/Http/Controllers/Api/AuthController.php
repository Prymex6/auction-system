<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailMail;
use App\Models\PlatformSetting;
use App\Models\User;
use App\Services\Auth\OAuthService;
use App\Services\Auth\TwoFactorAuthService;
use App\Services\DeviceService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private OAuthService $oauthService,
        private TwoFactorAuthService $twoFactorService
    ) {}

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users|regex:/^[a-zA-Z0-9_-]+$/',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'terms' => 'accepted',
        ], [
            'terms.accepted' => 'Musisz zaakceptować regulamin i politykę prywatności',
        ]);

        $postcode = $validated['postcode'] ?? $validated['postal_code'] ?? null;

        $freeAuctionLimit = PlatformSetting::first()?->free_user_auction_limit ?? 2;

        $user = User::create([
            'name' => $validated['name'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'postcode' => $postcode,
            'country' => $validated['country'] ?? null,
            'listings_free_count' => $freeAuctionLimit,
            'terms_accepted_at' => now(),
        ]);

        $newToken = $user->createToken('api-token');
        $token = $newToken->plainTextToken;

        // Rejestruj device
        DeviceService::registerOrUpdate($user, $request->userAgent(), $request->ip(), $newToken->accessToken->id);

        app(PushNotificationService::class)->sendToAdmins(
            'Nowa rejestracja',
            "Nowe konto: {$user->name} ({$user->email})",
            '/admin'
        );

        return response()->json([
            'message' => 'Rejestracja pomyślna',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // login (name lub email)
            'password' => 'required',
        ]);

        $user = User::where('name', $request->login)
            ->orWhere('email', $request->login)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'error' => 'Niepoprawne dane logowania',
            ], 401);
        }

        if ($user->isBanned()) {
            return response()->json([
                'message' => 'Twoje konto zostało zablokowane',
                'reason' => $user->ban_reason,
            ], 403);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            return response()->json([
                'message' => 'Wymagana jest weryfikacja 2FA',
                'requires_2fa' => true,
                'user_id' => $user->id,
            ], 200);
        }

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $newToken = $user->createToken('api-token');
        $token = $newToken->plainTextToken;

        // Rejestruj device
        DeviceService::registerOrUpdate($user, $request->userAgent(), $request->ip(), $newToken->accessToken->id);

        return response()->json([
            'message' => 'Logowanie pomyślne',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ], 200);
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Adres e-mail jest już potwierdzony'], 200);
        }

        Mail::queue(new VerifyEmailMail($user));

        return response()->json(['message' => 'Link weryfikacyjny został wysłany ponownie'], 200);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        } else {
            $request->user()->tokens()->delete();
        }

        return response()->json([
            'message' => 'Wylogowanie pomyślne',
        ], 200);
    }

    /**
     * Weryfikuj kod 2FA
     */
    public function verify2FA(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'code' => 'required|string|min:6|max:10',
        ]);

        $user = User::findOrFail($request->user_id);

        if (! $this->twoFactorService->verifyToken($user, $request->code)) {
            throw ValidationException::withMessages([
                'code' => ['Błędny kod 2FA'],
            ]);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $newToken = $user->createToken('api-token');
        $token = $newToken->plainTextToken;

        DeviceService::registerOrUpdate($user, $request->userAgent(), $request->ip(), $newToken->accessToken->id);

        return response()->json([
            'message' => '2FA zweryfikowana pomyślnie',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    /**
     * Skonfiguruj 2FA
     */
    public function setup2FA(Request $request)
    {
        $user = $request->user();
        $setup = $this->twoFactorService->setupTwoFactor($user);

        return response()->json([
            'message' => 'Zapisz kody zapasowe i zeskanuj kod QR',
            'secret' => $setup['secret'],
            'backup_codes' => $setup['backup_codes'],
            'qr_code' => $setup['qr_code'],
        ], 200);
    }

    public function confirm2FA(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($this->twoFactorService->confirmSetup($user, $request->code)) {
            return response()->json([
                'message' => '2FA włączona pomyślnie',
            ], 200);
        }

        throw ValidationException::withMessages([
            'code' => ['Błędny kod'],
        ]);
    }

    public function disable2FA(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Hasło jest błędne'],
            ]);
        }

        $this->twoFactorService->disableTwoFactor($user);

        return response()->json([
            'message' => '2FA wyłączona pomyślnie',
        ], 200);
    }
}
