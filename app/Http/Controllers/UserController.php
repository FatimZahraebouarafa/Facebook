<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Afficher le profil d'un utilisateur
     */
    public function show(User $user)
    {
        $posts = Post::where('user_id', $user->id)
                     ->with(['user', 'comments.user', 'likes'])
                     ->orderBy('created_at', 'desc')
                     ->paginate(10);
        
        // Vérification si l'utilisateur est ami
        $isFriend = false;
        if (Auth::check()) {
            try {
                /** @var User $currentUser */
                $currentUser = Auth::user();
                $isFriend = $currentUser->isFriendWith($user);
            } catch (\Exception $e) {
                // Ignorer les erreurs
            }
        }
        
        return view('users.profile', compact('user', 'posts', 'isFriend'));
    }

    /**
     * Afficher le formulaire d'édition du profil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('users.edit', compact('user'));
    }

    /**
     * Mettre à jour le profil de l'utilisateur
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|max:2048', // max 2MB
            'cover_photo' => 'nullable|image|max:2048',
        ]);
        
        $user->name = $validated['name'];
        $user->bio = $validated['bio'] ?? $user->bio;
        
        // Traitement de l'image de profil
        if ($request->hasFile('profile_picture')) {
            // Supprimer l'ancienne image si elle existe
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
        }
        
        // Traitement de la photo de couverture
        if ($request->hasFile('cover_photo')) {
            // Supprimer l'ancienne image si elle existe
            if ($user->cover_photo) {
                Storage::disk('public')->delete($user->cover_photo);
            }
            
            $path = $request->file('cover_photo')->store('cover-photos', 'public');
            $user->cover_photo = $path;
        }
        
        try {
            $user->save();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la mise à jour du profil.');
        }
        
        return redirect()->route('profile.show', $user)->with('success', 'Profil mis à jour avec succès!');
    }

    /**
     * Afficher le formulaire de changement de mot de passe
     */
    public function editPassword()
    {
        return view('users.edit-password');
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        /** @var User $user */
        $user = Auth::user();
        
        // Vérifier que le mot de passe actuel est correct
        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }
        
        $user->password = Hash::make($validated['password']);
        
        try {
            $user->save();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Une erreur est survenue lors de la mise à jour du mot de passe.');
        }
        
        return redirect()->route('profile.edit')->with('success', 'Mot de passe mis à jour avec succès!');
    }

    /**
     * Afficher la liste des utilisateurs pour la recherche d'amis
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $sort = $request->get('sort', 'name'); // Par défaut, tri par nom
            $order = $request->get('order', 'asc'); // Par défaut, ordre ascendant
            
            // Valider les valeurs de tri pour éviter les injections SQL
            $validSortFields = ['name', 'created_at', 'email'];
            $validOrderDirections = ['asc', 'desc'];
            
            if (!in_array($sort, $validSortFields)) {
                $sort = 'name';
            }
            
            if (!in_array($order, $validOrderDirections)) {
                $order = 'asc';
            }
            
            $query = User::where('id', '!=', Auth::id());
            
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }
            
            // Appliquer le tri
            $query->orderBy($sort, $order);
            
            $users = $query->paginate(20)->appends([
                'search' => $search,
                'sort' => $sort,
                'order' => $order
            ]);
            
            // Déterminer pour chaque utilisateur s'il est déjà ami
            if (Auth::check()) {
                $currentUser = Auth::user();
                foreach ($users as $user) {
                    try {
                        $user->is_friend = $currentUser->isFriendWith($user);
                        
                        // Vérifier s'il y a une demande d'amitié en attente
                        $sentRequest = $currentUser->sentFriendRequests()
                            ->where('friend_id', $user->id)
                            ->where('status', 'pending')
                            ->exists();
                            
                        $receivedRequest = $currentUser->receivedFriendRequests()
                            ->where('user_id', $user->id)
                            ->where('status', 'pending')
                            ->exists();
                            
                        $user->has_pending_request = $sentRequest || $receivedRequest;
                        
                        // Ajouter des informations supplémentaires pour l'affichage
                        $user->mutual_friends_count = $currentUser->getMutualFriendsCount($user);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("Erreur lors de la vérification des relations d'amitié: " . $e->getMessage());
                        $user->is_friend = false;
                        $user->has_pending_request = false;
                        $user->mutual_friends_count = 0;
                    }
                }
            }
            
            return view('users.index', compact('users', 'search', 'sort', 'order'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erreur dans index() de UserController : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la récupération des utilisateurs.');
        }
    }
} 