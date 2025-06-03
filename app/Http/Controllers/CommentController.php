<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Enregistrer un nouveau commentaire
     */
    public function store(Request $request, Post $post)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);
        
        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->post_id = $post->id;
        $comment->content = $validated['content'];
        $comment->save();
        
        return redirect()->back()->with('success', 'Commentaire ajouté avec succès!');
    }

    /**
     * Mettre à jour un commentaire existant
     */
    public function update(Request $request, Comment $comment)
    {
        // Vérifier que l'utilisateur est le propriétaire du commentaire
        if (Auth::id() !== $comment->user_id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier ce commentaire.');
        }
        
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);
        
        $comment->content = $validated['content'];
        $comment->save();
        
        return redirect()->back()->with('success', 'Commentaire mis à jour avec succès!');
    }

    /**
     * Supprimer un commentaire
     */
    public function destroy(Comment $comment)
    {
        // Vérifier que l'utilisateur est le propriétaire du commentaire ou de la publication
        if (Auth::id() !== $comment->user_id && Auth::id() !== $comment->post->user_id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer ce commentaire.');
        }
        
        $comment->delete();
        
        return redirect()->back()->with('success', 'Commentaire supprimé avec succès!');
    }
} 