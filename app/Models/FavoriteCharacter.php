<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteCharacter extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'list_id',
        'characters',
        'created_at',
        'updated_at'
    ];

    public function aniList()
    {
        return $this->belongsTo(AniList::class, 'list_id');
    }
}
