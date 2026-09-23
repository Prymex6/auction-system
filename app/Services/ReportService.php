<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\Message;
use App\Models\Report;
use App\Models\User;

class ReportService
{
    private function resolveReportableType(string $type): string
    {
        return match ($type) {
            'auction', 'Auction', Auction::class => Auction::class,
            'user', 'User', User::class => User::class,
            'message', 'Message', Message::class => Message::class,
            default => $type,
        };
    }

    public function createReport(
        User $reporter,
        string $type,
        int $reportableId,
        string $reason,
        ?string $description = null
    ): Report {
        return Report::create([
            'reported_by' => $reporter->id,
            'reportable_type' => $this->resolveReportableType($type),
            'reportable_id' => $reportableId,
            'reason' => $reason,
            'description' => $description,
            'status' => 'pending',
        ]);
    }

    public function resolveReport(Report $report, string $action, string $reason): bool
    {
        $status = $action === 'approved' ? 'resolved' : 'dismissed';

        $this->performAction($report, $action);

        return $report->update([
            'status' => $status,
            'resolution_reason' => $reason,
            'resolved_at' => now(),
        ]);
    }

    private function performAction(Report $report, string $action): void
    {
        if ($action === 'rejected') {
            return;
        }

        $type = $report->reportable_type;
        if ($type === Auction::class) {
            $this->handleAuctionReport($report);
        } elseif ($type === User::class) {
            $this->handleUserReport($report);
        } elseif ($type === Message::class) {
            $this->handleMessageReport($report);
        }
    }

    private function handleAuctionReport(Report $report): void
    {
        $auction = $report->reportable_type === Auction::class
            ? Auction::find($report->reportable_id)
            : null;
        if ($auction) {
            $auction->update([
                'status' => 'cancelled',
                'rejection_reason' => $report->reason,
            ]);
        }
    }

    private function handleUserReport(Report $report): void
    {
        $user = $report->reportable_type === User::class
            ? User::find($report->reportable_id)
            : null;
        if ($user) {
            $user->update([
                'is_banned' => true,
                'ban_reason' => 'Reported for: '.$report->reason,
            ]);
        }
    }

    private function handleMessageReport(Report $report): void
    {
        $message = $report->reportable_type === Message::class
            ? Message::find($report->reportable_id)
            : null;
        if ($message) {
            $message->update(['is_approved' => false]);
        }
    }

    public function getReportsStats(): array
    {
        return [
            'total_reports' => Report::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'approved_reports' => Report::where('status', 'resolved')->count(),
            'rejected_reports' => Report::where('status', 'dismissed')->count(),
            'by_type' => Report::selectRaw('reportable_type, COUNT(*) as count')
                ->groupBy('reportable_type')
                ->pluck('count', 'reportable_type')
                ->toArray(),
            'by_reason' => Report::selectRaw('reason, COUNT(*) as count')
                ->groupBy('reason')
                ->pluck('count', 'reason')
                ->toArray(),
        ];
    }

    public function hasUserReportedItem(User $user, string $type, int $itemId): bool
    {
        return Report::where('reported_by', $user->id)
            ->where('reportable_type', $this->resolveReportableType($type))
            ->where('reportable_id', $itemId)
            ->exists();
    }

    public function canUserReport(User $user): bool
    {
        // Users can report max 5 items per day
        $reportsToday = Report::where('reported_by', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        return $reportsToday < 5;
    }
}
