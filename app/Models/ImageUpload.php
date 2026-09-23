<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $path
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ImageUpload whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ImageUpload extends Model
{
    protected $fillable = [
        'path',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
