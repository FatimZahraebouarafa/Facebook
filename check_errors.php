<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Vérification des erreurs</h1>";

// Vérifier le fichier de logs d'erreur PHP
echo "<h2>Logs d'erreur PHP</h2>";
$error_log = ini_get('error_log');
if ($error_log) {
    echo "Fichier de logs d'erreur PHP: $error_log<br>";
    if (file_exists($error_log) && is_readable($error_log)) {
        $log_content = file_get_contents($error_log);
        $log_lines = explode("\n", $log_content);
        $last_lines = array_slice($log_lines, max(0, count($log_lines) - 50));
        
        echo "<h3>Dernières erreurs (50 dernières lignes):</h3>";
        echo "<pre>";
        foreach ($last_lines as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo "</pre>";
    } else {
        echo "Le fichier de logs d'erreur PHP n'existe pas ou n'est pas lisible.<br>";
    }
} else {
    echo "Le fichier de logs d'erreur PHP n'est pas configuré.<br>";
}

// Vérifier les logs Laravel si disponibles
echo "<h2>Logs Laravel</h2>";
$laravel_log = '../storage/logs/laravel.log';
if (file_exists($laravel_log) && is_readable($laravel_log)) {
    $log_content = file_get_contents($laravel_log);
    $log_size = strlen($log_content);
    
    echo "Taille du fichier de logs Laravel: " . round($log_size / 1024, 2) . " Ko<br>";
    
    // Extraire les dernières erreurs
    $last_part = substr($log_content, max(0, $log_size - 50000));
    $pattern = '/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*?(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s';
    
    if (preg_match_all($pattern, $last_part, $matches)) {
        $errors = array_slice($matches[0], -10);
        
        echo "<h3>Dernières erreurs Laravel (10 dernières):</h3>";
        echo "<pre>";
        foreach ($errors as $error) {
            echo htmlspecialchars($error) . "\n----------------------------------------\n";
        }
        echo "</pre>";
    } else {
        echo "Aucune erreur trouvée dans le format attendu.<br>";
    }
} else {
    echo "Le fichier de logs Laravel n'existe pas ou n'est pas lisible.<br>";
}

// Vérifier si les routes sont correctement chargées
echo "<h2>Vérification des routes</h2>";

// Fonction pour vérifier si une URL est accessible
function check_url($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $http_code;
}

// Détecter l'URL de base
$host = $_SERVER['HTTP_HOST'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$base_url = "http://$host$script_name";
$base_url = rtrim($base_url, '/');

$routes = [
    '/' => 'Page d\'accueil',
    '/login' => 'Page de connexion',
    '/register' => 'Page d\'inscription',
];

echo "URL de base détectée: $base_url<br>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Route</th><th>Description</th><th>Statut</th></tr>";

foreach ($routes as $route => $description) {
    $url = $base_url . $route;
    $status = check_url($url);
    $status_text = ($status >= 200 && $status < 400) ? 
        "<span style='color:green'>OK ($status)</span>" : 
        "<span style='color:red'>Erreur ($status)</span>";
    
    echo "<tr><td>$route</td><td>$description</td><td>$status_text</td></tr>";
}

echo "</table>";

// Afficher des informations sur la configuration PHP
echo "<h2>Configuration PHP</h2>";
echo "Version PHP: " . phpversion() . "<br>";
echo "Extensions chargées: " . implode(', ', get_loaded_extensions()) . "<br>";
echo "Limite de mémoire: " . ini_get('memory_limit') . "<br>";
echo "Temps maximum d'exécution: " . ini_get('max_execution_time') . " secondes<br>";
echo "Upload max: " . ini_get('upload_max_filesize') . "<br>";
echo "Post max: " . ini_get('post_max_size') . "<br>";

// Vérifier le système de fichiers
echo "<h2>Système de fichiers</h2>";
$important_files = [
    '../.env',
    '../artisan',
    '../composer.json',
    '../routes/web.php',
    '../app/Http/Controllers/UserController.php',
    '../app/Http/Controllers/PostController.php',
    '../app/Models/User.php',
];

foreach ($important_files as $file) {
    if (file_exists($file)) {
        echo "Fichier $file existe ";
        if (is_readable($file)) {
            echo "et est lisible.<br>";
        } else {
            echo "mais n'est PAS lisible.<br>";
        }
    } else {
        echo "Fichier $file n'existe PAS.<br>";
    }
}

echo "<p>Cette page a été générée le " . date('Y-m-d H:i:s') . "</p>";
?> 