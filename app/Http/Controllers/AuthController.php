<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $maxBirthDate = Carbon::now()->subYears(18)->toDateString();

        $role = $request->input('role', 'seeker');
        if (! in_array($role, ['seeker', 'wali'], true)) {
            $role = 'seeker';
        }
        $request->merge(['role' => $role]);

        $rules = [
            'role' => ['required', 'in:seeker,wali'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users'],
            'gender' => ['required', 'in:male,female'],
            'dob' => ['required', 'date', "before_or_equal:{$maxBirthDate}"],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ];

        if ($role === 'seeker') {
            $rules['marital_status'] = ['required', 'in:never_married,divorced,widowed,annulled'];
        } else {
            $rules['marital_status'] = ['nullable', 'in:never_married,divorced,widowed,annulled'];
            $rules['relationship_type'] = ['nullable', 'string', 'in:father,brother,uncle,grandfather,other_mahram'];
        }

        $validated = $request->validate($rules, [
            'dob.before_or_equal' => 'You must be at least 18 years old to register for Nikah Connect.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
            'marital_status' => $validated['marital_status'] ?? 'never_married',
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        if ($role === 'seeker') {
            // Initialize user profile
            Profile::create([
                'user_id' => $user->id,
                'wali_required' => ($validated['gender'] === 'female'),
                'completeness_percentage' => 15,
            ]);

            // Initialize free subscription tier
            Subscription::create([
                'user_id' => $user->id,
                'plan' => 'free',
                'status' => 'active',
                'starts_at' => now(),
            ]);

            AuditLog::record($user->id, 'user_registered', 'User', $user->id, ['role' => 'seeker', 'email' => $user->email]);

            Auth::login($user);

            return redirect()->route('profile.edit')->with('success', 'Account registered successfully! Please complete your Islamic profile.');
        }

        // Wali Registration
        AuditLog::record($user->id, 'wali_registered', 'User', $user->id, ['role' => 'wali', 'email' => $user->email]);

        Auth::login($user);

        return redirect()->route('wali.link')->with('success', 'Guardian account created successfully! Please configure your ward (family member) details.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();

                return back()->withErrors(['email' => 'This account has been deactivated or suspended.']);
            }

            AuditLog::record($user->id, 'user_login');

            if ($user->isSuperAdmin() || $user->isModerator()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->isWali()) {
                return redirect()->route('wali.dashboard');
            }

            return redirect()->intended(route('discovery.index'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        AuditLog::record($userId, 'user_logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    public function deactivate(Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = Auth::user();
        $user->update([
            'is_active' => false,
            'deactivation_reason' => $request->reason,
        ]);

        AuditLog::record($user->id, 'account_deactivated', 'User', $user->id, ['reason' => $request->reason]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('success', 'Your account has been deactivated as per GDPR right to erasure policy.');
    }

    public function showForgotPassword(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = PasswordBroker::sendResetLink(
            $request->only('email')
        );

        if ($status === PasswordBroker::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $status = PasswordBroker::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
                AuditLog::record($user->id, 'password_reset', 'User', $user->id);
            }
        );

        if ($status === PasswordBroker::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
