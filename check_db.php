<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Test de connexion à la base de données</h1>";

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
    
    echo "<h2>Paramètres de connexion</h2>";
    echo "DB_CONNECTION: " . ($db_params['DB_CONNECTION'] ?? 'non défini') . "<br>";
    echo "DB_HOST: " . ($db_params['DB_HOST'] ?? 'non défini') . "<br>";
    echo "DB_PORT: " . ($db_params['DB_PORT'] ?? 'non défini') . "<br>";
    echo "DB_DATABASE: " . ($db_params['DB_DATABASE'] ?? 'non défini') . "<br>";
    echo "DB_USERNAME: " . ($db_params['DB_USERNAME'] ?? 'non défini') . "<br>";
    echo "DB_PASSWORD: " . (isset($db_params['DB_PASSWORD']) ? '********' : 'non défini') . "<br>";
    
    // Tester la connexion
    echo "<h2>Test de connexion</h2>";
    
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
            
            echo "<div style='color: green; font-weight: bold;'>Connexion réussie à la base de données!</div>";
            
            // Vérifier les tables
            echo "<h3>Tables existantes:</h3>";
            
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($tables) > 0) {
                echo "<ul>";
                foreach ($tables as $table) {
                    echo "<li>$table</li>";
                }
                echo "</ul>";
            } else {
                echo "<div style='color: orange;'>Aucune table trouvée dans la base de données.</div>";
            }
            
            // Vérifier la table users
            $required_tables = ['users', 'posts', 'comments', 'likes', 'friendships', 'sessions'];
            
            echo "<h3>Vérification des tables requises:</h3>";
            foreach ($required_tables as $table) {
                if (in_array($table, $tables)) {
                    echo "<div style='color: green;'>Table '$table' existe.</div>";
                } else {
                    echo "<div style='color: red;'>Table '$table' n'existe pas!</div>";
                }
            }
            
        } catch (PDOException $e) {
            echo "<div style='color: red; font-weight: bold;'>Erreur de connexion à la base de données:</div>";
            echo "<pre>{$e->getMessage()}</pre>";
        }
    } else {
        echo "<div style='color: red;'>Paramètres de connexion incomplets!</div>";
    }
} else {
    echo "<div style='color: red; font-weight: bold;'>Fichier .env introuvable!</div>";
}
?> 