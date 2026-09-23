<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\ImageUpload;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'stats' => [
                'total_users' => User::count(),
                'total_auctions' => Auction::count(),
                'active_auctions' => Auction::where('status', 'active')->count(),
                'banned_users' => User::where('is_banned', true)->count(),
            ],
        ]);
    }

    public function banUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_banned' => true,
            'ban_reason' => $request->input('reason', null),
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'banned',
            'model_type' => 'User',
            'model_id' => $user->id,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
            'meta' => ['reason' => $request->input('reason')],
        ]);

        return response()->json(['message' => 'User banned']);
    }

    public function unbanUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'ban_until' => null,
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'unbanned',
            'model_type' => 'User',
            'model_id' => $user->id,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json(['message' => 'User unbanned']);
    }

    // Listing methods removed - listings merged into auctions

    public function auditLogs()
    {
        $logs = AuditLog::latest()->paginate(15);

        return response()->json($logs);
    }

    public function auditStats(Request $request)
    {
        $byAction = AuditLog::selectRaw('action, count(*) as total')->groupBy('action')->get();
        $byModel = AuditLog::selectRaw('model_type, count(*) as total')->groupBy('model_type')->get();
        $byAdmin = AuditLog::selectRaw('admin_user_id, count(*) as total')->groupBy('admin_user_id')->get();

        return response()->json([
            'total_actions' => AuditLog::count(),
            'by_action' => $byAction,
            'by_model' => $byModel,
            'by_admin' => $byAdmin,
        ]);
    }

    public function users()
    {
        $users = User::withCount('auctions')
            ->latest()
            ->get();

        return response()->json([
            'data' => $users,
        ]);
    }

    public function activateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $requireEmailVerification = PlatformSetting::first()?->require_email_verification ?? true;

        if ($requireEmailVerification && ! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Użytkownik musi najpierw potwierdzić adres e-mail, zanim będzie można aktywować konto',
            ], 422);
        }

        $user->update(['is_active' => true]);

        NotificationService::accountActivated($user);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'activated',
            'model_type' => 'User',
            'model_id' => $user->id,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json(['message' => 'User activated', 'data' => $user]);
    }

    public function deleteUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'Nie możesz usunąć własnego konta z panelu administratora',
            ], 422);
        }

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
            ImageUpload::where('path', $user->avatar)->delete();
        }

        $user->twoFactorAuth?->delete();
        $user->watchlist()->delete();
        $user->notifications()->delete();
        $user->devices()->delete();
        $user->tokens()->delete();

        $anonymousEmail = 'usuniety+'.$user->id.'@usuniety.golebiowylot.pl';

        $user->update([
            'name' => 'Użytkownik usunięty',
            'first_name' => null,
            'last_name' => null,
            'email' => $anonymousEmail,
            'password' => Hash::make(bin2hex(random_bytes(32))),
            'avatar' => null,
            'phone' => null,
            'bio' => null,
            'address' => null,
            'city' => null,
            'postcode' => null,
            'country' => null,
            'is_public' => false,
            'is_active' => false,
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'deleted',
            'model_type' => 'User',
            'model_id' => $user->id,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        $user->delete();

        return response()->json(['message' => 'Konto zostało usunięte']);
    }

    public function auctions()
    {
        $auctions = Auction::with(['seller', 'winner', 'highestBidder'])
            ->withCount('bids')
            ->latest()
            ->get();

        return response()->json([
            'data' => $auctions,
        ]);
    }

    public function reports()
    {
        if (class_exists('App\Models\Report')) {
            $reports = Report::with(['reporter', 'reportable'])
                ->latest()
                ->get()
                ->map(function ($report) {
                    $reportedUser = $this->resolveReportedUser($report);

                    return [
                        'id' => $report->id,
                        'reason' => $report->reason,
                        'description' => $report->description ?? '',
                        'reporter' => [
                            'id' => $report->reporter?->id,
                            'name' => $report->reporter?->name ?? 'Anonymous',
                        ],
                        'reportable_type' => $report->reportable_type,
                        'reportable' => $report->reportable ? [
                            'id' => $report->reportable->id,
                            'name' => $report->reportable->name ?? $report->reportable->title ?? 'N/A',
                        ] : null,
                        'reported_user' => $reportedUser ? [
                            'id' => $reportedUser->id,
                            'name' => $reportedUser->name,
                            'email' => $reportedUser->email,
                            'is_banned' => $reportedUser->is_banned,
                        ] : null,
                        'status' => $report->status,
                        'notes' => $report->notes,
                        'created_at' => $report->created_at,
                        'reviewed_at' => $report->reviewed_at,
                    ];
                });

            return response()->json([
                'data' => $reports,
            ]);
        }

        return response()->json(['data' => []]);
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'is_premium' => 'nullable|boolean',
            'premium_plan' => 'nullable|in:free,1month,3months,12months',
            'premium_until' => 'nullable|date',
            'is_admin' => 'nullable|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($validated);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'created',
            'model_type' => 'User',
            'model_id' => $user->id,
            'meta' => ['email' => $user->email],
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9_-]+$/|unique:users,name,'.$id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$id,
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'email_verified_at' => 'nullable|date',
            'phone_verified_at' => 'nullable|date',
            'listings_free_count' => 'nullable|integer|min:0',
            'is_premium' => 'nullable|boolean',
            'premium_plan' => 'nullable|in:free,1month,3months,12months',
            'premium_until' => 'nullable|date',
            'is_banned' => 'nullable|boolean',
            'ban_reason' => 'nullable|string|max:1000',
            'ban_until' => 'nullable|date',
            'two_factor_enabled' => 'nullable|boolean',
            'last_login_at' => 'nullable|date',
            'last_login_ip' => 'nullable|string|max:45',
            'is_admin' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'can_list_when_restricted' => 'nullable|boolean',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($validated);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'updated',
            'model_type' => 'User',
            'model_id' => $user->id,
            'meta' => $validated,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'User updated',
            'user' => $user,
        ]);
    }

    public function banUserTemporary(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
            'hours' => 'required|integer|min:1|max:8760', // max 1 rok
        ]);

        $banUntil = now()->addHours($validated['hours']);

        $user->update([
            'is_banned' => true,
            'ban_reason' => $validated['reason'],
            'ban_until' => $banUntil,
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'banned_temporary',
            'model_type' => 'User',
            'model_id' => $user->id,
            'meta' => [
                'reason' => $validated['reason'],
                'hours' => $validated['hours'],
                'until' => $banUntil,
            ],
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'User banned temporarily',
            'ban_until' => $banUntil,
        ]);
    }

    public function createAuctionWithListing(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'status' => 'nullable|in:active,ended,pending,cancelled',
            'type' => 'nullable|in:buy_now,auction,both',
            'start_price' => 'required|numeric|min:0',
            'winner_id' => 'nullable|integer|exists:users,id',
            'started_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'sniper_threshold' => 'nullable|integer|min:1',
            'extension_minutes' => 'nullable|integer|min:1',
            'pigeon_images' => 'nullable|array',
            'pedigree_images' => 'nullable|array',
            'title' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'year' => 'nullable|integer|min:1900|max:'.now()->year,
            'gender' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:5000',
        ]);

        $auction = Auction::create([
            'user_id' => $validated['user_id'],
            'category_id' => 1,
            'type' => $validated['type'] ?? 'auction',
            'title' => $validated['title'],
            'breed' => $validated['breed'],
            'year' => $validated['year'] ?? now()->year,
            'gender' => $validated['gender'] ?? 'golab_mlody',
            'color' => $validated['color'] ?? null,
            'size' => $validated['size'] ?? null,
            'description' => $validated['description'] ?? null,
            'pigeon_images' => $validated['pigeon_images'] ?? [],
            'pedigree_images' => $validated['pedigree_images'] ?? [],
            'status' => $validated['status'] ?? 'pending',
            'start_price' => $validated['start_price'],
            'current_price' => $validated['start_price'],
            'winner_id' => $validated['winner_id'] ?? null,
            'started_at' => $validated['started_at'] ?? now(),
            'ends_at' => $validated['ends_at'] ?? now()->addDays(7),
            'anti_sniper_enabled' => true,
            'sniper_threshold' => $validated['sniper_threshold'] ?? 2,
            'extension_minutes' => $validated['extension_minutes'] ?? 5,
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'created',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
            'meta' => ['user_id' => $validated['user_id']],
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'Auction created successfully',
            'auction' => $auction->load('seller'),
        ], 201);
    }

    public function createAuction(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'status' => 'nullable|in:active,ended,pending,cancelled',
            'type' => 'nullable|in:buy_now,auction,both',
            'start_price' => 'required|numeric|min:0',
            'winner_id' => 'nullable|integer|exists:users,id',
            'started_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'sniper_threshold' => 'nullable|integer|min:1',
            'extension_minutes' => 'nullable|integer|min:1',
        ]);

        $auction = Auction::create([
            'user_id' => $validated['user_id'],
            'status' => $validated['status'] ?? 'pending',
            'type' => $validated['type'] ?? 'auction',
            'start_price' => $validated['start_price'],
            'current_price' => $validated['start_price'],
            'winner_id' => $validated['winner_id'] ?? null,
            'started_at' => $validated['started_at'] ?? now(),
            'ends_at' => $validated['ends_at'] ?? now()->addDays(7),
            'anti_sniper_enabled' => true,
            'sniper_threshold' => $validated['sniper_threshold'] ?? 2,
            'extension_minutes' => $validated['extension_minutes'] ?? 5,
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'created',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
            'meta' => ['user_id' => $auction->user_id],
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'Auction created successfully',
            'auction' => $auction->load('seller'),
        ], 201);
    }

    public function updateAuction(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable|in:active,ended,pending,cancelled',
            'type' => 'nullable|in:buy_now,auction,both',
            'start_price' => 'nullable|numeric|min:0',
            'current_price' => 'nullable|numeric|min:0',
            'winner_id' => 'nullable|integer|exists:users,id',
            'started_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'sniper_threshold' => 'nullable|integer|min:1',
            'extension_minutes' => 'nullable|integer|min:1',
            'times_extended' => 'nullable|integer|min:0',
            'title' => 'nullable|string|max:255',
            'breed' => 'nullable|string|max:100',
            'gender' => 'nullable|in:samiec,samica,golab_mlody',
            'year' => 'nullable|integer|min:2000|max:'.(date('Y') + 1),
            'size' => 'nullable|in:maly,sredni,duzy',
            'color' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'pigeon_images' => 'nullable|array',
            'pigeon_images.*' => 'string',
            'pedigree_images' => 'nullable|array',
            'pedigree_images.*' => 'string',
        ]);

        $validated['anti_sniper_enabled'] = true;
        $auction->update($validated);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'updated',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
            'meta' => $validated,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'Auction updated',
            'auction' => $auction,
        ]);
    }

    public function deleteAuction(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'deleted',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        $auction->delete();

        return response()->json(['message' => 'Auction deleted']);
    }

    public function activateAuction(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        if ($auction->status !== 'pending') {
            return response()->json(['message' => 'Only pending auctions can be activated'], 400);
        }

        $endTime = now()->addDays(7);

        $auction->update([
            'status' => 'active',
            'started_at' => now(),
            'ends_at' => $endTime,
        ]);

        NotificationService::auctionActivated($auction);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'activated',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
            'meta' => ['ends_at' => $endTime],
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'Auction activated successfully',
            'auction' => $auction,
        ]);
    }

    public function approveAuction(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        $endsAt = now()->addDays(7);

        $auction->update([
            'status' => 'active',
            'ends_at' => $endsAt,
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'approved',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json(['message' => 'Auction approved']);
    }

    public function rejectAuction(Request $request, $id)
    {
        $auction = Auction::findOrFail($id);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $auction->update(['status' => 'cancelled']);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'rejected',
            'model_type' => 'Auction',
            'model_id' => $auction->id,
            'meta' => $validated,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json(['message' => 'Auction rejected']);
    }

    public function getReport(Request $request, $id)
    {
        if (! class_exists('App\Models\Report')) {
            return response()->json(['message' => 'Report model not found'], 404);
        }

        $report = Report::with(['reporter', 'reportable'])->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $report->id,
                'reason' => $report->reason,
                'description' => $report->description,
                'reporter' => [
                    'id' => $report->reporter->id,
                    'name' => $report->reporter->name,
                    'email' => $report->reporter->email,
                ],
                'reportable_type' => $report->reportable_type,
                'reportable' => $report->reportable ? [
                    'id' => $report->reportable->id,
                    'name' => $report->reportable->name ?? $report->reportable->title ?? 'N/A',
                ] : null,
                'status' => $report->status,
                'notes' => $report->notes,
                'created_at' => $report->created_at,
                'reviewed_at' => $report->reviewed_at,
            ],
        ]);
    }

    public function updateReport(Request $request, $id)
    {
        if (! class_exists('App\Models\Report')) {
            return response()->json(['message' => 'Report model not found'], 404);
        }

        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,reviewing,resolved,dismissed',
            'notes' => 'nullable|string|max:2000',
            'ban_user' => 'nullable|boolean',
            'ban_hours' => 'nullable|integer|min:1|max:8760',
            'ban_reason' => 'nullable|string|max:1000',
        ]);

        $reportedUser = $this->resolveReportedUser($report);
        if ($request->get('ban_user') && $reportedUser) {
            $banUntil = now()->addHours($request->get('ban_hours', 24));

            $reportedUser->update([
                'is_banned' => true,
                'ban_reason' => $request->get('ban_reason') ?? 'Banned from report: '.$report->reason,
                'ban_until' => $banUntil,
            ]);

            AuditLog::create([
                'admin_user_id' => $request->user()->id,
                'action' => 'banned_temporary',
                'model_type' => 'User',
                'model_id' => $reportedUser->id,
                'meta' => [
                    'reason' => 'From report #'.$report->id,
                    'hours' => $validated['ban_hours'] ?? 24,
                    'until' => $banUntil,
                ],
                'ip_address' => $request->ip() ?? '0.0.0.0',
                'user_agent' => $request->userAgent() ?? '',
            ]);
        }

        $report->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $report->notes,
            'reviewed_at' => in_array($validated['status'], ['resolved', 'dismissed']) ? now() : null,
            'reviewed_by' => $request->user()->id,
        ]);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'updated',
            'model_type' => 'Report',
            'model_id' => $report->id,
            'meta' => $validated,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'message' => 'Report updated',
            'report' => $report,
        ]);
    }

    /**
     * reportable to bezposrednio User, czy Auction (wtedy zglaszany user to
     */
    private function resolveReportedUser(Report $report): ?User
    {
        if ($report->reportable_type === User::class) {
            return $report->reportable;
        }

        if ($report->reportable_type === Auction::class) {
            return $report->reportable?->user;
        }

        return null;
    }

    public function getCategories(Request $request)
    {
        $categories = Category::withCount('auctions')
            ->orderBy('name')
            ->get()
            ->map(function ($category) {
                $count = $category->category_type === 'smart'
                    ? $category->getSmartAuctions()->count()
                    : $category->auctions_count;

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'category_type' => $category->category_type,
                    'smart_filter' => $category->smart_filter,
                    'is_featured' => $category->is_featured,
                    'show_as_home_section' => $category->show_as_home_section,
                    'auctions_count' => $count,
                    'listings_count' => $count,
                    'created_at' => $category->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_type' => 'required|in:standard,smart',
            'smart_filter' => 'nullable|array',
            'smart_filter.type' => 'nullable|in:auction,buy_now,both',
            'smart_filter.year' => 'nullable|integer|min:2000|max:2100',
            'smart_filter.sort_by' => 'nullable|in:latest,newest,bids_count_desc,price_desc,price_asc,ending_soon,seller_reputation',
            'smart_filter.user_id' => 'nullable|exists:users,id',
            'smart_filter.breed' => 'nullable|string',
            'smart_filter.min_price' => 'nullable|numeric',
            'smart_filter.max_price' => 'nullable|numeric',
            'is_featured' => 'boolean',
            'show_as_home_section' => 'boolean',
        ]);

        $validated['slug'] = Category::generateSlug($validated['name']);

        if (isset($validated['smart_filter'])) {
            $validated['smart_filter'] = array_filter(
                $validated['smart_filter'],
                static fn ($v) => $v !== null && $v !== ''
            ) ?: null;
        }

        $category = Category::create($validated);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'created',
            'model_type' => 'Category',
            'model_id' => $category->id,
            'meta' => $validated,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategoria została utworzona',
            'data' => $category,
        ], 201);
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'category_type' => 'sometimes|in:standard,smart',
            'smart_filter' => 'nullable|array',
            'smart_filter.type' => 'nullable|in:auction,buy_now,both',
            'smart_filter.year' => 'nullable|integer|min:2000|max:2100',
            'smart_filter.sort_by' => 'nullable|in:latest,newest,bids_count_desc,price_desc,price_asc,ending_soon,seller_reputation',
            'smart_filter.user_id' => 'nullable|exists:users,id',
            'smart_filter.breed' => 'nullable|string',
            'smart_filter.min_price' => 'nullable|numeric',
            'smart_filter.max_price' => 'nullable|numeric',
            'is_featured' => 'boolean',
            'show_as_home_section' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Category::generateSlug($validated['name']);
        }

        if (isset($validated['smart_filter'])) {
            $validated['smart_filter'] = array_filter(
                $validated['smart_filter'],
                static fn ($v) => $v !== null && $v !== ''
            ) ?: null;
        }

        $category->update($validated);

        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'updated',
            'model_type' => 'Category',
            'model_id' => $category->id,
            'meta' => $validated,
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategoria została zaktualizowana',
            'data' => $category->fresh(),
        ]);
    }

    public function deleteCategory(Request $request, Category $category)
    {
        AuditLog::create([
            'admin_user_id' => $request->user()->id,
            'action' => 'deleted',
            'model_type' => 'Category',
            'model_id' => $category->id,
            'meta' => ['name' => $category->name],
            'ip_address' => $request->ip() ?? '0.0.0.0',
            'user_agent' => $request->userAgent() ?? '',
        ]);

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategoria została usunięta',
        ]);
    }
}
