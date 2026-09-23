<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * 2FA Configuration
 *
 * @property int $id
 * @property int $user_id
 * @property string $secret
 * @property array<array-key, mixed>|null $backup_codes
 * @property bool $verified
 * @property string|null $verified_at
 * @property string|null $last_used_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereBackupCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereLastUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TwoFactorAuth whereVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
class TwoFactorAuth extends Model
{
    protected $table = 'two_factor_auths';

    protected $fillable = [
        'user_id',
        'secret',
        'backup_codes',
        'verified',
    ];

    protected $casts = [
        'backup_codes' => 'array',
        'verified' => 'boolean',
    ];

    protected $hidden = ['secret'];

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
