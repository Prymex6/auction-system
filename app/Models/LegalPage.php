<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * ..)
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalPage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class LegalPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
    ];
}
