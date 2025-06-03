<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Diagnostic et correction des problèmes d'authentification</h1>";

// Fonction pour corriger les permissions d'un répertoire
function fix_directory_permissions($path, $recursive = false) {
    if (!file_exists($path)) {
        echo "<div style='color: red;'>Le répertoire $path n'existe pas! Tentative de création...</div>";
        if (@mkdir($path, 0755, true)) {
            echo "<div style='color: green;'>Répertoire $path créé avec succès.</div>";
        } else {
            echo "<div style='color: red;'>Impossible de créer le répertoire $path.</div>";
            return false;
        }
    }
    
    if (!is_writable($path)) {
        echo "<div style='color: red;'>Le répertoire $path n'est pas accessible en écriture! Tentative de correction...</div>";
        if (@chmod($path, 0755)) {
            echo "<div style='color: green;'>Permissions du répertoire $path corrigées avec succès.</div>";
        } else {
            echo "<div style='color: red;'>Impossible de modifier les permissions du répertoire $path.</div>";
            return false;
        }
    } else {
        echo "<div style='color: green;'>Le répertoire $path est accessible en écriture.</div>";
    }
    
    // Correction récursive si demandé
    if ($recursive && is_dir($path)) {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            
            $item_path = $path . '/' . $item;
            if (is_dir($item_path)) {
                fix_directory_permissions($item_path, true);
            } else if (is_file($item_path)) {
                if (!is_writable($item_path)) {
                    if (@chmod($item_path, 0644)) {
                        echo "<div style='color: green;'>Permissions du fichier $item_path corrigées avec succès.</div>";
                    } else {
                        echo "<div style='color: red;'>Impossible de modifier les permissions du fichier $item_path.</div>";
                    }
                }
            }
        }
    }
    
    return true;
}

// Vérifier et corriger les répertoires critiques pour l'authentification
echo "<h2>Vérification et correction des répertoires critiques</h2>";

$critical_directories = [
    '../storage/framework/sessions',
    '../storage/framework/views',
    '../storage/framework/cache',
    '../storage/logs',
    '../bootstrap/cache'
];

foreach ($critical_directories as $directory) {
    echo "<h3>Vérification de $directory</h3>";
    fix_directory_permissions($directory, true);
}

// Nettoyer les fichiers de session obsolètes
echo "<h2>Nettoyage des sessions obsolètes</h2>";

$session_dir = '../storage/framework/sessions';
if (is_dir($session_dir)) {
    $count = 0;
    $files = scandir($session_dir);
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..' || $file == '.gitignore') continue;
        
        $file_path = $session_dir . '/' . $file;
        if (is_file($file_path)) {
            // Supprimer les fichiers de plus de 24h
            if (time() - filemtime($file_path) > 86400) {
                if (@unlink($file_path)) {
                    $count++;
                }
            }
        }
    }
    
    echo "<div>$count fichiers de session obsolètes supprimés.</div>";
} else {
    echo "<div style='color: red;'>Le répertoire des sessions n'existe pas!</div>";
}

// Vérifier et réparer la configuration APP_KEY
echo "<h2>Vérification de la clé d'application (APP_KEY)</h2>";

if (file_exists('../.env')) {
    $env_content = file_get_contents('../.env');
    
    // Vérifier si APP_KEY est défini correctement
    preg_match('/APP_KEY=(.*)/', $env_content, $matches);
    
    if (isset($matches[1]) && !empty(trim($matches[1])) && trim($matches[1]) !== 'base64:' && strlen(trim($matches[1])) > 30) {
        echo "<div style='color: green;'>APP_KEY est correctement défini.</div>";
    } else {
        echo "<div style='color: red;'>APP_KEY est vide ou mal défini. Génération d'une nouvelle clé...</div>";
        
        // Générer une nouvelle clé d'application
        $key = 'base64:' . base64_encode(random_bytes(32));
        
        if (isset($matches[0])) {
            // Remplacer la clé existante
            $new_env_content = str_replace($matches[0], "APP_KEY=$key", $env_content);
        } else {
            // Ajouter la clé si elle n'existe pas
            $new_env_content = $env_content . "\nAPP_KEY=$key\n";
        }
        
        if (@file_put_contents('../.env', $new_env_content)) {
            echo "<div style='color: green;'>Nouvelle APP_KEY générée et enregistrée avec succès: $key</div>";
        } else {
            echo "<div style='color: red;'>Impossible d'écrire la nouvelle APP_KEY dans le fichier .env.</div>";
        }
    }
} else {
    echo "<div style='color: red;'>Le fichier .env n'existe pas!</div>";
}

// Vérifier et corriger la configuration des sessions
echo "<h2>Vérification de la configuration des sessions</h2>";

if (file_exists('../.env')) {
    $env_content = file_get_contents('../.env');
    
    // Vérifier le driver de session
    preg_match('/SESSION_DRIVER=(.*)/', $env_content, $session_driver);
    
    if (isset($session_driver[1]) && !empty(trim($session_driver[1]))) {
        echo "<div>SESSION_DRIVER actuel: " . $session_driver[1] . "</div>";
        
        // Vérifier si le driver est supporté
        $supported_drivers = ['file', 'cookie', 'database', 'redis', 'memcached', 'array'];
        
        if (!in_array(trim($session_driver[1]), $supported_drivers)) {
            echo "<div style='color: red;'>Driver de session non supporté. Modification pour 'file'...</div>";
            
            $new_env_content = str_replace($session_driver[0], "SESSION_DRIVER=file", $env_content);
            
            if (@file_put_contents('../.env', $new_env_content)) {
                echo "<div style='color: green;'>SESSION_DRIVER modifié avec succès en 'file'.</div>";
            } else {
                echo "<div style='color: red;'>Impossible de modifier SESSION_DRIVER dans le fichier .env.</div>";
            }
        } else {
            echo "<div style='color: green;'>Le driver de session est supporté.</div>";
        }
    } else {
        echo "<div style='color: red;'>SESSION_DRIVER non défini. Ajout de la configuration...</div>";
        
        $new_env_content = $env_content . "\nSESSION_DRIVER=file\n";
        
        if (@file_put_contents('../.env', $new_env_content)) {
            echo "<div style='color: green;'>SESSION_DRIVER ajouté avec succès: file</div>";
        } else {
            echo "<div style='color: red;'>Impossible d'ajouter SESSION_DRIVER dans le fichier .env.</div>";
        }
    }
} else {
    echo "<div style='color: red;'>Le fichier .env n'existe pas!</div>";
}

// Vérifier les configurations PHP
echo "<h2>Vérification des configurations PHP</h2>";

$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (empty($missing_extensions)) {
    echo "<div style='color: green;'>Toutes les extensions PHP requises sont chargées.</div>";
} else {
    echo "<div style='color: red;'>Extensions PHP manquantes: " . implode(', ', $missing_extensions) . "</div>";
    echo "<div>Vous devez installer ces extensions pour que l'authentification fonctionne correctement.</div>";
}

// Vérifier la fonction de hachage par défaut
echo "<h2>Vérification de la fonction de hachage</h2>";

if (defined('PASSWORD_BCRYPT') && PASSWORD_DEFAULT === PASSWORD_BCRYPT) {
    echo "<div style='color: green;'>La fonction de hachage par défaut est BCRYPT, ce qui est correct.</div>";
} else {
    echo "<div style='color: orange;'>La fonction de hachage par défaut n'est pas BCRYPT. Cela pourrait causer des problèmes d'authentification.</div>";
}

// Création d'un fichier de test de session
echo "<h2>Test d'écriture de session</h2>";

$session_test_file = '../storage/framework/sessions/test_' . time() . '.txt';
if (@file_put_contents($session_test_file, 'Test de session ' . date('Y-m-d H:i:s'))) {
    echo "<div style='color: green;'>Test d'écriture de session réussi.</div>";
    @unlink($session_test_file);
} else {
    echo "<div style='color: red;'>Impossible d'écrire dans le répertoire des sessions!</div>";
}

// Nettoyage du cache
echo "<h2>Nettoyage du cache de l'application</h2>";

$cache_files = [
    '../bootstrap/cache/config.php',
    '../bootstrap/cache/routes.php',
    '../bootstrap/cache/services.php'
];

foreach ($cache_files as $file) {
    if (file_exists($file)) {
        if (@unlink($file)) {
            echo "<div style='color: green;'>Fichier de cache $file supprimé avec succès.</div>";
        } else {
            echo "<div style='color: red;'>Impossible de supprimer le fichier de cache $file.</div>";
        }
    }
}

echo "<p>Script terminé à " . date('Y-m-d H:i:s') . "</p>";
echo "<p>Après avoir exécuté ce script, veuillez redémarrer votre serveur web pour appliquer les modifications.</p>";
?>
