<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patrol extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'shift_id',
        'location_id',
        'violation_id',
        'action_id',
        'description',
        'photos',
        'signature',
        'face_photo',
        'patrol_time',
    ];

    protected function casts(): array
    {
        return [
            'patrol_time' => 'datetime',
            'photos' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(Violation::class);
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(Action::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PatrolAttachment::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function checkpoints(): HasMany
    {
        return $this->hasMany(PatrolCheckpoint::class);
    }
}
