<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $reported_by
 * @property string $reportable_type
 * @property int $reportable_id
 * @property string $reason
 * @property string $description
 * @property string $status
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property string|null $resolution_reason
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model|\Eloquent $reportable
 * @property-read User|null $reporter
 * @property-read User|null $reviewer
 *
 * @method static \Database\Factories\ReportFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReportedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereResolutionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReviewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereReviewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Report whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reported_by',
        'reportable_type',
        'reportable_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
        'resolution_reason',
        'notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function resolve(string $reason = '', string $notes = ''): void
    {
        $this->update([
            'status' => 'resolved',
            'reviewed_at' => now(),
            'resolution_reason' => $reason,
            'notes' => $notes,
        ]);
    }

    public function dismiss(string $reason = ''): void
    {
        $this->update([
            'status' => 'dismissed',
            'reviewed_at' => now(),
            'resolution_reason' => $reason,
        ]);
    }
}
