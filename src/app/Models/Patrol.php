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
        'qr_code_token',
        'qr_scanned_at',
        'qr_scanned_ip',
    ];

    protected function casts(): array
    {
        return [
            'patrol_time' => 'datetime',
            'qr_scanned_at' => 'datetime',
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

    /**
     * Check if patrol is validated via QR code scan
     */
    public function isValidated(): bool
    {
        return $this->qr_scanned_at !== null && !empty($this->qr_code_token);
    }

    /**
     * Mark patrol as validated with QR code scan
     */
    public function validateWithQrCode(string $token, ?string $ipAddress = null): bool
    {
        if ($this->qr_code_token === $token) {
            $this->update([
                'qr_scanned_at' => now(),
                'qr_scanned_ip' => $ipAddress ?? request()?->ip(),
            ]);
            return true;
        }
        return false;
    }

    /**
     * Generate unique QR code token for this patrol
     */
    public function generateQrToken(): void
    {
        if (empty($this->qr_code_token)) {
            $this->update(['qr_code_token' => \Illuminate\Support\Str::random(32)]);
        }
    }
}
