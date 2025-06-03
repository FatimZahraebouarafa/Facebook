<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Nettoyage du cache et vérification de la configuration Laravel</h1>";

// Définir les chemins des répertoires de cache
$cache_directories = [
    '../bootstrap/cache/*.php',
    '../storage/framework/cache/data/*',
    '../storage/framework/views/*.php',
    '../storage/framework/sessions/*',
];

// Nettoyer les fichiers de cache
echo "<h2>Nettoyage des fichiers de cache</h2>";
foreach ($cache_directories as $pattern) {
    $files = glob($pattern);
    
    if (!empty($files)) {
        echo "Suppression des fichiers dans $pattern:<br>";
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (@unlink($file)) {
                    $count++;
                }
            }
        }
        
        echo "<div style='color: green;'>$count fichiers supprimés avec succès.</div>";
    } else {
        echo "Aucun fichier trouvé dans $pattern.<br>";
    }
}

// Créer les fichiers de cache nécessaires
$empty_files = [
    '../storage/framework/sessions/.gitignore',
    '../storage/framework/views/.gitignore',
    '../storage/framework/cache/.gitignore',
    '../storage/framework/cache/data/.gitignore',
];

foreach ($empty_files as $file) {
    if (!file_exists(dirname($file))) {
        @mkdir(dirname($file), 0755, true);
    }
    
    if (!file_exists($file)) {
        @file_put_contents($file, '*' . PHP_EOL . '!.gitignore' . PHP_EOL);
    }
}

// Vérifier la configuration .env
echo "<h2>Vérification du fichier .env</h2>";
if (file_exists('../.env')) {
    echo "Le fichier .env existe.<br>";
    
    // Vérifier les clés importantes
    $env_content = file_get_contents('../.env');
    $required_keys = [
        'APP_NAME',
        'APP_ENV',
        'APP_KEY',
        'APP_DEBUG',
        'APP_URL',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
        'SESSION_DRIVER',
        'CACHE_DRIVER',
    ];
    
    $missing_keys = [];
    
    foreach ($required_keys as $key) {
        if (strpos($env_content, "$key=") === false) {
            $missing_keys[] = $key;
        }
    }
    
    if (empty($missing_keys)) {
        echo "<div style='color: green;'>Toutes les clés de configuration requises sont présentes.</div>";
    } else {
        echo "<div style='color: red;'>Clés de configuration manquantes: " . implode(', ', $missing_keys) . "</div>";
    }
    
    // Vérifier si APP_KEY est défini
    preg_match('/APP_KEY=(.*)/', $env_content, $matches);
    if (isset($matches[1]) && !empty(trim($matches[1])) && trim($matches[1]) !== 'base64:') {
        echo "<div style='color: green;'>APP_KEY est correctement défini.</div>";
    } else {
        echo "<div style='color: red;'>APP_KEY est vide ou mal défini. Cela peut causer des problèmes de sécurité et de session.</div>";
        
        // Générer une nouvelle clé
        echo "<div>Tentative de génération d'une nouvelle APP_KEY:</div>";
        $key = 'base64:' . base64_encode(random_bytes(32));
        echo "Nouvelle clé générée: $key<br>";
        
        // Remplacer la clé dans le fichier .env
        $new_env_content = preg_replace('/APP_KEY=.*/', "APP_KEY=$key", $env_content);
        if (@file_put_contents('../.env', $new_env_content)) {
            echo "<div style='color: green;'>Nouvelle APP_KEY écrite dans le fichier .env avec succès.</div>";
        } else {
            echo "<div style='color: red;'>Impossible d'écrire la nouvelle APP_KEY dans le fichier .env.</div>";
        }
    }
} else {
    echo "<div style='color: red;'>Le fichier .env n'existe pas!</div>";
}

// Vérifier l'espace disque
echo "<h2>Vérification de l'espace disque</h2>";
$disk_free = disk_free_space('/');
$disk_total = disk_total_space('/');
$disk_used = $disk_total - $disk_free;
$percent_used = round(($disk_used / $disk_total) * 100, 2);

echo "Espace disque total: " . round($disk_total / 1024 / 1024 / 1024, 2) . " Go<br>";
echo "Espace disque libre: " . round($disk_free / 1024 / 1024 / 1024, 2) . " Go<br>";
echo "Espace disque utilisé: " . round($disk_used / 1024 / 1024 / 1024, 2) . " Go ($percent_used%)<br>";

if ($percent_used > 90) {
    echo "<div style='color: red;'>Attention: l'espace disque est presque plein!</div>";
} else {
    echo "<div style='color: green;'>Espace disque suffisant.</div>";
}

echo "<p>Script terminé à " . date('Y-m-d H:i:s') . "</p>";
?> 