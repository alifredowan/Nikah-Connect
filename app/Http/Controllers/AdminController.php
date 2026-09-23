<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\DiscountCode;
use App\Models\PlatformSetting;
use App\Models\Report;
use App\Models\Subscription;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function settings(): View
    {
        $settings = PlatformSetting::all();
        $discountCodes = DiscountCode::all();

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
        $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:discount_codes'],
            'discount_percentage' => ['required', 'integer', 'min:1', 'max:100'],
            'max_uses' => ['required', 'integer', 'min:1'],
        ]);

        DiscountCode::create([
            'code' => strtoupper($request->code),
            'discount_percentage' => $request->discount_percentage,
            'max_uses' => $request->max_uses,
            'is_active' => true,
        ]);

        return back()->with('success', 'Promo code created successfully!');
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'in:super_admin,moderator'],
            'password' => ['required', 'string', 'min:8'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:manage_verifications,manage_reports,view_audit_logs,manage_settings'],
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
