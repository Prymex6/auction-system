<?php

namespace App\Repositories;

use App\Models\Report;
use App\Models\User;

class ReportRepository
{
    public function create(array $data): Report
    {
        return Report::create($data);
    }

    public function getById(int $id): ?Report
    {
        return Report::with(['reporter'])->find($id);
    }

    public function getAll(int $perPage = 15)
    {
        return Report::with(['reporter'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getPending(int $perPage = 15)
    {
        return Report::where('status', 'pending')
            ->with(['reporter'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getByStatus(string $status, int $perPage = 15)
    {
        return Report::where('status', $status)
            ->with(['reporter'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function getByType(string $type, int $perPage = 15)
    {
        return Report::where('reportable_type', $type)
            ->with(['reporter'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function update(Report $report, array $data): bool
    {
        return $report->update($data);
    }

    public function delete(Report $report): bool
    {
        return $report->delete();
    }

    public function userHasReported(User $user, string $type, int $itemId): bool
    {
        return Report::where('reported_by', $user->id)
            ->where('reportable_type', $type)
            ->where('reportable_id', $itemId)
            ->exists();
    }

    public function getStats(): array
    {
        return [
            'total' => Report::count(),
            'pending' => Report::where('status', 'pending')->count(),
            'approved' => Report::where('status', 'resolved')->count(),
            'rejected' => Report::where('status', 'dismissed')->count(),
        ];
    }
}
