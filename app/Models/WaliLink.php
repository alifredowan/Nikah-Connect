<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaliLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'seeker_user_id',
        'wali_user_id',
        'wali_name',
        'wali_phone',
        'wali_email',
        'relationship_type',
        'permission_level',
        'status',
        'invite_token',
    ];

    public function seeker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seeker_user_id');
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_user_id');
    }

    public function isApproveRequired(): bool
    {
        return $this->permission_level === 'approve_required';
    }

    public function isFullProxy(): bool
    {
        return $this->permission_level === 'full_proxy';
    }

    public function isViewOnly(): bool
    {
        return $this->permission_level === 'view_only';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
