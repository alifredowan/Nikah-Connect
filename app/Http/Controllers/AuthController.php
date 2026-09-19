<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users'],
            'gender' => ['required', 'in:male,female'],
            'dob' => ['required', 'date', "before_or_equal:{$maxBirthDate}"],
            'marital_status' => ['required', 'in:never_married,divorced,widowed,annulled'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'dob.before_or_equal' => 'You must be at least 18 years old to register for Nikah Connect (FR-1.3).',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
            'marital_status' => $validated['marital_status'],
            'password' => Hash::make($validated['password']),
            'role' => 'seeker',
            'is_active' => true,
            'email_verified_at' => now(), // Simulated instant verification for MVP
        ]);

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

        AuditLog::record($user->id, 'user_registered', 'User', $user->id, ['email' => $user->email]);

        Auth::login($user);

        return redirect()->route('profile.edit')->with('success', 'Account registered successfully! Please complete your Islamic profile.');
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

            if ($user->isAdmin() || $user->isModerator()) {
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

    public function quickLogin(Request $request, string $role): RedirectResponse
    {
        $user = match ($role) {
            'admin' => User::where('role', 'admin')->first(),
            'moderator' => User::where('role', 'moderator')->first(),
            'wali' => User::where('role', 'wali')->first(),
            'bride' => User::where('gender', 'female')->where('role', 'seeker')->first(),
            'groom' => User::where('gender', 'male')->where('role', 'seeker')->first(),
            default => null,
        };

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();

            if ($user->isAdmin() || $user->isModerator()) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->isWali()) {
                return redirect()->route('wali.dashboard');
            }

            return redirect()->route('discovery.index');
        }

        return redirect()->route('login')->with('error', 'Demo user not found. Please seed the database first.');
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
}
