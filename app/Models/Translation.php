<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
