<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method void save()
 */
class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'image_path',
        'likes_count',
    ];

    /**
     * Obtenir l'utilisateur qui a créé la publication
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtenir les commentaires de la publication
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Obtenir les likes de la publication
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Vérifier si un utilisateur a aimé cette publication
     * 
     * @param User|null $user
     * @return bool
     */
    public function isLikedBy($user)
    {
        if (!$user || !$user instanceof User || !$user->id) {
            return false;
        }
        
        try {
            return $this->likes()->where('user_id', $user->id)->exists();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans isLikedBy: ' . $e->getMessage());
            return false;
        }
    }
} 