<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FriendshipController extends Controller
{
    /**
     * Afficher la liste des amis de l'utilisateur
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $friends = collect(); // Collection vide par défaut
        
        // Tenter de récupérer les amis
        try {
            if (method_exists($user, 'friends')) {
                $friends = $user->friends();
            }
        } catch (\Exception $e) {
            // Gérer silencieusement l'erreur
        }
        
        return view('friendships.index', compact('friends'));
    }

    /**
     * Afficher les demandes d'amitié en attente
     */
    public function requests()
    {
        $pendingRequests = Friendship::where('friend_id', Auth::id())
                                    ->where('status', 'pending')
                                    ->with('user')
                                    ->get();
                                    
        return view('friendships.requests', compact('pendingRequests'));
    }

    /**
     * Envoyer une demande d'amitié
     */
    public function sendRequest(User $user)
    {
        try {
            // Vérifier qu'on n'envoie pas une demande à nous-même
            if (Auth::id() === $user->id) {
                return redirect()->back()->with('error', 'Vous ne pouvez pas vous envoyer une demande d\'amitié à vous-même.');
            }
            
            // Vérifier si une demande existe déjà
            $existingRequest = Friendship::where(function($query) use ($user) {
                                    $query->where('user_id', Auth::id())
                                        ->where('friend_id', $user->id);
                                })
                                ->orWhere(function($query) use ($user) {
                                    $query->where('user_id', $user->id)
                                        ->where('friend_id', Auth::id());
                                })
                                ->first();
                                
            if ($existingRequest) {
                if ($existingRequest->status === 'pending') {
                    return redirect()->back()->with('error', 'Une demande d\'amitié est déjà en attente.');
                } elseif ($existingRequest->status === 'accepted') {
                    return redirect()->back()->with('error', 'Vous êtes déjà ami avec cet utilisateur.');
                } else {
                    // Si la demande a été rejetée, on peut permettre d'en envoyer une nouvelle
                    $existingRequest->delete();
                }
            }
            
            // Créer une nouvelle demande d'amitié
            $friendship = new Friendship();
            $friendship->user_id = Auth::id();
            $friendship->friend_id = $user->id;
            $friendship->status = 'pending';
            $friendship->save();
            
            // Log pour le débogage
            \Illuminate\Support\Facades\Log::info('Demande d\'amitié envoyée', [
                'user_id' => Auth::id(),
                'friend_id' => $user->id,
                'friendship_id' => $friendship->id
            ]);
            
            return redirect()->back()->with('success', 'Demande d\'amitié envoyée!');
        } catch (\Exception $e) {
            // Log l'erreur
            \Illuminate\Support\Facades\Log::error('Erreur lors de l\'envoi d\'une demande d\'amitié', [
                'user_id' => Auth::id(),
                'friend_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Une erreur est survenue lors de l\'envoi de la demande d\'amitié. Veuillez réessayer.');
        }
    }

    /**
     * Accepter une demande d'amitié
     */
    public function acceptRequest(Friendship $friendship)
    {
        // Vérifier que la demande est adressée à l'utilisateur actuel
        if ($friendship->friend_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à accepter cette demande.');
        }
        
        $friendship->status = 'accepted';
        $friendship->save();
        
        return redirect()->back()->with('success', 'Demande d\'amitié acceptée!');
    }

    /**
     * Rejeter une demande d'amitié
     */
    public function rejectRequest(Friendship $friendship)
    {
        // Vérifier que la demande est adressée à l'utilisateur actuel
        if ($friendship->friend_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas autorisé à rejeter cette demande.');
        }
        
        $friendship->status = 'rejected';
        $friendship->save();
        
        return redirect()->back()->with('success', 'Demande d\'amitié rejetée.');
    }

    /**
     * Supprimer une amitié
     */
    public function removeFriend(User $user)
    {
        $friendship = Friendship::where(function($query) use ($user) {
                            $query->where('user_id', Auth::id())
                                  ->where('friend_id', $user->id);
                         })
                         ->orWhere(function($query) use ($user) {
                            $query->where('user_id', $user->id)
                                  ->where('friend_id', Auth::id());
                         })
                         ->where('status', 'accepted')
                         ->first();
                         
        if (!$friendship) {
            return redirect()->back()->with('error', 'Vous n\'êtes pas ami avec cet utilisateur.');
        }
        
        $friendship->delete();
        
        return redirect()->back()->with('success', 'Vous n\'êtes plus ami avec cet utilisateur.');
    }
} 