<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Ajouter un like à une publication
     */
    public function like(Post $post)
    {
        // Vérifier si l'utilisateur a déjà aimé la publication
        $existingLike = Like::where('user_id', Auth::id())
                           ->where('post_id', $post->id)
                           ->first();
                           
        if ($existingLike) {
            return redirect()->back()->with('error', 'Vous avez déjà aimé cette publication.');
        }
        
        // Créer un nouveau like
        $like = new Like();
        $like->user_id = Auth::id();
        $like->post_id = $post->id;
        $like->save();
        
        // Incrémenter le compteur de likes de la publication
        $post->likes_count = $post->likes()->count();
        $post->save();
        
        return redirect()->back()->with('success', 'Vous avez aimé cette publication!');
    }

    /**
     * Retirer un like d'une publication
     */
    public function unlike(Post $post)
    {
        // Trouver et supprimer le like
        $like = Like::where('user_id', Auth::id())
                    ->where('post_id', $post->id)
                    ->first();
                    
        if (!$like) {
            return redirect()->back()->with('error', 'Vous n\'avez pas aimé cette publication.');
        }
        
        $like->delete();
        
        // Mettre à jour le compteur de likes
        $post->likes_count = $post->likes()->count();
        $post->save();
        
        return redirect()->back()->with('success', 'Vous n\'aimez plus cette publication.');
    }

    /**
     * Toggle like/unlike (utile pour les interfaces AJAX)
     */
    public function toggle(Post $post)
    {
        // Vérifier si l'utilisateur a déjà aimé la publication
        $existingLike = Like::where('user_id', Auth::id())
                           ->where('post_id', $post->id)
                           ->first();
        
        if ($existingLike) {
            // Si déjà aimé, on retire le like
            $existingLike->delete();
            $message = 'Vous n\'aimez plus cette publication.';
            $liked = false;
        } else {
            // Sinon, on ajoute un like
            $like = new Like();
            $like->user_id = Auth::id();
            $like->post_id = $post->id;
            $like->save();
            $message = 'Vous avez aimé cette publication!';
            $liked = true;
        }
        
        // Mettre à jour le compteur de likes
        $post->likes_count = $post->likes()->count();
        $post->save();
        
        // Toujours retourner une réponse JSON si la requête est AJAX
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'liked' => $liked,
                'likesCount' => $post->likes_count,
                'message' => $message
            ]);
        }
        
        return redirect()->back()->with('success', $message);
    }
} 