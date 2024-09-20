<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Image extends Model
{
    use HasFactory;

    // public function users(): MorphToMany
    // {
    //     return $this->morphedByMany(User::class, 'imageable');
    // }
}
