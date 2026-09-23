<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $path
 * @property string $visitor_hash
 * @property Carbon $viewed_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView whereViewedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PageView whereVisitorHash($value)
 *
 * @mixin \Eloquent
 */
class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'path',
        'visitor_hash',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];
}
