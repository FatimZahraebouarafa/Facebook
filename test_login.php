<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simuler les classes Laravel pour le test
class FakeUser {
    public $id = 1;
    public $name = "Utilisateur Test";
    public $email = "test@example.com";
    public $password;
    
    public function __construct($email, $password) {
        $this->email = $email;
        $this->password = $password ? password_hash($password, PASSWORD_BCRYPT) : null;
    }
}

class FakeAuth {
    private static $authenticated = false;
    private static $user = null;
    
    public static function attempt($credentials) {
        // Vérifier si les credentials correspondent à l'utilisateur simulé
        if ($credentials['email'] === 'test@example.com' && 
            password_verify($credentials['password'], self::$user->password)) {
            self::$authenticated = true;
            return true;
        }
        return false;
    }
    
    public static function check() {
        return self::$authenticated;
    }
    
    public static function user() {
        return self::$authenticated ? self::$user : null;
    }
    
    public static function logout() {
        self::$authenticated = false;
    }
    
    public static function setUser($user) {
        self::$user = $user;
    }
}

// Données de test
$email = 'test@example.com';
$password = 'password123';

echo "Test de connexion utilisateur\n";
echo "----------------------------\n";
echo "Email: $email\n";
echo "Mot de passe: $password\n\n";

try {
    // Créer un utilisateur de test
    $user = new FakeUser($email, $password);
    
    echo "Utilisateur créé avec l'ID: {$user->id}\n\n";
    
    // Configurer l'environnement de test
    FakeAuth::setUser($user);
    
    // Tester le hash du mot de passe
    echo "Test du hachage du mot de passe\n";
    echo "----------------------------\n";
    
    $hashedPassword = $user->password;
    echo "Mot de passe haché: " . substr($hashedPassword, 0, 10) . "...\n";
    
    $passwordCheck = password_verify($password, $hashedPassword);
    echo "Vérification du mot de passe: " . ($passwordCheck ? "RÉUSSI" : "ÉCHEC") . "\n\n";
    
    // Tentative de connexion
    echo "Tentative de connexion\n";
    echo "----------------------------\n";
    
    // Déconnexion d'abord pour être sûr
    FakeAuth::logout();
    
    // Tentative de connexion
    $credentials = [
        'email' => $email,
        'password' => $password
    ];
    
    $loginSuccess = FakeAuth::attempt($credentials);
    
    if ($loginSuccess) {
        echo "Connexion RÉUSSIE!\n";
        echo "Utilisateur authentifié: " . FakeAuth::user()->name . " (ID: " . FakeAuth::user()->id . ")\n";
        
        // Test de session simplifié
        echo "\nInformation de session\n";
        echo "----------------------------\n";
        $session_id = session_status() === PHP_SESSION_ACTIVE ? session_id() : "Session non active";
        echo "ID de session: $session_id\n";
        
        if (session_status() === PHP_SESSION_ACTIVE) {
            echo "Session active et fonctionnelle\n";
        } else {
            echo "Session non active - démarrage d'une session de test\n";
            if (session_start()) {
                echo "Session démarrée avec succès\n";
                echo "Nouvel ID de session: " . session_id() . "\n";
            } else {
                echo "ERREUR: Impossible de démarrer la session!\n";
                
                // Vérifier le répertoire des sessions
                $sessions_dir = ini_get('session.save_path');
                echo "Répertoire des sessions: " . $sessions_dir . "\n";
                echo "Le répertoire existe: " . (is_dir($sessions_dir) ? "OUI" : "NON") . "\n";
                echo "Le répertoire est accessible en écriture: " . (is_writable($sessions_dir) ? "OUI" : "NON") . "\n";
            }
        }
    } else {
        echo "Connexion ÉCHOUÉE!\n";
        echo "Raisons possibles:\n";
        echo "- Mot de passe incorrect\n";
        echo "- Problème de hachage\n";
        echo "- Problème de configuration de l'authentification\n";
        echo "- Problème de session\n";
    }
} catch (\Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
    echo "Dans le fichier: " . $e->getFile() . " à la ligne " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest terminé à " . date('Y-m-d H:i:s') . "\n";