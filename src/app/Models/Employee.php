<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['nip', 'name', 'dept_id'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function patrols(): HasMany
    {
        return $this->hasMany(Patrol::class);
    }
}
