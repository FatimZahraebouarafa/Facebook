<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'content',
    ];

    /**
     * Obtenir l'utilisateur qui a créé le commentaire
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir la publication à laquelle ce commentaire appartient
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
} 