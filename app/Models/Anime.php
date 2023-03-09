<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'anime'
    ];

    public function aniLists()
    {
        return $this->hasMany(AniList::class, 'anime_id');
    }
}
