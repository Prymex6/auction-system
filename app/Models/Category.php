<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string $category_type
 * @property array<array-key, mixed>|null $smart_filter
 * @property bool $is_featured
 * @property bool $show_as_home_section
 * @property-read Collection<int, Auction> $auctions
 * @property-read int|null $auctions_count
 *
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCategoryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereShowAsHomeSection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSmartFilter($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_type',
        'smart_filter',
        'is_featured',
        'show_as_home_section',
    ];

    protected function casts(): array
    {
        return [
            'smart_filter' => 'array',
            'is_featured' => 'boolean',
            'show_as_home_section' => 'boolean',
        ];
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    public function getSmartAuctions()
    {
        if ($this->category_type !== 'smart' || ! $this->smart_filter) {
            return collect();
        }

        $query = Auction::with(['seller'])
            ->where('status', 'active');

        if (isset($this->smart_filter['type']) && $this->smart_filter['type'] !== null) {
            if ($this->smart_filter['type'] === 'auction') {
                $query->where(function ($q) {
                    $q->where('type', 'auction')->orWhereNull('type');
                });
            } else {
                $query->where('type', $this->smart_filter['type']);
            }
        }

        // Support for year filter (young pigeons)
        if (isset($this->smart_filter['year'])) {
            $query->where('year', $this->smart_filter['year']);
        }

        if (isset($this->smart_filter['breed'])) {
            $query->where('breed', $this->smart_filter['breed']);
        }

        if (isset($this->smart_filter['min_price'])) {
            $query->where('current_price', '>=', $this->smart_filter['min_price']);
        }

        if (isset($this->smart_filter['max_price'])) {
            $query->where('current_price', '<=', $this->smart_filter['max_price']);
        }

        if (isset($this->smart_filter['user_id'])) {
            $query->where('user_id', $this->smart_filter['user_id']);
        }

        // Smart sorting based on sort_by
        $sortBy = $this->smart_filter['sort_by'] ?? 'latest';

        switch ($sortBy) {
            case 'bids_count_desc':
                $query->orderBy('bids_count', 'desc')->latest();
                break;

            case 'price_desc':
                $query->orderBy('current_price', 'desc')->latest();
                break;

            case 'price_asc':
                $query->orderBy('current_price', 'asc')->latest();
                break;

            case 'ending_soon':
                $query->orderBy('ends_at', 'asc');
                break;

            case 'seller_reputation':
                $query->orderByDesc(
                    Review::selectRaw('AVG(rating)')
                        ->whereColumn('to_user_id', 'auctions.user_id')
                )->latest();
                break;

            case 'newest':
            case 'latest':
            default:
                // Sortowanie po dacie dodania - najnowsze najpierw
                $query->latest();
                break;
        }

        return $query->get();
    }

    /**
     * Wygeneruj slug z nazwy
     */
    public static function generateSlug(string $name): string
    {
        return str($name)
            ->slug()
            ->toString();
    }
}
