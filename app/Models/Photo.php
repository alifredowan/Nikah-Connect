<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'file_path',
        'is_primary',
        'is_private',
        'is_blurred',
        'moderation_status',
    ];

    protected $hidden = [
        'file_path',
    ];

    protected $appends = [
        'display_url',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_private' => 'boolean',
            'is_blurred' => 'boolean',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function getUrlAttribute(): string
    {
        return $this->displayUrl();
    }

    public function getDisplayUrlAttribute(): string
    {
        return route('photos.view', $this->id);
    }

    public function displayUrl(?User $viewer = null): string
    {
        return route('photos.view', $this->id);
    }
}
