<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nip', 'name', 'shfgroup'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function groupcal(): BelongsTo
    {
        return $this->belongsTo(Groupcal::class, 'shfgroup', 'shfgroup');
    }

    public function patrols(): HasMany
    {
        return $this->hasMany(Patrol::class);
    }
    
    // Accessor untuk kompatibilitas mundur
    public function getDepartmentAttribute()
    {
        return (object) ['name' => $this->shfgroup ?? '-'];
    }
}

