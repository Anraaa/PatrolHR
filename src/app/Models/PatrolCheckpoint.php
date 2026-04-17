<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrolCheckpoint extends Model
{
    protected $fillable = [
        'patrol_id',
        'location_id',
        'user_id',
        'face_photo',
        'signature',
        'scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function patrol(): BelongsTo
    {
        return $this->belongsTo(Patrol::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
