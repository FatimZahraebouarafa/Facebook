<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

/**
 * @method void save()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User where($column, $operator = null, $value = null, $boolean = 'and')
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'bio',
        'cover_photo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Obtenir les publications de l'utilisateur
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Obtenir les commentaires de l'utilisateur
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Obtenir les likes de l'utilisateur
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Obtenir les demandes d'amitié envoyées par l'utilisateur
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    /**
     * Obtenir les demandes d'amitié reçues par l'utilisateur
     */
    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    /**
     * Obtenir tous les amis de l'utilisateur (acceptés)
     * 
     * @return Collection
     */
    public function friends(): Collection
    {
        $sentFriendships = $this->sentFriendRequests()
            ->where('status', 'accepted')
            ->with('friend')
            ->get();
            
        $receivedFriendships = $this->receivedFriendRequests()
            ->where('status', 'accepted')
            ->with('user')
            ->get();

        $friends = collect();
        
        foreach ($sentFriendships as $friendship) {
            $friends->push($friendship->friend);
        }
        
        foreach ($receivedFriendships as $friendship) {
            $friends->push($friendship->user);
        }
        
        return $friends->unique('id');
    }

    /**
     * Vérifier si l'utilisateur est ami avec un autre utilisateur
     * 
     * @param User $user
     * @return bool
     */
    public function isFriendWith(User $user): bool
    {
        // Si c'est le même utilisateur
        if ($this->id === $user->id) {
            return false;
        }
        
        $sentFriendship = $this->sentFriendRequests()
            ->where('friend_id', $user->id)
            ->where('status', 'accepted')
            ->exists();
            
        $receivedFriendship = $this->receivedFriendRequests()
            ->where('user_id', $user->id)
            ->where('status', 'accepted')
            ->exists();
            
        return $sentFriendship || $receivedFriendship;
    }
    
    /**
     * Obtenir le nombre d'amis en commun avec un autre utilisateur
     * 
     * @param User $user
     * @return int
     */
    public function getMutualFriendsCount(User $user): int
    {
        try {
            $myFriends = $this->friends()->pluck('id')->toArray();
            $theirFriends = $user->friends()->pluck('id')->toArray();
            
            return count(array_intersect($myFriends, $theirFriends));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Erreur lors du calcul des amis communs: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtenir la liste des amis en commun avec un autre utilisateur
     * 
     * @param User $user
     * @return Collection
     */
    public function getMutualFriends(User $user): Collection
    {
        try {
            $myFriends = $this->friends();
            $theirFriendsIds = $user->friends()->pluck('id')->toArray();
            
            return $myFriends->filter(function($friend) use ($theirFriendsIds) {
                return in_array($friend->id, $theirFriendsIds);
            })->values();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Erreur lors de la récupération des amis communs: " . $e->getMessage());
            return collect();
        }
    }
}
