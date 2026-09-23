<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $settings = PlatformSetting::first();
        if (! ($settings?->enable_user_reports ?? true)) {
            return response()->json([
                'message' => 'Zgłaszanie ogłoszeń jest obecnie wyłączone',
            ], 403);
        }

        $validated = $request->validate([
            'auction_id' => 'required|integer|exists:auctions,id',
            'reason' => 'required|string|in:spam,fraud,inappropriate,illegal,dead_pigeon,other',
            'description' => 'required|string|min:10|max:2000',
        ]);

        $exists = Report::where('reported_by', $request->user()->id)
            ->where('reportable_type', Auction::class)
            ->where('reportable_id', $validated['auction_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Zgłosiłeś już tę aukcję — administracja ją sprawdzi',
            ], 422);
        }

        $report = Report::create([
            'reported_by' => $request->user()->id,
            'reportable_type' => Auction::class,
            'reportable_id' => $validated['auction_id'],
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => 'pending',
        ]);

        $this->maybeAutoBanSeller($validated['auction_id'], $settings);

        app(PushNotificationService::class)->sendToAdmins(
            'Nowe zgłoszenie',
            'Zgłoszono aukcję #'.$validated['auction_id'].' ('.$validated['reason'].')',
            '/admin'
        );

        return response()->json([
            'message' => 'Zgłoszenie zostało wysłane — dziękujemy',
            'data' => ['id' => $report->id],
        ], 201);
    }

    private function maybeAutoBanSeller(int $auctionId, ?PlatformSetting $settings): void
    {
        $threshold = $settings?->auto_ban_reports_threshold;
        if (! $threshold) {
            return;
        }

        $auction = Auction::find($auctionId);
        if (! $auction || ! $auction->seller || $auction->seller->is_banned) {
            return;
        }

        $reportsAgainstSeller = Report::where('reportable_type', Auction::class)
            ->whereIn('reportable_id', Auction::where('user_id', $auction->user_id)->pluck('id'))
            ->count();

        if ($reportsAgainstSeller >= $threshold) {
            $durationHours = $settings?->auto_ban_duration_hours;
            $auction->seller->update([
                'is_banned' => true,
                'ban_reason' => "Automatyczna blokada: przekroczono próg {$threshold} zgłoszeń",
                'ban_until' => $durationHours ? now()->addHours($durationHours) : null,
            ]);
        }
    }
}
