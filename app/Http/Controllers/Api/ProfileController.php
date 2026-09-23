<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ImageUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'data' => new UserResource($request->user()->load(['devices', 'notificationPreferences'])),
        ]);
    }

    /**
     * Aktualizuj profil
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'string|max:255|regex:/^[a-zA-Z0-9_-]+$/|unique:users,name,'.$request->user()->id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|max:2048',
            'is_public' => 'nullable|boolean',
        ]);

        $user = $request->user();
        $user->update($validated);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return response()->json([
            'message' => 'Profil zaktualizowany',
            'data' => new UserResource($user),
        ]);
    }

    public function changePassword(Request $request)
    {
        $passwordRule = ['nullable', 'confirmed', Password::min(8)->letters()->numbers()];
        $request->validate([
            'current_password' => 'required',
            'password' => $passwordRule,
            'new_password' => $passwordRule,
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Hasło jest błędne'],
            ]);
        }

        $newPassword = $request->input('new_password') ?? $request->input('password');
        $user->update(['password' => Hash::make($newPassword)]);

        return response()->json([
            'message' => 'Hasło zmienione pomyślnie',
        ]);
    }

    public function loginHistory(Request $request)
    {
        $limit = $request->get('limit', 20);

        $devices = $request->user()->devices()
            ->orderByDesc('last_activity_at')
            ->paginate($limit);

        return response()->json([
            'data' => $devices,
        ]);
    }

    public function notificationPreferences(Request $request)
    {
        $prefs = $request->user()->notificationPreferences()->firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'email_notifications' => true,
                'sound_notifications' => true,
                'push_notifications' => true,
                'bid_notifications' => true,
                'message_notifications' => true,
                'auction_end_notifications' => true,
            ]
        );

        return response()->json([
            'data' => $prefs,
        ]);
    }

    public function updateNotificationPreferences(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'sometimes|boolean',
            'sound_notifications' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
            'bid_notifications' => 'sometimes|boolean',
            'message_notifications' => 'sometimes|boolean',
            'auction_end_notifications' => 'sometimes|boolean',
        ]);

        $prefs = $request->user()->notificationPreferences()->firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'email_notifications' => true,
                'sound_notifications' => true,
                'push_notifications' => true,
                'bid_notifications' => true,
                'message_notifications' => true,
                'auction_end_notifications' => true,
            ]
        );
        $prefs->update($validated);

        return response()->json([
            'message' => 'Preferencje zaktualizowane',
            'data' => $prefs,
        ]);
    }

    /**
     * urzadzenia, avatar).
     */
    public function destroy(Request $request)
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

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            ImageUpload::where('path', $user->avatar)->delete();
        }

        $user->twoFactorAuth?->delete();
        $user->watchlist()->delete();
        $user->notifications()->delete();
        $user->devices()->delete();
        $user->tokens()->delete();

        $anonymousEmail = 'usuniety+'.$user->id.'@usuniety.golebiowylot.pl';

        $user->update([
            'name' => 'Użytkownik usunięty',
            'first_name' => null,
            'last_name' => null,
            'email' => $anonymousEmail,
            'password' => Hash::make(bin2hex(random_bytes(32))),
            'avatar' => null,
            'phone' => null,
            'bio' => null,
            'address' => null,
            'city' => null,
            'postcode' => null,
            'country' => null,
            'is_public' => false,
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        $user->delete();

        return response()->json([
            'message' => 'Konto zostało usunięte',
        ]);
    }

    public function export(Request $request)
    {
        $user = $request->user();

        $data = [
            'wygenerowano' => now()->toIso8601String(),
            'konto' => [
                'id' => $user->id,
                'login' => $user->name,
                'imie' => $user->first_name,
                'nazwisko' => $user->last_name,
                'email' => $user->email,
                'telefon' => $user->phone,
                'bio' => $user->bio,
                'adres' => $user->address,
                'miasto' => $user->city,
                'kod_pocztowy' => $user->postcode,
                'kraj' => $user->country,
                'profil_publiczny' => (bool) $user->is_public,
                'reputacja' => $user->reputation,
                'konto_utworzone' => $user->created_at,
            ],
            'aukcje_wystawione' => $user->auctions()
                ->get(['id', 'title', 'status', 'start_price', 'current_price', 'created_at']),
            'oferty_licytacji' => $user->bids()
                ->get(['id', 'auction_id', 'amount', 'created_at']),
            'wiadomosci_wyslane' => $user->sentMessages()
                ->get(['id', 'recipient_id', 'content', 'created_at']),
            'wiadomosci_otrzymane' => $user->receivedMessages()
                ->get(['id', 'sender_id', 'content', 'created_at']),
            'recenzje_wystawione' => $user->givenReviews()
                ->get(['id', 'to_user_id', 'auction_id', 'rating', 'comment', 'created_at']),
            'recenzje_otrzymane' => $user->receivedReviews()
                ->get(['id', 'from_user_id', 'auction_id', 'rating', 'comment', 'created_at']),
            'lista_obserwowanych' => $user->watchlist()->pluck('auction_id'),
            'powiadomienia' => $user->notifications()
                ->get(['id', 'type', 'title', 'message', 'created_at']),
            'urzadzenia' => $user->devices()
                ->get(['device_name', 'device_type', 'ip_address', 'last_activity_at']),
        ];

        $filename = 'moje-dane-golebiowylot-'.$user->id.'-'.now()->format('Y-m-d').'.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
