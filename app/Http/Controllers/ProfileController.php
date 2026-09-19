<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\PhotoAccessGrant;
use App\Models\Profile;
use App\Models\VerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();
        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);
        $profile->load(['photos', 'photoAccessGrants.grantedToUser']);
        $profile->updateCompleteness();

        return view('profile.show', compact('user', 'profile'));
    }

    public function edit(): View
    {
        $user = Auth::user();
        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);

        return view('profile.edit', compact('user', 'profile'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->profile ?? Profile::create(['user_id' => $user->id]);

        $validated = $request->validate([
            'height_cm' => ['nullable', 'integer', 'min:120', 'max:230'],
            'education_level' => ['nullable', 'string', 'max:100'],
            'profession' => ['nullable', 'string', 'max:100'],
            'employment_type' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'mother_tongue' => ['nullable', 'string', 'max:50'],
            'citizenship' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],

            // Religious fields
            'sect_madhhab' => ['nullable', 'string', 'max:100'],
            'prayer_frequency' => ['nullable', 'string', 'max:50'],
            'hijab_niqab_practice' => ['nullable', 'string', 'max:50'],
            'beard_practice' => ['nullable', 'string', 'max:50'],
            'quran_knowledge' => ['nullable', 'string', 'max:50'],
            'mosque_attendance' => ['nullable', 'string', 'max:50'],
            'halal_dietary_adherence' => ['nullable', 'string', 'max:50'],
            'polygamy_opinion' => ['nullable', 'string', 'max:50'],
            'desired_family_structure' => ['nullable', 'string', 'max:50'],

            // Family fields
            'parents_status' => ['nullable', 'string', 'max:100'],
            'parents_occupation' => ['nullable', 'string', 'max:100'],
            'siblings_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'family_religiosity' => ['nullable', 'string', 'max:50'],

            // Partner preferences
            'pref_age_min' => ['nullable', 'integer', 'min:18', 'max:80'],
            'pref_age_max' => ['nullable', 'integer', 'min:18', 'max:80'],
            'pref_sect' => ['nullable', 'string', 'max:100'],
            'pref_location' => ['nullable', 'string', 'max:100'],
            'pref_education' => ['nullable', 'string', 'max:100'],

            // Privacy & Wali
            'wali_required' => ['nullable', 'boolean'],
        ]);

        $partnerPreferences = [
            'age_min' => $validated['pref_age_min'] ?? null,
            'age_max' => $validated['pref_age_max'] ?? null,
            'preferred_sect' => $validated['pref_sect'] ?? null,
            'preferred_location' => $validated['pref_location'] ?? null,
            'preferred_education' => $validated['pref_education'] ?? null,
        ];

        $profile->fill($validated);
        $profile->partner_preferences = $partnerPreferences;
        $profile->wali_required = $request->boolean('wali_required');
        $profile->save();

        $profile->updateCompleteness();

        AuditLog::record($user->id, 'profile_updated', 'Profile', $profile->id);

        return redirect()->route('profile.show')->with('success', 'Your profile details have been updated successfully!');
    }

    public function uploadPhoto(Request $request): RedirectResponse
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:5120'], // max 5MB
            'is_private' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();
        $profile = $user->profile;

        if ($profile->photos()->count() >= 6) {
            return back()->withErrors(['photo' => 'You can upload a maximum of 6 photos (FR-2.2).']);
        }

        $path = $request->file('photo')->store('profile-photos', 'public');

        $isFirstPhoto = ! $profile->photos()->exists();

        $profile->photos()->create([
            'file_path' => $path,
            'is_primary' => $isFirstPhoto,
            'is_private' => $request->boolean('is_private'),
            'is_blurred' => true, // default blurred per FR-2.2
            'moderation_status' => 'approved', // auto-approved for dev, flagged in production
        ]);

        $profile->updateCompleteness();

        return back()->with('success', 'Photo uploaded! By default, photos remain blurred until reciprocal permission is granted (FR-2.2).');
    }

    public function setPrimaryPhoto(int $photoId): RedirectResponse
    {
        $profile = Auth::user()->profile;
        $photo = $profile->photos()->findOrFail($photoId);

        $profile->photos()->update(['is_primary' => false]);
        $photo->update(['is_primary' => true]);

        return back()->with('success', 'Primary profile photo updated.');
    }

    public function deletePhoto(int $photoId): RedirectResponse
    {
        $profile = Auth::user()->profile;
        $photo = $profile->photos()->findOrFail($photoId);

        if (Storage::disk('public')->exists($photo->file_path)) {
            Storage::disk('public')->delete($photo->file_path);
        }

        $cacheFile = storage_path('app/blurred-photos/blurred_'.$photo->id.'.jpg');
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }

        $photo->delete();
        $profile->updateCompleteness();

        return back()->with('success', 'Photo removed.');
    }

    public function submitVerification(Request $request): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'in:national_id,passport,driving_license'],
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'selfie' => ['nullable', 'image', 'max:5120'],
        ]);

        $user = Auth::user();

        $docPath = $request->file('document')->store('verifications', 'public');
        $selfiePath = $request->hasFile('selfie') ? $request->file('selfie')->store('verifications', 'public') : null;

        VerificationRequest::create([
            'user_id' => $user->id,
            'document_type' => $request->document_type,
            'document_path' => $docPath,
            'selfie_path' => $selfiePath,
            'status' => 'pending',
        ]);

        AuditLog::record($user->id, 'verification_submitted', 'User', $user->id);

        return back()->with('success', 'Verification documents submitted! Our moderation team will review them to grant your Verified badge (FR-1.4).');
    }

    public function grantPhotoAccess(int $userId): RedirectResponse
    {
        $profile = Auth::user()->profile;

        PhotoAccessGrant::updateOrCreate([
            'profile_id' => $profile->id,
            'granted_to_user_id' => $userId,
        ], [
            'status' => 'active',
        ]);

        return back()->with('success', 'Photo access granted! The recipient can now view your unblurred photos.');
    }
}
