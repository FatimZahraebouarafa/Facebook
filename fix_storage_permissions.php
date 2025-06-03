<?php
// Afficher les erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Correction des permissions du répertoire storage</h1>";

// Fonction pour vérifier et corriger les permissions d'un répertoire
function check_and_fix_permissions($path) {
    echo "<h3>Vérification de $path</h3>";
    
    if (!file_exists($path)) {
        echo "<div style='color: red;'>Le répertoire $path n'existe pas!</div>";
        
        // Tenter de créer le répertoire
        if (@mkdir($path, 0755, true)) {
            echo "<div style='color: green;'>Répertoire $path créé avec succès.</div>";
        } else {
            echo "<div style='color: red;'>Impossible de créer le répertoire $path.</div>";
            return false;
        }
    }
    
    if (!is_writable($path)) {
        echo "<div style='color: red;'>Le répertoire $path n'est pas accessible en écriture!</div>";
        
        // Tenter de modifier les permissions
        if (@chmod($path, 0755)) {
            echo "<div style='color: green;'>Permissions du répertoire $path corrigées avec succès.</div>";
        } else {
            echo "<div style='color: red;'>Impossible de modifier les permissions du répertoire $path.</div>";
            return false;
        }
    } else {
        echo "<div style='color: green;'>Le répertoire $path est accessible en écriture.</div>";
    }
    
    return true;
}

// Répertoires à vérifier
$directories = [
    'storage',
    'storage/app',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

// Vérifier et corriger les permissions
foreach ($directories as $directory) {
    check_and_fix_permissions($directory);
}

// Créer un fichier de test pour vérifier les permissions
$test_file = 'storage/logs/test_permissions.txt';
echo "<h3>Test d'écriture dans les logs</h3>";

if (file_put_contents($test_file, 'Test de permissions ' . date('Y-m-d H:i:s'))) {
    echo "<div style='color: green;'>Fichier de test créé avec succès dans le répertoire des logs.</div>";
    
    // Supprimer le fichier de test
    if (unlink($test_file)) {
        echo "<div style='color: green;'>Fichier de test supprimé avec succès.</div>";
    } else {
        echo "<div style='color: orange;'>Impossible de supprimer le fichier de test.</div>";
    }
} else {
    echo "<div style='color: red;'>Impossible de créer un fichier de test dans le répertoire des logs.</div>";
}

// Créer les liens symboliques si nécessaire
echo "<h3>Création du lien symbolique pour le répertoire public</h3>";
if (file_exists('public') && file_exists('storage/app/public')) {
    if (!file_exists('public/storage')) {
        if (@symlink('../storage/app/public', 'public/storage')) {
            echo "<div style='color: green;'>Lien symbolique créé avec succès.</div>";
        } else {
            echo "<div style='color: red;'>Impossible de créer le lien symbolique.</div>";
        }
    } else {
        echo "<div style='color: green;'>Le lien symbolique existe déjà.</div>";
    }
} else {
    echo "<div style='color: red;'>Les répertoires public ou storage/app/public n'existent pas.</div>";
}

echo "<p>Script terminé à " . date('Y-m-d H:i:s') . "</p>";
?> 