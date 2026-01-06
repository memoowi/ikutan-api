<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasUlids;
    
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'date' => 'datetime',
        'isActive' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
