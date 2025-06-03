<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            Log::info('Début du processus de connexion pour l\'utilisateur: ' . $request->email);
            
            $this->validateLogin($request);

            // If the class is using the ThrottlesLogins trait, we can automatically throttle
            // the login attempts for this application. We'll key this by the username and
            // the IP address of the client making these requests into this application.
            if (method_exists($this, 'hasTooManyLoginAttempts') &&
                $this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                
                Log::warning('Trop de tentatives de connexion pour l\'utilisateur: ' . $request->email);
                
                return $this->sendLockoutResponse($request);
            }

            if ($this->attemptLogin($request)) {
                return $this->sendLoginResponse($request);
            }

            // If the login attempt was unsuccessful we will increment the number of attempts
            // to login and redirect the user back to the login form. Of course, when this
            // user surpasses their maximum number of attempts they will get locked out.
            $this->incrementLoginAttempts($request);
            
            Log::warning('Identifiants invalides pour l\'utilisateur: ' . $request->email);
            
            return $this->sendFailedLoginResponse($request);
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
            
            // Retourner une réponse d'erreur
            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors(['email' => 'Une erreur système est survenue. Veuillez réessayer ultérieurement.']);
        }
    }

    protected function attemptLogin(Request $request)
    {
        try {
            Log::info('Tentative de connexion pour l\'utilisateur: ' . $request->email);
            
            // Vérifiez que les identifiants sont bien définis
            $credentials = $this->credentials($request);
            if (empty($credentials['email']) || empty($credentials['password'])) {
                Log::warning('Tentative de connexion avec des identifiants incomplets');
                return false;
            }
            
            $result = $this->guard()->attempt(
                $credentials, $request->filled('remember')
            );
            
            if (!$result) {
                Log::warning('Échec d\'authentification pour l\'utilisateur: ' . $request->email);
            } else {
                Log::info('Connexion réussie pour l\'utilisateur: ' . $request->email);
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Exception lors de la connexion pour l\'utilisateur ' . $request->email . ': ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            throw $e; // Relancer l'exception pour qu'elle soit gérée par la méthode login
        }
    }

    protected function sendLoginResponse(Request $request)
    {
        try {
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
            } catch (\Exception $e) {
                Log::error('Erreur lors de la régénération de la session: ' . $e->getMessage());
                // Continuer malgré l'erreur
            }
            
            $this->clearLoginAttempts($request);
            
            Log::info('Préparation de la redirection après connexion pour l\'utilisateur: ' . $request->email);
            
            if (!$this->guard()->user()) {
                Log::error('Utilisateur non trouvé dans la session après connexion réussie');
                throw new \Exception('Utilisateur non trouvé dans la session après connexion réussie');
            }
            
            return $this->authenticated($request, $this->guard()->user())
                ?: redirect()->intended($this->redirectPath());
        } catch (\Exception $e) {
            Log::error('Exception lors de la redirection après connexion: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            // Nettoyage de la session en cas d'erreur
            try {
                Session::flush();
                Auth::logout();
            } catch (\Exception $ex) {
                Log::error('Erreur lors du nettoyage de la session: ' . $ex->getMessage());
            }
            
            // Redirection de secours en cas d'erreur
            return redirect('/')->with('error', 'Une erreur est survenue lors de la connexion. Veuillez réessayer.');
        }
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            $user = $this->guard()->user();
            $email = $user ? $user->email : 'inconnu';
            
            Log::info('Déconnexion de l\'utilisateur: ' . $email);
            
            $this->guard()->logout();
            
            // Suppression sécurisée de la session
            try {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } catch (\Exception $ex) {
                Log::error('Erreur lors de l\'invalidation de la session: ' . $ex->getMessage());
                Session::flush();
            }
            
            Log::info('Déconnexion réussie pour l\'utilisateur: ' . $email);
            
            return $this->loggedOut($request) ?: redirect('/');
        } catch (\Exception $e) {
            Log::error('Exception lors de la déconnexion: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            // Nettoyage de force de la session
            Session::flush();
            
            return redirect('/');
        }
    }
}