<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $casts = ['scopes' => 'array', 'last_used_at' => 'datetime', 'expires_at' => 'datetime'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
