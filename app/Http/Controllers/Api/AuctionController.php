<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuctionResource;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Notification;
use App\Models\PlatformSetting;
use App\Repositories\Contracts\AuctionRepositoryInterface;
use App\Services\PushNotificationService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuctionController extends Controller
{
    public function __construct(
        private AuctionRepositoryInterface $auctionRepository
    ) {}

    public function index(Request $request)
    {
        $limit = (int) $request->get('per_page', $request->get('limit', 20));
        $limit = max(1, min($limit, 200));
        $page = (int) $request->get('page', 1);
        $filter = $request->get('filter', 'active'); // active, ending-soon, all
        $categoryParam = $request->get('category');
        $search = trim((string) $request->get('search', $request->get('q', '')));
        $sort = $request->get('sort');

        // If category is specified (by id or slug), get auctions from that category
        if ($categoryParam) {
            $category = Category::where('slug', $categoryParam)
                ->when(is_numeric($categoryParam), fn ($q) => $q->orWhere('id', (int) $categoryParam))
                ->first();

            if (! $category) {
                return response()->json(['data' => [], 'meta' => null]);
            }

            // Get auctions based on category type
            if ($category->category_type === 'smart') {
                $allAuctions = $category->getSmartAuctions();
            } else {
                // Standard category
                $allAuctions = $category->auctions()->where('status', 'active')->get();
                if ($allAuctions->isEmpty()) {
                    $allAuctions = $category->auctions()->limit(6)->get();
                }
            }

            // Apply additional filters if provided
            if ($request->filled('breed')) {
                $allAuctions = $allAuctions->filter(fn ($a) => $a->breed === $request->get('breed'));
            }
            if ($request->filled('gender')) {
                $allAuctions = $allAuctions->filter(fn ($a) => $a->gender === $request->get('gender'));
            }
            if ($search !== '') {
                $needle = mb_strtolower($search);
                $allAuctions = $allAuctions->filter(fn ($a) => str_contains(mb_strtolower($a->title ?? ''), $needle)
                  || str_contains(mb_strtolower($a->breed ?? ''), $needle)
                  || str_contains(mb_strtolower($a->ring_number ?? ''), $needle));
            }

            $allAuctions = match ($sort) {
                'ending' => $allAuctions->sortBy('ends_at'),
                'price_low' => $allAuctions->sortBy(fn ($a) => (float) $a->current_price),
                'price_high' => $allAuctions->sortByDesc(fn ($a) => (float) $a->current_price),
                'newest' => $allAuctions->sortByDesc('created_at'),
                default => $allAuctions,
            };

            // Manual pagination for collection
            $total = $allAuctions->count();
            $perPage = $limit;
            $lastPage = (int) max(1, ceil($total / $perPage));
            $items = $allAuctions->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'data' => AuctionResource::collection($items),
                'meta' => [
                    'current_page' => $page,
                    'last_page' => $lastPage,
                    'per_page' => $perPage,
                    'total' => $total,
                ],
            ]);
        }

        // Standard behavior without category filter
        if ($search !== '' || $sort || $request->hasAny(['breed', 'gender', 'min_price', 'max_price'])) {
            $query = Auction::with('seller')->where('status', 'active')->where('ends_at', '>', now());

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('breed', 'like', "%{$search}%")
                        ->orWhere('ring_number', 'like', "%{$search}%");
                });
            }
            if ($request->filled('breed')) {
                $query->where('breed', $request->get('breed'));
            }
            if ($request->filled('gender')) {
                $query->where('gender', $request->get('gender'));
            }
            if ($request->filled('min_price')) {
                $query->where('current_price', '>=', (float) $request->get('min_price'));
            }
            if ($request->filled('max_price')) {
                $query->where('current_price', '<=', (float) $request->get('max_price'));
            }

            match ($sort) {
                'ending' => $query->orderBy('ends_at', 'asc'),
                'price_low' => $query->orderBy('current_price', 'asc'),
                'price_high' => $query->orderBy('current_price', 'desc'),
                default => $query->latest(),
            };

            $auctions = $query->paginate($limit, ['*'], 'page', $page);
        } else {
            $auctions = match ($filter) {
                'ending-soon' => collect($this->auctionRepository->getEndingSoon(60, $limit)),
                'all' => $this->auctionRepository->getByStatus('active', $limit, $page),
                default => $this->auctionRepository->getActive($limit, $page),
            };
        }

        $items = method_exists($auctions, 'items') ? $auctions->items() : $auctions;

        return response()->json([
            'data' => AuctionResource::collection($items),
            'meta' => method_exists($auctions, 'total') ? [
                'current_page' => $auctions->currentPage(),
                'last_page' => $auctions->lastPage(),
                'per_page' => $auctions->perPage(),
                'total' => $auctions->total(),
            ] : null,
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
            'category_id' => 'nullable|exists:categories,id',
            'min_price' => 'nullable|numeric',
            'max_price' => 'nullable|numeric',
            'limit' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $limit = $validated['limit'] ?? 20;
        $page = $validated['page'] ?? 1;

        $auctions = $this->auctionRepository->search(
            $validated['q'],
            array_filter([
                'category_id' => $validated['category_id'] ?? null,
                'min_price' => $validated['min_price'] ?? null,
                'max_price' => $validated['max_price'] ?? null,
            ]),
            $limit,
            $page
        );

        return response()->json([
            'data' => AuctionResource::collection($auctions),
        ]);
    }

    public function show(Auction $auction)
    {
        return response()->json([
            'data' => new AuctionResource($auction),
        ]);
    }

    public function userAuctions(Request $request)
    {
        $limit = $request->get('limit', 20);
        $page = $request->get('page', 1);

        $auctions = $this->auctionRepository->getBySeller(
            $request->user()->id,
            $limit,
            $page
        );

        return response()->json([
            'data' => AuctionResource::collection($auctions),
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $settings = PlatformSetting::first();
        $hasListingException = ! $user->isAdmin() && $user->can_list_when_restricted;
        if (($settings?->only_admin_can_list ?? false) && ! $user->isAdmin() && ! $hasListingException) {
            return response()->json([
                'message' => 'Wystawianie aukcji jest obecnie dostępne wyłącznie dla administracji serwisu',
            ], 403);
        }

        $maxImages = $settings?->max_images_per_auction ?? 20;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'breed' => 'required|string|max:100',
            'gender' => 'required|in:samiec,samica,golab_mlody',
            'year' => 'required|integer|min:2000|max:'.(date('Y') + 1),
            'size' => 'required|in:maly,sredni,duzy',
            'color' => 'required|string|max:100',
            'ring_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'type' => 'nullable|in:buy_now,auction,both',
            'start_price' => 'required|numeric|min:10',
            'pigeon_images' => 'required|array|min:1|max:'.$maxImages,
            'pigeon_images.*' => 'string',
            'pedigree_images' => 'nullable|array|max:'.$maxImages,
            'pedigree_images.*' => 'string',
        ]);

        if ($hasListingException) {
            $validated['type'] = 'buy_now';
        }

        if (! $user->isAdmin()) {
            $periodLimits = [
                'day' => [$settings?->max_auctions_per_day, now()->startOfDay()],
                'week' => [$settings?->max_auctions_per_week, now()->startOfWeek()],
                'month' => [$settings?->max_auctions_per_month, now()->startOfMonth()],
            ];
            foreach ($periodLimits as $label => [$limit, $since]) {
                if ($limit !== null && $user->auctions()->where('created_at', '>=', $since)->count() >= $limit) {
                    $periodLabel = ['day' => 'dzisiaj', 'week' => 'w tym tygodniu', 'month' => 'w tym miesiącu'][$label];

                    return response()->json([
                        'message' => "Osiągnięto limit wystawionych ogłoszeń ({$periodLabel}). Spróbuj ponownie później.",
                    ], 422);
                }
            }

            if (! app(UserService::class)->canListAuction($user)) {
                return response()->json([
                    'message' => 'Osiągnięto limit aktywnych aukcji dla Twojego konta. Zakończ istniejące aukcje lub przejdź na plan premium.',
                ], 422);
            }
        }

        // Mapowanie color do color_code
        $colorMapping = [
            'Niebieska' => 'NIEB',
            'Niebieska nakrapiana' => 'NNAK',
            'Ciemna nakrapiana' => 'CNAK',
            'Ciemna' => 'CIEM',
            'Czarna' => 'CZAR',
            'Czerwona nakrapiana' => 'CZEN',
            'Czerwona' => 'CZER',
            'Płowa' => 'PLOW',
            'Biała' => 'BIAL',
            'Szpakowata' => 'SZPA',
            'Niebieska pstra' => 'NIEP',
            'Niebieska nakrapiana pstra' => 'NNAP',
            'Ciemna nakrapiana pstra' => 'CNAP',
            'Ciemna pstra' => 'CIEP',
            'Czarna pstra' => 'CZAP',
            'Czerwona nakrapiana pstra' => 'CZNP',
            'Czerwona pstra' => 'CZEP',
            'Płowa pstra' => 'PLOP',
            'Szpakowata pstra' => 'SZPP',
            'Czerwona szpakowata' => 'CZES',
        ];

        $auction = Auction::create([
            'user_id' => $request->user()->id,
            'category_id' => null,
            'type' => $validated['type'] ?? 'auction',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'breed' => $validated['breed'],
            'year' => $validated['year'],
            'gender' => $validated['gender'],
            'color' => $validated['color'],
            'color_code' => $colorMapping[$validated['color']] ?? 'NIEB',
            'ring_number' => $validated['ring_number'] ?? null,
            'size' => $validated['size'],
            'pigeon_images' => $validated['pigeon_images'],
            'pedigree_images' => $validated['pedigree_images'] ?? null,
            'start_price' => $validated['start_price'],
            'current_price' => $validated['start_price'],
            'minimum_increase' => 1.00,
            'started_at' => now(),
            'ends_at' => now()->addDays($settings?->default_auction_duration ?? 7),
            'status' => ($settings?->require_auction_approval ?? true) ? 'pending' : 'active',
        ]);

        if ($auction->status === 'pending') {
            app(PushNotificationService::class)->sendToAdmins(
                'Nowa aukcja do zaakceptowania',
                "{$request->user()->name}: {$auction->title}",
                '/admin?tab=auctions'
            );
        }

        return response()->json([
            'message' => 'Aukcja została utworzona pomyślnie',
            'data' => new AuctionResource($auction),
        ], 201);
    }

    public function buyNow(Request $request, Auction $auction)
    {
        $user = $request->user();

        if ($auction->user_id === $user->id) {
            return response()->json(['message' => 'Nie możesz kupić własnej aukcji'], 403);
        }

        if (! in_array($auction->type, ['buy_now', 'both'])) {
            return response()->json(['message' => 'Ta aukcja nie ma opcji Kup teraz'], 422);
        }

        $result = DB::transaction(function () use ($auction, $user) {
            $locked = Auction::whereKey($auction->id)->lockForUpdate()->first();

            if (! $locked || ! $locked->isActive()) {
                return null;
            }

            $locked->update([
                'status' => 'ended',
                'ended_at' => now(),
                'winner_id' => $user->id,
                'final_bid_amount' => $locked->current_price ?? $locked->start_price,
            ]);

            return $locked;
        });

        if (! $result) {
            return response()->json(['message' => 'Aukcja nie jest już dostępna'], 422);
        }

        Notification::create([
            'user_id' => $result->user_id,
            'type' => 'auction_ended',
            'title' => 'Twój gołąb został sprzedany!',
            'message' => "Użytkownik {$user->name} kupił '{$result->title}' za ".number_format((float) $result->final_bid_amount, 0, ',', ' ').' zł. Skontaktujcie się w ciągu 48 godzin.',
            'auction_id' => $result->id,
            'related_user_id' => $user->id,
        ]);
        Notification::create([
            'user_id' => $user->id,
            'type' => 'auction_won',
            'title' => 'Zakup potwierdzony!',
            'message' => "Kupiłeś '{$result->title}'. Skontaktuj się ze sprzedawcą w ciągu 48 godzin, aby ustalić przekazanie.",
            'auction_id' => $result->id,
            'related_user_id' => $result->user_id,
        ]);

        return response()->json([
            'message' => 'Zakup potwierdzony! Skontaktuj się ze sprzedawcą, aby ustalić szczegóły przekazania.',
            'data' => new AuctionResource($result->fresh()),
        ]);
    }

    public function complete(Request $request, Auction $auction)
    {
        if ($auction->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json(['message' => 'Nie masz uprawnień do tej aukcji'], 403);
        }

        if ($auction->status === 'ended') {
            return response()->json(['message' => 'Aukcja jest już zakończona'], 422);
        }

        // wlasciciel sprzeda golebia poza platforma i chce zdjac ogloszenie.
        if ($auction->type !== 'buy_now' && $auction->status === 'active') {
            return response()->json(['message' => 'Nie możesz zakańczać aktywnej licytacji'], 422);
        }

        $auction->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return response()->json([
            'message' => 'Aukcja została zakończona',
            'data' => new AuctionResource($auction->fresh()),
        ]);
    }

    public function update(Request $request, Auction $auction)
    {
        if ($auction->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Nie masz uprawnień do edycji tej aukcji',
            ], 403);
        }

        if ($auction->type !== 'buy_now' && in_array($auction->status, ['active', 'ended'])) {
            return response()->json([
                'message' => 'Nie możesz edytować aktywnej lub zakończonej aukcji',
            ], 422);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'breed' => 'sometimes|required|string|max:100',
            'gender' => 'sometimes|required|in:samiec,samica,golab_mlody',
            'year' => 'sometimes|required|integer|min:2000|max:'.(date('Y') + 1),
            'size' => 'sometimes|required|in:maly,sredni,duzy',
            'color' => 'sometimes|required|string|max:100',
            'ring_number' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'type' => 'nullable|in:buy_now,auction,both',
            'start_price' => 'sometimes|required|numeric|min:10',
            'pigeon_images' => 'nullable|array',
            'pigeon_images.*' => 'string',
            'pedigree_images' => 'nullable|array',
            'pedigree_images.*' => 'string',
        ]);

        $auctionData = array_filter([
            'title' => $validated['title'] ?? null,
            'breed' => $validated['breed'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'year' => $validated['year'] ?? null,
            'size' => $validated['size'] ?? null,
            'color' => $validated['color'] ?? null,
            'ring_number' => $validated['ring_number'] ?? null,
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? null,
            'pigeon_images' => $validated['pigeon_images'] ?? null,
            'pedigree_images' => $validated['pedigree_images'] ?? null,
            'start_price' => $validated['start_price'] ?? null,
        ], static fn ($value) => $value !== null);

        $wasActive = $auction->status === 'active';

        if (! empty($auctionData)) {
            $auction->update($auctionData);
        }

        $requeuedForApproval = false;
        if ($wasActive && ! $request->user()->isAdmin() && ! empty($auctionData)) {
            $requiresApproval = PlatformSetting::first()?->require_auction_approval ?? true;
            if ($requiresApproval) {
                $auction->update(['status' => 'pending']);
                $requeuedForApproval = true;

                app(PushNotificationService::class)->sendToAdmins(
                    'Nowa aukcja do zaakceptowania',
                    "{$request->user()->name} (edycja): {$auction->title}",
                    '/admin?tab=auctions'
                );
            }
        }

        return response()->json([
            'message' => $requeuedForApproval
              ? 'Aukcja została zaktualizowana. Zmiany w już aktywnym ogłoszeniu wymagają ponownej akceptacji administratora - do tego czasu ogłoszenie nie jest publicznie widoczne.'
              : 'Aukcja została zaktualizowana pomyślnie',
            'data' => new AuctionResource($auction),
            'requeued_for_approval' => $requeuedForApproval,
        ]);
    }

    public function destroy(Request $request, Auction $auction)
    {
        if ($auction->user_id !== $request->user()->id && ! $request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Nie masz uprawnień do usunięcia tej aukcji',
            ], 403);
        }

        if (in_array($auction->status, ['active', 'ended'])) {
            return response()->json([
                'message' => 'Nie możesz usunąć aktywnej lub zakończonej aukcji',
            ], 422);
        }

        $auction->delete();

        return response()->json([
            'message' => 'Aukcja została usunięta pomyślnie',
        ]);
    }
}
