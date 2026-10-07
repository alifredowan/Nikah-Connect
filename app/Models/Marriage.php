<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marriage extends Model
{
    use HasFactory;

    protected $fillable = [
        'groom_id',
        'bride_id',
        'initiated_by_user_id',
        'status',
        'marriage_date',
        'confirmation_notes',
        'story_title',
        'story_body',
        'story_is_public',
        'story_approved_at',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'marriage_date' => 'date',
            'story_is_public' => 'boolean',
            'story_approved_at' => 'datetime',
            'confirmed_at' => 'datetime',
        ];
    }

    public function groom(): BelongsTo
    {
        return $this->belongsTo(User::class, 'groom_id');
    }

    public function bride(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bride_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending_confirmation';
    }

    /**
     * Get the spouse of a given user in this marriage.
     */
    public function spouseOf(User $user): ?User
    {
        if ($user->id === $this->groom_id) {
            return $this->bride;
        }

        if ($user->id === $this->bride_id) {
            return $this->groom;
        }

        return null;
    }
}
