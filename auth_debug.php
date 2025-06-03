<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic des problèmes d'authentification</h1>";

// Vérifier les fichiers de logs spécifiques à l'authentification
$laravel_log = '../storage/logs/laravel.log';
if (file_exists($laravel_log) && is_readable($laravel_log)) {
    echo "<h2>Analyse des logs d'authentification</h2>";
    
    $log_content = file_get_contents($laravel_log);
    
    // Rechercher les entrées liées à l'authentification
    $auth_patterns = [
        'Tentative de connexion' => '/Tentative de connexion.*?(?=\[\d{4}-\d{2}-\d{2}|$)/s',
        'Échec d\'authentification' => '/Échec d\'authentification.*?(?=\[\d{4}-\d{2}-\d{2}|$)/s',
        'Exception lors de la connexion' => '/Exception lors de la connexion.*?(?=\[\d{4}-\d{2}-\d{2}|$)/s',
        'Exception lors de la redirection' => '/Exception lors de la redirection.*?(?=\[\d{4}-\d{2}-\d{2}|$)/s'
    ];
    
    foreach ($auth_patterns as $type => $pattern) {
        if (preg_match_all($pattern, $log_content, $matches)) {
            $entries = array_slice($matches[0], -5);
            
            echo "<h3>Dernières entrées de type \"$type\" (5 dernières):</h3>";
            echo "<pre>";
            foreach ($entries as $entry) {
                echo htmlspecialchars($entry) . "\n----------------------------------------\n";
            }
            echo "</pre>";
        } else {
            echo "<p>Aucune entrée de type \"$type\" trouvée dans les logs.</p>";
        }
    }
    
    // Rechercher les erreurs générales liées à l'authentification
    $general_auth_errors = '/\[\d{4}-\d{2}-\d{2}.*?(auth|login|session|cookie|user).*?(error|exception|failed).*?(?=\[\d{4}-\d{2}-\d{2}|$)/si';
    
    if (preg_match_all($general_auth_errors, $log_content, $matches)) {
        $entries = array_slice($matches[0], -10);
        
        echo "<h3>Erreurs générales liées à l'authentification (10 dernières):</h3>";
        echo "<pre>";
        foreach ($entries as $entry) {
            echo htmlspecialchars($entry) . "\n----------------------------------------\n";
        }
        echo "</pre>";
    } else {
        echo "<p>Aucune erreur générale liée à l'authentification trouvée dans les logs.</p>";
    }
} else {
    echo "<p style='color: red;'>Le fichier de logs Laravel n'existe pas ou n'est pas lisible.</p>";
}

// Vérifier la configuration de session
echo "<h2>Configuration de session</h2>";
if (file_exists('../.env')) {
    $env_content = file_get_contents('../.env');
    
    preg_match('/SESSION_DRIVER=(.*)/', $env_content, $session_driver);
    preg_match('/SESSION_LIFETIME=(.*)/', $env_content, $session_lifetime);
    preg_match('/SESSION_SECURE_COOKIE=(.*)/', $env_content, $session_secure);
    
    echo "Driver de session: " . ($session_driver[1] ?? 'non défini') . "<br>";
    echo "Durée de vie de session: " . ($session_lifetime[1] ?? 'non défini') . " minutes<br>";
    echo "Cookie sécurisé: " . ($session_secure[1] ?? 'non défini') . "<br>";
    
    // Vérifier les répertoires de session
    $session_path = '../storage/framework/sessions';
    if (is_dir($session_path)) {
        echo "Répertoire de sessions: existe";
        
        if (is_writable($session_path)) {
            echo " et est accessible en écriture.<br>";
            
            // Compter les fichiers de session
            $session_files = glob("$session_path/*");
            $count = count($session_files);
            echo "Nombre de fichiers de session: $count<br>";
            
            if ($count > 0) {
                // Afficher l'âge des fichiers de session les plus récents
                usort($session_files, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                
                $recent_sessions = array_slice($session_files, 0, 5);
                echo "Sessions les plus récentes:<br><ul>";
                foreach ($recent_sessions as $session) {
                    $time = date('Y-m-d H:i:s', filemtime($session));
                    $size = filesize($session);
                    echo "<li>" . basename($session) . " - Dernière modification: $time, Taille: $size octets</li>";
                }
                echo "</ul>";
            }
        } else {
            echo " mais n'est PAS accessible en écriture. <strong>C'est un problème !</strong><br>";
        }
    } else {
        echo "Répertoire de sessions: n'existe PAS. <strong>C'est un problème !</strong><br>";
    }
} else {
    echo "<p style='color: red;'>Le fichier .env n'existe pas.</p>";
}

// Vérifier la table des utilisateurs
echo "<h2>Vérification de la table des utilisateurs</h2>";
if (file_exists('../.env')) {
    $env_content = file_get_contents('../.env');
    preg_match('/DB_CONNECTION=(.*)/', $env_content, $db_connection);
    preg_match('/DB_HOST=(.*)/', $env_content, $db_host);
    preg_match('/DB_PORT=(.*)/', $env_content, $db_port);
    preg_match('/DB_DATABASE=(.*)/', $env_content, $db_name);
    preg_match('/DB_USERNAME=(.*)/', $env_content, $db_user);
    preg_match('/DB_PASSWORD=(.*)/', $env_content, $db_pass);
    
    if (isset($db_connection[1]) && isset($db_host[1]) && isset($db_port[1]) && isset($db_name[1]) && isset($db_user[1]) && isset($db_pass[1])) {
        try {
            $dsn = "{$db_connection[1]}:host={$db_host[1]};port={$db_port[1]};dbname={$db_name[1]}";
            $pdo = new PDO($dsn, $db_user[1], $db_pass[1]);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "Connexion à la base de données réussie.<br>";
            
            // Vérifier la structure de la table users
            try {
                $stmt = $pdo->query("DESCRIBE users");
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo "Structure de la table users:<br><table border='1' cellpadding='5'>";
                echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
                
                $expected_columns = ['id', 'name', 'email', 'password', 'remember_token', 'created_at', 'updated_at'];
                $found_columns = [];
                
                foreach ($columns as $column) {
                    echo "<tr>";
                    echo "<td>{$column['Field']}</td>";
                    echo "<td>{$column['Type']}</td>";
                    echo "<td>{$column['Null']}</td>";
                    echo "<td>{$column['Key']}</td>";
                    echo "<td>{$column['Default']}</td>";
                    echo "</tr>";
                    
                    $found_columns[] = $column['Field'];
                }
                echo "</table><br>";
                
                // Vérifier si tous les champs requis sont présents
                $missing_columns = array_diff($expected_columns, $found_columns);
                if (!empty($missing_columns)) {
                    echo "<p style='color: red;'>Champs manquants dans la table users: " . implode(', ', $missing_columns) . "</p>";
                } else {
                    echo "<p style='color: green;'>Tous les champs requis sont présents dans la table users.</p>";
                }
                
                // Vérifier les utilisateurs
                $stmt = $pdo->query("SELECT id, name, email, LENGTH(password) as pwd_length FROM users LIMIT 5");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($users) > 0) {
                    echo "Échantillon d'utilisateurs (5 max):<br><table border='1' cellpadding='5'>";
                    echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Longueur du mot de passe</th></tr>";
                    
                    foreach ($users as $user) {
                        $pwd_status = ($user['pwd_length'] >= 60) ? 'OK' : 'TROP COURT';
                        $pwd_color = ($user['pwd_length'] >= 60) ? 'green' : 'red';
                        
                        echo "<tr>";
                        echo "<td>{$user['id']}</td>";
                        echo "<td>{$user['name']}</td>";
                        echo "<td>{$user['email']}</td>";
                        echo "<td style='color: $pwd_color;'>{$user['pwd_length']} ($pwd_status)</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>Aucun utilisateur trouvé dans la base de données.</p>";
                }
            } catch (PDOException $e) {
                echo "<p style='color: red;'>Erreur lors de la vérification de la table users: " . $e->getMessage() . "</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Erreur de connexion à la base de données: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Configuration de base de données incomplète dans le fichier .env.</p>";
    }
} else {
    echo "<p style='color: red;'>Le fichier .env n'existe pas.</p>";
}

// Vérifier les cookies et les sessions PHP
echo "<h2>Vérification des cookies et sessions PHP</h2>";
echo "Configuration des cookies:<br>";
echo "- cookie.secure: " . ini_get('session.cookie_secure') . "<br>";
echo "- cookie.httponly: " . ini_get('session.cookie_httponly') . "<br>";
echo "- cookie.samesite: " . ini_get('session.cookie_samesite') . "<br>";
echo "- cookie.domain: " . ini_get('session.cookie_domain') . "<br>";
echo "- cookie.path: " . ini_get('session.cookie_path') . "<br>";
echo "- cookie.lifetime: " . ini_get('session.cookie_lifetime') . " secondes<br>";

echo "Configuration des sessions:<br>";
echo "- session.save_handler: " . ini_get('session.save_handler') . "<br>";
echo "- session.save_path: " . ini_get('session.save_path') . "<br>";
echo "- session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . " secondes<br>";

echo "<p>Diagnostic terminé le " . date('Y-m-d H:i:s') . "</p>";
?>
