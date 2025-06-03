<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Afficher toutes les publications
     */
    public function index()
    {
        $posts = Post::with(['user', 'comments.user', 'likes'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('posts.index', compact('posts'));
    }

    /**
     * Afficher les publications de l'utilisateur et de ses amis
     */
    public function feed()
    {
        try {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            
            // Vérifier si l'utilisateur est bien connecté
            if (!$user) {
                return redirect()->route('login')->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }
            
            // Par défaut, afficher uniquement les publications de l'utilisateur
            $friendIds = [$user->id];
            
            // Récupérer les amis en évitant l'erreur de méthode
            try {
                // Vérifier si les méthodes existent
                if (method_exists($user, 'sentFriendRequests') && method_exists($user, 'receivedFriendRequests')) {
                    // Amis issus des demandes envoyées
                    $sentFriendships = $user->sentFriendRequests()
                        ->where('status', 'accepted')
                        ->with('friend')
                        ->get();
                        
                    foreach ($sentFriendships as $friendship) {
                        if ($friendship->friend && $friendship->friend->id) {
                            $friendIds[] = $friendship->friend->id;
                        }
                    }
                    
                    // Amis issus des demandes reçues
                    $receivedFriendships = $user->receivedFriendRequests()
                        ->where('status', 'accepted')
                        ->with('user')
                        ->get();
                        
                    foreach ($receivedFriendships as $friendship) {
                        if ($friendship->user && $friendship->user->id) {
                            $friendIds[] = $friendship->user->id;
                        }
                    }
                    
                    // Éliminer les doublons
                    $friendIds = array_unique($friendIds);
                }
            } catch (\Exception $e) {
                // En cas d'erreur, continuer avec uniquement l'ID de l'utilisateur
                \Illuminate\Support\Facades\Log::error('Erreur lors de la récupération des amis : ' . $e->getMessage());
            }
            
            // Charger les publications avec une gestion des erreurs
            try {
                $posts = Post::with(['user', 'comments.user', 'likes'])
                    ->whereIn('user_id', $friendIds)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                    
                return view('posts.feed', compact('posts'));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erreur lors du chargement des publications : ' . $e->getMessage());
                return view('posts.feed', ['posts' => collect()])->with('error', 'Erreur lors du chargement des publications.');
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur générale dans feed() : ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Une erreur est survenue. Veuillez vous reconnecter.');
        }
    }

    /**
     * Afficher le formulaire de création d'une publication
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Enregistrer une nouvelle publication
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'content' => 'required|string',
                'image' => 'nullable|image|max:2048', // max 2MB
            ]);
            
            $post = new Post();
            $post->user_id = Auth::id();
            $post->content = $validated['content'];
            
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('posts', 'public');
                $post->image_path = $imagePath;
            }
            
            $post->save();
            
            // Log pour le débogage
            \Illuminate\Support\Facades\Log::info('Publication créée', [
                'user_id' => Auth::id(),
                'post_id' => $post->id,
                'has_image' => $request->hasFile('image')
            ]);
            
            return redirect()->route('home')->with('success', 'Publication créée avec succès!');
        } catch (\Exception $e) {
            // Log l'erreur
            \Illuminate\Support\Facades\Log::error('Erreur lors de la création d\'une publication', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la création de la publication. Veuillez réessayer.');
        }
    }

    /**
     * Afficher une publication spécifique
     */
    public function show(Post $post)
    {
        $post->load(['user', 'comments.user', 'likes']);
        return view('posts.show', compact('post'));
    }

    /**
     * Afficher le formulaire d'édition d'une publication
     */
    public function edit(Post $post)
    {
        // Vérifier que l'utilisateur est le propriétaire de la publication
        if (Auth::id() !== $post->user_id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier cette publication.');
        }
        
        return view('posts.edit', compact('post'));
    }

    /**
     * Mettre à jour une publication
     */
    public function update(Request $request, Post $post)
    {
        // Vérifier que l'utilisateur est le propriétaire de la publication
        if (Auth::id() !== $post->user_id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à modifier cette publication.');
        }
        
        $validated = $request->validate([
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048', // max 2MB
        ]);
        
        $post->content = $validated['content'];
        
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            
            $imagePath = $request->file('image')->store('posts', 'public');
            $post->image_path = $imagePath;
        }
        
        $post->save();
        
        return redirect()->route('posts.show', $post)->with('success', 'Publication mise à jour avec succès!');
    }

    /**
     * Supprimer une publication
     */
    public function destroy(Post $post)
    {
        // Vérifier que l'utilisateur est le propriétaire de la publication
        if (Auth::id() !== $post->user_id) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à supprimer cette publication.');
        }
        
        // Supprimer l'image associée si elle existe
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }
        
        $post->delete();
        
        return redirect()->route('home')->with('success', 'Publication supprimée avec succès!');
    }
} 