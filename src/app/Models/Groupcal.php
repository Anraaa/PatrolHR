<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Groupcal extends Model
{
    use HasFactory;

    protected $table = 'groupcal';
    
    protected $primaryKey = 'date_shift';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    public $timestamps = false;

    protected $fillable = ['date_shift', 'shfgroup'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'shfgroup', 'shfgroup');
    }
}
