<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AniList extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'overall_rating',
        'animation_rating',
        'history_rating',
        'characters_rating',
        'music_rating',
        'the_good',
        'the_bad',
        'user_id',
        'anime_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function anime()
    {
        return $this->belongsTo(Anime::class);
    }

    public function favoriteCharacters()
    {
        return $this->hasMany(FavoriteCharacter::class);
    }
}
