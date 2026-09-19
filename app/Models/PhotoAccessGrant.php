<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoAccessGrant extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'photo_id',
        'granted_to_user_id',
        'status',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function grantedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_to_user_id');
    }
}
