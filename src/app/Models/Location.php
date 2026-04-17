<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'uuid', 'latitude', 'longitude', 'radius_meters'];

    protected function casts(): array
    {
        return [
            'latitude'      => 'double',
            'longitude'     => 'double',
            'radius_meters' => 'integer',
        ];
    }

    /**
     * Haversine formula — returns distance in meters between this location and given coords.
     */
    public function distanceTo(float $lat, float $lng): float
    {
        if ($this->latitude === null || $this->longitude === null) {
            return 0.0;
        }
        $R    = 6371000; // Earth radius in meters
        $phi1 = deg2rad($this->latitude);
        $phi2 = deg2rad($lat);
        $dphi = deg2rad($lat - $this->latitude);
        $dlam = deg2rad($lng - $this->longitude);
        $a    = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlam / 2) ** 2;

        return 2 * $R * asin(sqrt($a));
    }

    protected static function booted(): void
    {
        static::creating(function (Location $location) {
            if (empty($location->uuid)) {
                $location->uuid = Str::uuid()->toString();
            }
        });
    }

    public function getQrContentAttribute(): string
    {
        return url("/admin/patrols/scan/{$this->uuid}");
    }

    public function patrols(): HasMany
    {
        return $this->hasMany(Patrol::class);
    }
}
