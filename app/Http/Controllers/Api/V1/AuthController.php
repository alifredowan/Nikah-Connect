<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $maxBirthDate = Carbon::now()->subYears(18)->toDateString();

        $normalizedEmail = strtolower(trim((string) $request->input('email')));
        $normalizedPhone = $request->filled('phone') ? trim((string) $request->input('phone')) : null;

        $request->merge([
            'email' => $normalizedEmail,
            'phone' => $normalizedPhone,
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->where(function ($query) use ($normalizedEmail) {
                    return $query->whereRaw('LOWER(email) = ?', [$normalizedEmail]);
                }),
            ],
            'phone' => ['nullable', 'string', 'unique:users'],
            'gender' => ['required', 'in:male,female'],
            'dob' => ['required', 'date', "before_or_equal:{$maxBirthDate}"],
            'marital_status' => ['required', 'in:never_married,divorced,widowed,annulled'],
            'password' => ['required', Password::defaults()],
        ], [
            'email.unique' => 'An account with this email address already exists. Please sign in or use forgot password.',
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
            'email_verified_at' => now(),
        ]);

        Profile::create([
            'user_id' => $user->id,
            'wali_required' => ($validated['gender'] === 'female'),
        ]);

        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'free',
            'status' => 'active',
            'starts_at' => now(),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user->load('profile'),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $normalizedEmail = strtolower(trim((string) $request->input('email')));
        $request->merge(['email' => $normalizedEmail]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account deactivated or suspended',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user->load('profile'),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()->load(['profile.photos', 'activeSubscription']),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }
}
