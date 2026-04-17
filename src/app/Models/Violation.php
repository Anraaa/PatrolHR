<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Violation extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function patrols(): HasMany
    {
        return $this->hasMany(Patrol::class);
    }
}
