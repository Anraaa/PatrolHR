<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatrolAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['patrol_id', 'file_path', 'type'];

    public function patrol(): BelongsTo
    {
        return $this->belongsTo(Patrol::class);
    }
}
