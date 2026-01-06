<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasUlids;

    protected $casts = [
        'checked_at' => 'datetime',
        'is_canceled_by_user' => 'boolean',
    ];
}
