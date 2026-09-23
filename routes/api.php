<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PageViewController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserDeviceController;
use App\Http\Controllers\Api\WatchlistController;
use App\Models\Auction;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::get('stats', function () {
    return Cache::remember('platform-stats', 300, function () {
        return [
            'active_auctions' => Auction::where('status', 'active')->count(),
            'breeders' => User::count(),
            'ended_auctions' => Auction::where('status', 'ended')->count(),
        ];
    });
});

// ===== PUBLIC ROUTES =====

// Auth routes (public)
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('login', [AuthController::class, 'login'])->middleware('dynamic-login-throttle');
    Route::post('verify-2fa', [AuthController::class, 'verify2FA'])->middleware('throttle:10,1');
});

// Public auctions (read-only)
Route::group(['prefix' => 'auctions'], function () {
    Route::get('/', [AuctionController::class, 'index']);
    Route::get('search', [AuctionController::class, 'search']);
    Route::middleware('auth:sanctum')->get('my', [AuctionController::class, 'userAuctions']);
    Route::get('{auction}', [AuctionController::class, 'show']);
    Route::get('{auction}/bids', [BidController::class, 'getByAuction']);
});

// Public user profiles
Route::group(['prefix' => 'users'], function () {
    Route::get('{user}', [UserController::class, 'show']);
    Route::get('{user}/auctions', [UserController::class, 'auctions']);
    Route::get('{user}/reviews', [UserController::class, 'reviews']);
    Route::get('{user}/stats', [UserController::class, 'stats']);
});

// Public reviews
Route::group(['prefix' => 'reviews'], function () {
    Route::get('/', [ReviewController::class, 'index']);
});

// Public blogs
Route::group(['prefix' => 'blogs'], function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('{blogPost}', [BlogController::class, 'show']);
});

// Public platform settings
Route::get('settings', [SettingsController::class, 'index']);

// Publiczna lista ras (filtry i formularze)
Route::get('breeds', function () {
    return response()->json([
        'data' => Auction::where('status', 'active')
            ->whereNotNull('breed')
            ->distinct()
            ->orderBy('breed')
            ->pluck('breed'),
    ]);
});

// Newsletter (zapis publiczny — zgoda RODO, throttle)
Route::post('newsletter', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,60');
// wysylany mailem, nie surowy POST z dowolnym adresem w body.

Route::post('track', [PageViewController::class, 'store'])->middleware('throttle:120,1');

Route::get('push/vapid-public-key', [PushSubscriptionController::class, 'vapidPublicKey']);

// Public static pages (regulamin, polityka prywatnosci, FAQ...)
Route::get('pages', [PageController::class, 'index']);
Route::get('pages/{slug}', [PageController::class, 'show']);

// Public categories (including featured smart categories)
Route::get('categories/featured', function () {
    $categories = Category::where('is_featured', true)
        ->orWhere('show_as_home_section', true)
        ->get()
        ->map(function ($category) {
            $auctions = [];

            if ($category->category_type === 'smart') {
                $auctionModels = $category->getSmartAuctions();
            } else {
                $auctionModels = $category->auctions()->where('status', 'active')->get();
                if ($auctionModels->isEmpty()) {
                    $auctionModels = $category->auctions()->limit(6)->get();
                }
            }

            $auctions = $auctionModels->map(function ($auction) {
                // Ensure pigeon_images is properly formatted as array
                $pigeonImages = $auction->pigeon_images ?? [];
                if (! is_array($pigeonImages)) {
                    $pigeonImages = json_decode($pigeonImages, true) ?? [];
                }

                return [
                    'id' => $auction->id,
                    'title' => $auction->title ?? 'Bez tytułu',
                    'type' => $auction->type ?? 'auction',
                    'status' => $auction->status ?? 'active',
                    'gender' => $auction->gender,
                    'breed' => $auction->breed,
                    'ring_number' => $auction->ring_number,
                    'color' => $auction->color,
                    'current_price' => $auction->current_price ?? $auction->start_price ?? 0,
                    'start_price' => $auction->start_price,
                    'bids_count' => $auction->bids_count ?? 0,
                    'ends_at' => $auction->ends_at,
                    'started_at' => $auction->started_at,
                    'pigeon_images' => $pigeonImages,
                    'images' => method_exists($auction, 'images') ? ($auction->images->pluck('path')->toArray() ?? []) : [],
                    'seller_name' => $auction->seller?->name ?? 'Nieznany',
                    'seller_id' => $auction->user_id,
                ];
            })->take(6)->values();

            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'category_type' => $category->category_type,
                'is_featured' => (bool) $category->is_featured,
                'show_as_home_section' => (bool) $category->show_as_home_section,
                'auctions' => $auctions,
            ];
        });

    return response()->json(['success' => true, 'data' => $categories]);
});

// ===== PROTECTED ROUTES =====

Route::middleware(['auth:sanctum', 'ensure-user-exists'])->group(function () {
    // Images
    Route::group(['prefix' => 'images'], function () {
        Route::post('upload', [ImageController::class, 'upload']);
        Route::post('{path}/delete', [ImageController::class, 'delete'])->where('path', '.*');
        Route::get('{path}/info', [ImageController::class, 'info'])->where('path', '.*');
    });

    // Auth - 2FA Management
    Route::group(['prefix' => 'auth'], function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('2fa/setup', [AuthController::class, 'setup2FA']);
        Route::post('2fa/confirm', [AuthController::class, 'confirm2FA']);
        Route::post('2fa/disable', [AuthController::class, 'disable2FA']);
        Route::post('email/verification-notification', [AuthController::class, 'resendVerificationEmail'])->middleware('throttle:3,10');
    });

    Route::group(['prefix' => 'push'], function () {
        Route::post('subscribe', [PushSubscriptionController::class, 'subscribe']);
        Route::post('unsubscribe', [PushSubscriptionController::class, 'unsubscribe']);
    });

    // Profile management
    Route::group(['prefix' => 'profile'], function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::patch('/', [ProfileController::class, 'update']);
        Route::post('change-password', [ProfileController::class, 'changePassword']);
        Route::get('login-history', [ProfileController::class, 'loginHistory']);
        Route::get('notification-preferences', [ProfileController::class, 'notificationPreferences']);
        Route::patch('notification-preferences', [ProfileController::class, 'updateNotificationPreferences']);
        Route::get('export', [ProfileController::class, 'export']);
        Route::delete('/', [ProfileController::class, 'destroy']);
    });

    // Auctions
    Route::group(['prefix' => 'auctions'], function () {
        Route::post('/', [AuctionController::class, 'store'])->middleware(['user-active', 'require-2fa']);
        Route::put('{auction}', [AuctionController::class, 'update'])->middleware('user-active');
        Route::delete('{auction}', [AuctionController::class, 'destroy'])->middleware('user-active');
        Route::post('{auction}/bid', [BidController::class, 'placeFromAuction'])->middleware(['user-active', 'require-2fa']);
        // Route::post('{auction}/buy', [AuctionController::class, 'buyNow'])->middleware(['user-active', 'require-2fa']);
        Route::patch('{auction}/complete', [AuctionController::class, 'complete'])->middleware('user-active');
    });

    Route::post('reports', [ReportController::class, 'store'])->middleware(['user-active', 'throttle:10,60']);

    // Bids
    Route::group(['prefix' => 'bids'], function () {
        Route::post('auction/{auction}', [BidController::class, 'store'])->middleware(['user-active', 'require-2fa']);
        Route::post('auction/{auction}/auto-bid', [BidController::class, 'placeAutoBid'])->middleware(['user-active', 'require-2fa']);
        Route::get('my', [BidController::class, 'userBids']);
    });

    // Notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('{notification}', [NotificationController::class, 'destroy']);
    });

    // Watchlist
    Route::group(['prefix' => 'watchlist'], function () {
        Route::get('/', [WatchlistController::class, 'index']);
        Route::post('auction/{auction}', [WatchlistController::class, 'store'])->middleware('user-active');
        Route::delete('auction/{auction}', [WatchlistController::class, 'destroy'])->middleware('user-active');
        Route::get('auction/{auction}/check', [WatchlistController::class, 'check']);
    });

    // Messages
    Route::group(['prefix' => 'messages'], function () {
        Route::get('conversations', [MessageController::class, 'conversations']);
        Route::get('user/{user}', [MessageController::class, 'show']);
        Route::post('user/{user}', [MessageController::class, 'store'])->middleware('user-active');
        Route::post('{message}/read', [MessageController::class, 'markAsRead']);
        Route::delete('{message}', [MessageController::class, 'destroy']);
        Route::get('unread-count', [MessageController::class, 'unreadCount']);
    });

    // Reviews
    Route::group(['prefix' => 'reviews', 'middleware' => 'user-active'], function () {
        Route::post('/', [ReviewController::class, 'store']);
        Route::patch('{review}', [ReviewController::class, 'update']);
        Route::delete('{review}', [ReviewController::class, 'destroy']);
    });
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth:sanctum', 'is-admin'])->group(function () {
    Route::group(['prefix' => 'admin'], function () {

        // Dashboard
        Route::get('dashboard', [AdminController::class, 'dashboard']);

        Route::get('analytics', [AnalyticsController::class, 'index']);

        // Users
        Route::get('users', [AdminController::class, 'users']);
        Route::post('users', [AdminController::class, 'createUser']);
        Route::patch('users/{id}', [AdminController::class, 'updateUser']);
        Route::patch('users/{id}/activate', [AdminController::class, 'activateUser']);
        Route::post('users/{id}/ban', [AdminController::class, 'banUser']);
        Route::post('users/{id}/ban-temporary', [AdminController::class, 'banUserTemporary']);
        Route::post('users/{id}/unban', [AdminController::class, 'unbanUser']);
        Route::delete('users/{id}', [AdminController::class, 'deleteUser']);

        // Auctions
        Route::get('auctions', [AdminController::class, 'auctions']);
        Route::post('auctions', [AdminController::class, 'createAuction']);
        Route::post('auctions-with-listing', [AdminController::class, 'createAuctionWithListing']);
        Route::patch('auctions/{id}', [AdminController::class, 'updateAuction']);
        Route::delete('auctions/{id}', [AdminController::class, 'deleteAuction']);
        Route::post('auctions/{id}/approve', [AdminController::class, 'approveAuction']);
        Route::post('auctions/{id}/reject', [AdminController::class, 'rejectAuction']);
        Route::post('auctions/{id}/activate', [AdminController::class, 'activateAuction']);

        // Reports
        Route::get('reports', [AdminController::class, 'reports']);
        Route::get('reports/{id}', [AdminController::class, 'getReport']);
        Route::patch('reports/{id}', [AdminController::class, 'updateReport']);

        Route::get('newsletter', [NewsletterController::class, 'index']);

        Route::patch('pages/{slug}', [PageController::class, 'update']);

        // Audit Logs
        Route::get('audit-logs', [AdminController::class, 'auditLogs']);
        Route::get('audit-logs/stats', [AdminController::class, 'auditStats']);

        Route::group(['prefix' => 'error-logs'], function () {
            Route::get('/', [ErrorLogController::class, 'index']);
            Route::get('{errorLog}', [ErrorLogController::class, 'show']);
            Route::patch('{errorLog}/resolve', [ErrorLogController::class, 'resolve']);
            Route::delete('{errorLog}', [ErrorLogController::class, 'destroy']);
        });

        // Categories (Smart Categories)
        Route::get('categories', [AdminController::class, 'getCategories']);
        Route::post('categories', [AdminController::class, 'storeCategory']);
        Route::patch('categories/{category}', [AdminController::class, 'updateCategory']);
        Route::delete('categories/{category}', [AdminController::class, 'deleteCategory']);

        // Blogs
        Route::group(['prefix' => 'blogs'], function () {
            Route::post('/', [BlogController::class, 'store']);
            Route::patch('{blogPost}', [BlogController::class, 'update']);
            Route::delete('{blogPost}', [BlogController::class, 'destroy']);
        });

        // Platform Settings (admin only - update)
        Route::patch('settings', [SettingsController::class, 'update']);
        Route::post('settings/reset', [SettingsController::class, 'reset']);
    });
});

// User Settings (authenticated)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('devices', [UserDeviceController::class, 'index']);
    Route::delete('devices/{device}', [UserDeviceController::class, 'destroy']);
    Route::post('devices/logout-others', [UserDeviceController::class, 'destroyOthers']);

    Route::post('users/{user}/block', [UserController::class, 'block']);
    Route::delete('users/{user}/block', [UserController::class, 'unblock']);

    Route::post('users/{user}/rate', [ReviewController::class, 'rateProfile'])->middleware('throttle:20,1');
});
