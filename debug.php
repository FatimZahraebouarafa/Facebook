<?php
// Afficher les erreurs PHP pour le diagnostic
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure l'autoloader de Composer
require __DIR__ . '/vendor/autoload.php';

// Charger les variables d'environnement
$app = require_once __DIR__ . '/bootstrap/app.php';

// Obtenir le noyau HTTP
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Tester la création de publication
function testPostCreation() {
    try {
        $user = \App\Models\User::first();
        if (!$user) {
            echo "Aucun utilisateur trouvé dans la base de données.<br>";
            return;
        }

        $post = new \App\Models\Post();
        $post->user_id = $user->id;
        $post->content = "Publication de test via le script de débogage";
        $post->save();

        echo "Publication créée avec succès. ID: " . $post->id . "<br>";
    } catch (\Exception $e) {
        echo "Erreur lors de la création de la publication: " . $e->getMessage() . "<br>";
        echo "Trace: <pre>" . $e->getTraceAsString() . "</pre><br>";
    }
}

// Tester l'envoi d'une demande d'amitié
function testFriendRequest() {
    try {
        // Trouver deux utilisateurs pour tester
        $users = \App\Models\User::take(2)->get();
        if (count($users) < 2) {
            echo "Pas assez d'utilisateurs pour tester les demandes d'amitié.<br>";
            return;
        }

        $sender = $users[0];
        $receiver = $users[1];

        // Vérifier si une demande existe déjà
        $existingRequest = \App\Models\Friendship::where(function($query) use ($sender, $receiver) {
            $query->where('user_id', $sender->id)
                  ->where('friend_id', $receiver->id);
        })->orWhere(function($query) use ($sender, $receiver) {
            $query->where('user_id', $receiver->id)
                  ->where('friend_id', $sender->id);
        })->first();

        if ($existingRequest) {
            echo "Une demande d'amitié existe déjà entre ces utilisateurs.<br>";
            echo "Statut: " . $existingRequest->status . "<br>";
            return;
        }

        // Créer une nouvelle demande d'amitié
        $friendship = new \App\Models\Friendship();
        $friendship->user_id = $sender->id;
        $friendship->friend_id = $receiver->id;
        $friendship->status = 'pending';
        $friendship->save();

        echo "Demande d'amitié créée avec succès. ID: " . $friendship->id . "<br>";
    } catch (\Exception $e) {
        echo "Erreur lors de la création de la demande d'amitié: " . $e->getMessage() . "<br>";
        echo "Trace: <pre>" . $e->getTraceAsString() . "</pre><br>";
    }
}

// Vérifier la structure des tables
function checkTableStructure() {
    try {
        $tables = ['posts', 'friendships', 'likes', 'comments'];
        
        foreach ($tables as $table) {
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            echo "<strong>Table: {$table}</strong><br>";
            echo "Colonnes: " . implode(', ', $columns) . "<br><br>";
        }
    } catch (\Exception $e) {
        echo "Erreur lors de la vérification des tables: " . $e->getMessage() . "<br>";
    }
}

// Exécuter les tests
echo "<h1>Tests de débogage</h1>";

echo "<h2>Structure des tables</h2>";
checkTableStructure();

echo "<h2>Test de création de publication</h2>";
testPostCreation();

echo "<h2>Test de demande d'amitié</h2>";
testFriendRequest();

echo "<p>Tests terminés.</p>";

echo "<h1>Diagnostic d'erreur</h1>";

// Vérifier que le fichier .env existe
echo "<h2>Vérification du fichier .env</h2>";
if (file_exists('../.env')) {
    echo "Le fichier .env existe. <br>";
    
    // Vérifier que le fichier .env est lisible
    if (is_readable('../.env')) {
        echo "Le fichier .env est lisible. <br>";
        
        // Afficher quelques valeurs clés de configuration (sans mots de passe)
        $env_content = file_get_contents('../.env');
        $lines = explode("\n", $env_content);
        echo "<pre>";
        foreach ($lines as $line) {
            if (empty(trim($line)) || strpos($line, '#') === 0) {
                continue;
            }
            
            // Ne pas afficher les mots de passe et informations sensibles
            if (strpos($line, 'PASSWORD') !== false || 
                strpos($line, 'KEY') !== false || 
                strpos($line, 'SECRET') !== false) {
                $parts = explode('=', $line, 2);
                if (count($parts) > 1) {
                    echo $parts[0] . "=********\n";
                }
            } else {
                echo $line . "\n";
            }
        }
        echo "</pre>";
    } else {
        echo "Le fichier .env n'est pas lisible. <br>";
    }
} else {
    echo "Le fichier .env n'existe pas. <br>";
}

// Vérifier la structure des répertoires
echo "<h2>Vérification des répertoires</h2>";
$directories = [
    '../storage',
    '../storage/logs',
    '../storage/framework',
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../storage/framework/cache',
    '../bootstrap/cache'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "Le répertoire $dir existe. ";
        if (is_writable($dir)) {
            echo "Il est accessible en écriture. <br>";
        } else {
            echo "Il n'est PAS accessible en écriture. <strong>C'est un problème !</strong><br>";
        }
    } else {
        echo "Le répertoire $dir n'existe pas. <strong>C'est un problème !</strong><br>";
    }
}

// Vérifier la connexion à la base de données
echo "<h2>Vérification de la base de données</h2>";
if (file_exists('../.env')) {
    $env_content = file_get_contents('../.env');
    preg_match('/DB_CONNECTION=(.*)/', $env_content, $db_connection);
    preg_match('/DB_HOST=(.*)/', $env_content, $db_host);
    preg_match('/DB_PORT=(.*)/', $env_content, $db_port);
    preg_match('/DB_DATABASE=(.*)/', $env_content, $db_name);
    preg_match('/DB_USERNAME=(.*)/', $env_content, $db_user);
    preg_match('/DB_PASSWORD=(.*)/', $env_content, $db_pass);
    
    // Ne pas afficher le mot de passe
    if (isset($db_connection[1]) && isset($db_host[1]) && isset($db_port[1]) && isset($db_name[1]) && isset($db_user[1])) {
        echo "Configuration de base de données trouvée:<br>";
        echo "- Type: " . $db_connection[1] . "<br>";
        echo "- Hôte: " . $db_host[1] . "<br>";
        echo "- Port: " . $db_port[1] . "<br>";
        echo "- Base de données: " . $db_name[1] . "<br>";
        echo "- Utilisateur: " . $db_user[1] . "<br>";
        
        // Essayer de se connecter à la base de données
        try {
            if ($db_connection[1] === 'mysql') {
                $dsn = "mysql:host={$db_host[1]};port={$db_port[1]};dbname={$db_name[1]}";
                $pdo = new PDO($dsn, $db_user[1], $db_pass[1]);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                echo "Connexion à la base de données réussie ! <br>";
                
                // Vérifier l'existence des tables clés
                $tables = ['users', 'posts', 'comments', 'likes', 'friendships'];
                foreach ($tables as $table) {
                    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                    $stmt->execute([$table]);
                    if ($stmt->rowCount() > 0) {
                        echo "- Table '$table' existe. <br>";
                    } else {
                        echo "- Table '$table' n'existe pas. <strong>C'est peut-être un problème !</strong><br>";
                    }
                }
            } else {
                echo "Type de base de données non pris en charge par ce script de diagnostic. <br>";
            }
        } catch (PDOException $e) {
            echo "Erreur de connexion à la base de données: " . $e->getMessage() . "<br>";
        }
    } else {
        echo "Configuration de base de données incomplète dans le fichier .env. <br>";
    }
} else {
    echo "Impossible de vérifier la base de données car le fichier .env est manquant. <br>";
}

// Vérifier les extensions PHP nécessaires
echo "<h2>Vérification des extensions PHP</h2>";
$required_extensions = [
    'pdo',
    'pdo_mysql',
    'openssl',
    'mbstring',
    'tokenizer',
    'xml',
    'ctype',
    'json',
    'fileinfo',
    'bcmath'
];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "Extension $ext chargée. <br>";
    } else {
        echo "Extension $ext NON chargée. <strong>C'est un problème !</strong><br>";
    }
}

echo "<h2>Version PHP</h2>";
echo "Version PHP: " . phpversion() . "<br>";

echo "<h2>Autres informations</h2>";
echo "Espace disque disponible: " . round(disk_free_space('/') / 1024 / 1024 / 1024, 2) . " Go<br>";
echo "Mémoire maximale allouée à PHP: " . ini_get('memory_limit') . "<br>";
echo "Temps maximum d'exécution: " . ini_get('max_execution_time') . " secondes<br>";
?> 