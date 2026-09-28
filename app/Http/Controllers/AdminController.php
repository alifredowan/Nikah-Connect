<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\DiscountCode;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\VerificationRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $metrics = [
            'total_users' => User::count(),
            'seekers' => User::where('role', 'seeker')->count(),
            'walis' => User::where('role', 'wali')->count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'pending_verifications' => VerificationRequest::where('status', 'pending')->count(),
            'active_conversations' => Conversation::where('status', 'active')->count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'total_revenue' => Subscription::where('status', 'active')->sum('amount_paid'),
            'paying_subscribers' => Subscription::where('status', 'active')->where('plan', '!=', 'free')->count(),
            'active_packages' => SubscriptionPlan::where('is_active', true)->count(),
            'active_promo_codes' => DiscountCode::where('is_active', true)->count(),
        ];

        $recentVerifications = VerificationRequest::with('user.profile')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentReports = Report::with(['reporter', 'reported'])
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $recentAuditLogs = AuditLog::with('user')->latest('created_at')->take(8)->get();

        return view('admin.dashboard', compact('metrics', 'recentVerifications', 'recentReports', 'recentAuditLogs'));
    }

    public function verifications(): View
    {
        $verifications = VerificationRequest::with(['user.profile', 'reviewer'])
            ->latest()
            ->paginate(15);

        return view('admin.verifications', compact('verifications'));
    }

    public function approveVerification(int $id): RedirectResponse
    {
        $admin = Auth::user();
        $request = VerificationRequest::findOrFail($id);

        $request->update([
            'status' => 'approved',
            'reviewer_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $request->user->update(['is_verified' => true]);

        AuditLog::record($admin->id, 'verification_approved', 'User', $request->user_id);

        return back()->with('success', 'Verification approved! Candidate now has the official "Verified" badge (FR-1.4).');
    }

    public function rejectVerification(Request $request, int $id): RedirectResponse
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $admin = Auth::user();
        $verification = VerificationRequest::findOrFail($id);

        $verification->update([
            'status' => 'rejected',
            'reviewer_id' => $admin->id,
            'rejection_reason' => $request->rejection_reason,
            'reviewed_at' => now(),
        ]);

        $verification->user->update(['is_verified' => false]);

        AuditLog::record($admin->id, 'verification_rejected', 'User', $verification->user_id, [
            'reason' => $request->rejection_reason,
        ]);

        return back()->with('info', 'Verification rejected with reason recorded.');
    }

    public function reports(): View
    {
        $reports = Report::with(['reporter', 'reported', 'conversation', 'moderator'])
            ->latest()
            ->paginate(15);

        return view('admin.reports', compact('reports'));
    }

    public function actionReport(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:warn,suspend,ban,dismiss'],
            'resolution_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = Auth::user();
        $report = Report::findOrFail($id);

        $action = $request->action;
        $reportedUser = $report->reported;

        if ($action === 'suspend' || $action === 'ban') {
            $reportedUser->update(['is_active' => false]);
        }

        $report->update([
            'status' => $action === 'dismiss' ? 'dismissed' : 'actioned',
            'action_taken' => $action,
            'resolution_notes' => $request->resolution_notes,
            'moderator_id' => $admin->id,
            'resolved_at' => now(),
        ]);

        AuditLog::record($admin->id, "report_{$action}", 'Report', $report->id, [
            'reported_user_id' => $reportedUser->id,
            'action' => $action,
        ]);

        return back()->with('success', "Report has been successfully processed with action: {$action}.");
    }

    public function packages(): View
    {
        $packages = SubscriptionPlan::orderBy('sort_order')->orderBy('monthly_price')->get();

        return view('admin.packages', compact('packages'));
    }

    public function storePackage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:50', 'alpha_dash', 'unique:subscription_plans,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'annual_price' => ['required', 'numeric', 'min:0'],
            'daily_profile_views' => ['required', 'integer'],
            'daily_interests' => ['required', 'integer'],
            'advanced_filters' => ['nullable', 'boolean'],
            'profile_boost' => ['nullable', 'boolean'],
            'see_who_viewed' => ['nullable', 'boolean'],
            'dedicated_advisor' => ['nullable', 'boolean'],
            'badge_text' => ['nullable', 'string', 'max:50'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (SubscriptionPlan::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        $features = [];
        if (! empty($validated['features'])) {
            $features = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $validated['features']))));
        }

        $package = SubscriptionPlan::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'monthly_price' => $validated['monthly_price'],
            'annual_price' => $validated['annual_price'],
            'daily_profile_views' => $validated['daily_profile_views'],
            'daily_interests' => $validated['daily_interests'],
            'advanced_filters' => $request->boolean('advanced_filters'),
            'profile_boost' => $request->boolean('profile_boost'),
            'see_who_viewed' => $request->boolean('see_who_viewed'),
            'dedicated_advisor' => $request->boolean('dedicated_advisor'),
            'badge_text' => $validated['badge_text'] ?? null,
            'features' => $features,
            'is_active' => $request->boolean('is_active', true),
            'is_popular' => $request->boolean('is_popular'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        AuditLog::record(Auth::id(), 'subscription_package_created', 'SubscriptionPlan', $package->id, [
            'name' => $package->name,
            'slug' => $package->slug,
            'monthly_price' => $package->monthly_price,
        ]);

        return back()->with('success', "Subscription package '{$package->name}' created successfully!");
    }

    public function updatePackage(Request $request, int $id): RedirectResponse
    {
        $package = SubscriptionPlan::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('subscription_plans', 'slug')->ignore($package->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'annual_price' => ['required', 'numeric', 'min:0'],
            'daily_profile_views' => ['required', 'integer'],
            'daily_interests' => ['required', 'integer'],
            'advanced_filters' => ['nullable', 'boolean'],
            'profile_boost' => ['nullable', 'boolean'],
            'see_who_viewed' => ['nullable', 'boolean'],
            'dedicated_advisor' => ['nullable', 'boolean'],
            'badge_text' => ['nullable', 'string', 'max:50'],
            'features' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $features = [];
        if (! empty($validated['features'])) {
            $features = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $validated['features']))));
        }

        $package->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'monthly_price' => $validated['monthly_price'],
            'annual_price' => $validated['annual_price'],
            'daily_profile_views' => $validated['daily_profile_views'],
            'daily_interests' => $validated['daily_interests'],
            'advanced_filters' => $request->boolean('advanced_filters'),
            'profile_boost' => $request->boolean('profile_boost'),
            'see_who_viewed' => $request->boolean('see_who_viewed'),
            'dedicated_advisor' => $request->boolean('dedicated_advisor'),
            'badge_text' => $validated['badge_text'] ?? null,
            'features' => $features,
            'is_active' => $request->boolean('is_active'),
            'is_popular' => $request->boolean('is_popular'),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        AuditLog::record(Auth::id(), 'subscription_package_updated', 'SubscriptionPlan', $package->id, [
            'name' => $package->name,
            'monthly_price' => $package->monthly_price,
        ]);

        return back()->with('success', "Subscription package '{$package->name}' updated successfully!");
    }

    public function togglePackageStatus(int $id): RedirectResponse
    {
        $package = SubscriptionPlan::findOrFail($id);
        $package->update(['is_active' => ! $package->is_active]);

        $status = $package->is_active ? 'activated' : 'deactivated';
        AuditLog::record(Auth::id(), "subscription_package_{$status}", 'SubscriptionPlan', $package->id);

        return back()->with('success', "Package '{$package->name}' {$status} successfully.");
    }

    public function destroyPackage(int $id): RedirectResponse
    {
        $package = SubscriptionPlan::findOrFail($id);

        if ($package->slug === 'free') {
            return back()->with('error', 'The default Free tier cannot be deleted.');
        }

        $activeSubscribersCount = Subscription::where('plan', $package->slug)->where('status', 'active')->count();
        if ($activeSubscribersCount > 0) {
            return back()->with('error', "Cannot delete package '{$package->name}' because it has {$activeSubscribersCount} active subscribers. Deactivate it instead.");
        }

        $name = $package->name;
        $package->delete();

        AuditLog::record(Auth::id(), 'subscription_package_deleted', 'SubscriptionPlan', $id, ['name' => $name]);

        return back()->with('success', "Package '{$name}' deleted successfully.");
    }

    public function promoCodes(): View
    {
        $discountCodes = DiscountCode::with('plan')->latest('created_at')->get();
        $packages = SubscriptionPlan::where('monthly_price', '>', 0)->orderBy('name')->get();
        $allUsers = User::where('role', 'seeker')->orderBy('name')->select('id', 'name', 'email')->get();

        return view('admin.promo-codes', compact('discountCodes', 'packages', 'allUsers'));
    }

    public function storePromoCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:discount_codes,code'],
            'discount_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'plan_slug' => ['nullable', 'string'],
            'allowed_emails' => ['nullable', 'string'],
            'description' => ['nullable', 'string', 'max:255'],
            'max_uses' => ['required', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $planSlug = $request->plan_slug === 'all' || empty($request->plan_slug) ? null : $request->plan_slug;

        $promo = DiscountCode::create([
            'code' => strtoupper(trim($request->code)),
            'discount_percentage' => (int) $request->discount_percentage,
            'plan_slug' => $planSlug,
            'allowed_emails' => $request->allowed_emails ? trim($request->allowed_emails) : null,
            'description' => $request->description,
            'max_uses' => (int) $request->max_uses,
            'times_used' => 0,
            'is_active' => true,
            'expires_at' => $request->expires_at ? Carbon::parse($request->expires_at) : null,
        ]);

        AuditLog::record(Auth::id(), 'promo_code_created', 'DiscountCode', $promo->id, [
            'code' => $promo->code,
            'discount_percentage' => $promo->discount_percentage,
            'plan_slug' => $promo->plan_slug,
            'restricted' => $promo->isRestrictedToUsers(),
        ]);

        return back()->with('success', "Promo code '{$promo->code}' created successfully!");
    }

    public function togglePromoCodeStatus(int $id): RedirectResponse
    {
        $code = DiscountCode::findOrFail($id);
        $code->update(['is_active' => ! $code->is_active]);

        $status = $code->is_active ? 'activated' : 'deactivated';
        AuditLog::record(Auth::id(), "promo_code_{$status}", 'DiscountCode', $code->id);

        return back()->with('success', "Promo code '{$code->code}' {$status} successfully.");
    }

    public function destroyPromoCode(int $id): RedirectResponse
    {
        $code = DiscountCode::findOrFail($id);
        $name = $code->code;
        $code->delete();

        AuditLog::record(Auth::id(), 'promo_code_deleted', 'DiscountCode', $id, ['code' => $name]);

        return back()->with('success', "Promo code '{$name}' deleted successfully.");
    }

    public function settings(): View
    {
        $settings = PlatformSetting::all();
        $discountCodes = DiscountCode::with('plan')->latest('created_at')->get();

        return view('admin.settings', compact('settings', 'discountCodes'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $inputs = $request->except('_token');

        foreach ($inputs as $key => $value) {
            PlatformSetting::where('key', $key)->update(['value' => $value]);
        }

        AuditLog::record(Auth::id(), 'platform_settings_updated');

        return back()->with('success', 'Platform policy parameters updated successfully (FR-7.3).');
    }

    public function createDiscount(Request $request): RedirectResponse
    {
        return $this->storePromoCode($request);
    }

    public function auditLogs(): View
    {
        $logs = AuditLog::with('user')->latest('created_at')->paginate(25);

        return view('admin.audit-logs', compact('logs'));
    }

    public function staff(): View
    {
        $staffUsers = User::whereIn('role', ['super_admin', 'admin', 'moderator'])
            ->orderBy('created_at', 'desc')
            ->get();

        $availablePermissions = [
            'manage_verifications' => 'KYC Profile & ID Verifications',
            'manage_reports' => 'User Abuse Reports & Sanctions (Warn, Suspend, Ban)',
            'view_audit_logs' => 'Audit Logs & Governance Tracking',
            'manage_settings' => 'Platform Settings & Policies',
        ];

        return view('admin.staff', compact('staffUsers', 'availablePermissions'));
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $normalizedEmail = strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $normalizedEmail]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(function ($query) use ($normalizedEmail) {
                    return $query->whereRaw('LOWER(email) = ?', [$normalizedEmail]);
                }),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:super_admin,moderator'],
            'password' => ['required', 'string', 'min:8'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:manage_verifications,manage_reports,view_audit_logs,manage_settings'],
        ], [
            'email.unique' => 'A user or staff member with this email address already exists.',
        ]);

        $permissions = $validated['role'] === 'super_admin'
            ? array_keys([
                'manage_verifications' => true,
                'manage_reports' => true,
                'view_audit_logs' => true,
                'manage_settings' => true,
            ])
            : ($validated['permissions'] ?? []);

        $staff = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'permissions' => $permissions,
            'password' => Hash::make($validated['password']),
            'gender' => 'male',
            'dob' => '1990-01-01',
            'marital_status' => 'never_married',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        AuditLog::record(Auth::id(), 'staff_member_created', 'User', $staff->id, [
            'role' => $staff->role,
            'permissions' => $staff->permissions,
        ]);

        return back()->with('success', "New staff member ({$staff->name} as ".ucwords(str_replace('_', ' ', $staff->role)).') created successfully!');
    }

    public function updateStaffPermissions(Request $request, int $id): RedirectResponse
    {
        $staff = User::findOrFail($id);

        if ($staff->isSuperAdmin() && ! Auth::user()->isSuperAdmin()) {
            abort(403, 'Only a Super Admin can modify other Super Admins.');
        }

        $validated = $request->validate([
            'role' => ['nullable', 'in:super_admin,moderator'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:manage_verifications,manage_reports,view_audit_logs,manage_settings'],
        ]);

        $updates = [
            'permissions' => $validated['permissions'] ?? [],
        ];

        if (isset($validated['role'])) {
            $updates['role'] = $validated['role'];
            if ($validated['role'] === 'super_admin') {
                $updates['permissions'] = [
                    'manage_verifications',
                    'manage_reports',
                    'view_audit_logs',
                    'manage_settings',
                ];
            }
        }

        $staff->update($updates);

        AuditLog::record(Auth::id(), 'staff_permissions_updated', 'User', $staff->id, [
            'permissions' => $staff->permissions,
            'role' => $staff->role,
        ]);

        return back()->with('success', "Permissions updated for {$staff->name}.");
    }

    public function toggleStaffStatus(int $id): RedirectResponse
    {
        $staff = User::findOrFail($id);

        if ($staff->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $staff->update(['is_active' => ! $staff->is_active]);

        $statusText = $staff->is_active ? 'activated' : 'deactivated';

        AuditLog::record(Auth::id(), "staff_{$statusText}", 'User', $staff->id);

        return back()->with('success', "Staff account {$statusText} successfully.");
    }
}
