<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'height_cm',
        'education_level',
        'profession',
        'employment_type',
        'annual_income_range',
        'city',
        'state',
        'country',
        'mother_tongue',
        'citizenship',
        'bio',
        'sect_madhhab',
        'prayer_frequency',
        'hijab_niqab_practice',
        'beard_practice',
        'quran_knowledge',
        'mosque_attendance',
        'halal_dietary_adherence',
        'polygamy_opinion',
        'desired_family_structure',
        'parents_status',
        'parents_occupation',
        'siblings_count',
        'family_religiosity',
        'partner_preferences',
        'field_visibility',
        'wali_required',
        'completeness_percentage',
    ];

    protected function casts(): array
    {
        return [
            'height_cm' => 'integer',
            'siblings_count' => 'integer',
            'wali_required' => 'boolean',
            'completeness_percentage' => 'integer',
            'partner_preferences' => 'array',
            'field_visibility' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(Photo::class)->where('is_primary', true);
    }

    public function approvedPhotos(): HasMany
    {
        return $this->hasMany(Photo::class)->where('moderation_status', 'approved');
    }

    public function photoAccessGrants(): HasMany
    {
        return $this->hasMany(PhotoAccessGrant::class);
    }

    /**
     * Dynamically calculate and save profile completeness percentage (FR-2.4).
     */
    public function updateCompleteness(): int
    {
        $fields = [
            'height_cm' => 5,
            'education_level' => 5,
            'profession' => 5,
            'city' => 5,
            'country' => 5,
            'bio' => 10,
            'sect_madhhab' => 15,
            'prayer_frequency' => 15,
            'halal_dietary_adherence' => 5,
            'quran_knowledge' => 5,
            'family_religiosity' => 5,
            'desired_family_structure' => 5,
            'partner_preferences' => 10,
        ];

        $score = 0;
        foreach ($fields as $field => $points) {
            if (! empty($this->{$field})) {
                $score += $points;
            }
        }

        if ($this->photos()->where('moderation_status', 'approved')->exists()) {
            $score += 5;
        }

        $percentage = min(100, $score);
        $this->updateQuietly(['completeness_percentage' => $percentage]);

        return $percentage;
    }

    public function isPhotoVisibleTo(?User $viewer): bool
    {
        if (! $viewer) {
            return false;
        }

        if ($viewer->id === $this->user_id || $viewer->isAdmin() || $viewer->isModerator()) {
            return true;
        }

        // Linked Wali has access to ward's photos
        if ($viewer->isWali()) {
            $isWardsPhoto = WaliLink::where('wali_id', $viewer->id)
                ->where('seeker_id', $this->user_id)
                ->where('status', 'accepted')
                ->exists();

            if ($isWardsPhoto) {
                return true;
            }
        }

        // Check if mutual acceptance exists between users and wali approval is satisfied
        $mutualInterest = Interest::where('status', 'accepted')
            ->whereIn('wali_approval_status', ['approved', 'not_required'])
            ->where(function ($q) use ($viewer) {
                $q->where(function ($sub) use ($viewer) {
                    $sub->where('sender_id', $this->user_id)->where('recipient_id', $viewer->id);
                })->orWhere(function ($sub) use ($viewer) {
                    $sub->where('sender_id', $viewer->id)->where('recipient_id', $this->user_id);
                });
            })->exists();

        if ($mutualInterest) {
            return true;
        }

        // Check explicit photo access grant
        return $this->photoAccessGrants()
            ->where('granted_to_user_id', $viewer->id)
            ->where('status', 'active')
            ->exists();
    }
}
