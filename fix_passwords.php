<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Vérification et correction des mots de passe utilisateurs</h1>";

// Charger les paramètres de connexion du fichier .env
if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $db_params = [];
    
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $db_params[$key] = $value;
        }
    }
    
    // Tester la connexion
    if (isset($db_params['DB_CONNECTION']) && 
        isset($db_params['DB_HOST']) && 
        isset($db_params['DB_PORT']) && 
        isset($db_params['DB_DATABASE']) && 
        isset($db_params['DB_USERNAME']) && 
        isset($db_params['DB_PASSWORD'])) {
        
        try {
            $dsn = "{$db_params['DB_CONNECTION']}:host={$db_params['DB_HOST']};port={$db_params['DB_PORT']};dbname={$db_params['DB_DATABASE']}";
            $pdo = new PDO($dsn, $db_params['DB_USERNAME'], $db_params['DB_PASSWORD']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<div style='color: green;'>Connexion réussie à la base de données!</div>";
            
            // Vérifier la table users
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                echo "<div style='color: green;'>Table 'users' trouvée.</div>";
                
                // Vérifier les utilisateurs et leurs mots de passe
                $stmt = $pdo->query("SELECT id, name, email, password FROM users");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($users) > 0) {
                    echo "<div>Nombre d'utilisateurs trouvés: " . count($users) . "</div>";
                    
                    // Vérifier si les mots de passe sont au format bcrypt
                    $bcrypt_users = 0;
                    $non_bcrypt_users = 0;
                    $users_to_fix = [];
                    
                    foreach ($users as $user) {
                        if (strpos($user['password'], '$2y$') === 0) {
                            $bcrypt_users++;
                        } else {
                            $non_bcrypt_users++;
                            $users_to_fix[] = $user;
                        }
                    }
                    
                    echo "<div>Utilisateurs avec mot de passe bcrypt: $bcrypt_users</div>";
                    echo "<div>Utilisateurs avec mot de passe non bcrypt: $non_bcrypt_users</div>";
                    
                    // Corriger les mots de passe non bcrypt
                    if ($non_bcrypt_users > 0) {
                        echo "<h3>Correction des mots de passe non bcrypt</h3>";
                        
                        $default_password = "password123"; // Mot de passe par défaut
                        $hashed_password = password_hash($default_password, PASSWORD_BCRYPT);
                        
                        foreach ($users_to_fix as $user) {
                            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                            if ($stmt->execute([$hashed_password, $user['id']])) {
                                echo "<div style='color: green;'>Mot de passe corrigé pour {$user['email']} (ID: {$user['id']})</div>";
                                echo "<div>Nouveau mot de passe: $default_password</div>";
                            } else {
                                echo "<div style='color: red;'>Impossible de corriger le mot de passe pour {$user['email']}</div>";
                            }
                        }
                    }
                } else {
                    echo "<div style='color: orange;'>Aucun utilisateur trouvé dans la base de données.</div>";
                }
            } else {
                echo "<div style='color: red;'>Table 'users' non trouvée!</div>";
            }
            
        } catch (PDOException $e) {
            echo "<div style='color: red;'>Erreur de connexion à la base de données: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div style='color: red;'>Paramètres de connexion incomplets!</div>";
    }
} else {
    echo "<div style='color: red;'>Fichier .env introuvable!</div>";
}

echo "<p>Script terminé à " . date('Y-m-d H:i:s') . "</p>";
?> 