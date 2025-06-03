<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    // Afficher le formulaire de login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Traiter la connexion
    public function login(Request $request)
    {
        try {
            Log::info('Tentative de connexion pour l\'utilisateur: ' . $request->email);
            
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            
            // Vérification supplémentaire pour s'assurer que les identifiants sont bien formés
            if (empty($credentials['email']) || empty($credentials['password'])) {
                Log::warning('Tentative de connexion avec des identifiants incomplets');
                return back()->withErrors(['email' => 'Veuillez fournir un email et un mot de passe.']);
            }

            if (Auth::attempt($credentials)) {
                // S'assurer que la session est correctement configurée
                if (!$request->hasSession() || !$request->session()->isStarted()) {
                    Log::error('Session non démarrée avant la régénération - correction en cours');
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                }
                
                // Régénérer la session avec gestion d'erreur
                try {
                    $request->session()->regenerate();
                    Log::info('Session régénérée avec succès pour l\'utilisateur: ' . $request->email);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la régénération de la session: ' . $e->getMessage());
                    Log::error('Trace: ' . $e->getTraceAsString());
                    // Continuer malgré l'erreur
                }
                
                // Vérification que l'utilisateur est bien connecté
                if (!Auth::check()) {
                    Log::error('Utilisateur non authentifié après Auth::attempt réussi');
                    throw new \Exception('Échec de l\'authentification après tentative réussie');
                }
                
                Log::info('Connexion réussie pour l\'utilisateur: ' . Auth::user()->email);
                
                // Ajout du message flash
                return redirect()->intended('/home')
                       ->with('success', 'Connexion réussie ! Bienvenue ' . Auth::user()->name);
            }
            
            Log::warning('Échec d\'authentification pour l\'utilisateur: ' . $request->email);

            return back()->withErrors([
                'email' => 'Identifiants incorrects.',
            ])->onlyInput('email');
        } catch (\Exception $e) {
            Log::error('Exception non gérée pendant le processus de connexion: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            // En cas d'erreur critique, essayons de nettoyer la session
            try {
                Session::flush();
                Auth::logout();
            } catch (\Exception $ex) {
                Log::error('Erreur lors du nettoyage de la session: ' . $ex->getMessage());
            }
            
            return back()->withErrors([
                'email' => 'Une erreur système est survenue. Veuillez réessayer ultérieurement.',
            ])->onlyInput('email');
        }
    }

    // Déconnexion
    public function logout(Request $request)
    {
        try {
            $user = Auth::user();
            $email = $user ? $user->email : 'inconnu';
            
            Log::info('Déconnexion de l\'utilisateur: ' . $email);
            
            Auth::logout();
            
            // Suppression sécurisée de la session
            try {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                Log::info('Session invalidée avec succès');
            } catch (\Exception $ex) {
                Log::error('Erreur lors de l\'invalidation de la session: ' . $ex->getMessage());
                Session::flush();
            }
            
            Log::info('Déconnexion réussie pour l\'utilisateur: ' . $email);
            
            return redirect('/');
        } catch (\Exception $e) {
            Log::error('Exception lors de la déconnexion: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            // Nettoyage de force de la session
            Session::flush();
            
            return redirect('/');
        }
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            Log::info('Validation passed. Creating user with: ' . json_encode([
                'name' => $request->name,
                'email' => $request->email
            ]));

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Log::info('User created with ID: ' . $user->id);
            
            // S'assurer que la session est correctement configurée
            if (!$request->hasSession() || !$request->session()->isStarted()) {
                Log::error('Session non démarrée avant la connexion - correction en cours');
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
            }

            Auth::login($user);
            
            // Vérification que l'utilisateur est bien connecté
            if (!Auth::check()) {
                Log::error('Utilisateur non authentifié après Auth::login');
                throw new \Exception('Échec de l\'authentification après création de compte');
            }

            return redirect('/home')->with('success', 'Compte créé avec succès ! Bienvenue ' . $user->name);
        } catch (\Exception $e) {
            Log::error('Error during registration: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            return back()->withErrors(['email' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
        }
    }
}
