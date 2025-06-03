<?php

require __DIR__ . '/../vendor/autoload.php';

// Charger les variables d'environnement depuis le fichier .env
$dotenv = \Illuminate\Support\Env::create(__DIR__ . '/../');

// Boostrap l'application Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Tester les méthodes problématiques
echo "Test des méthodes des modèles Laravel\n";

// Tester User::friends()
$user = new \App\Models\User();
echo "La méthode User::friends() existe-t-elle ? " . (method_exists($user, 'friends') ? 'Oui' : 'Non') . "\n";

// Tester User::isFriendWith()
echo "La méthode User::isFriendWith() existe-t-elle ? " . (method_exists($user, 'isFriendWith') ? 'Oui' : 'Non') . "\n";

// Tester User::save()
echo "La méthode User::save() existe-t-elle ? " . (method_exists($user, 'save') ? 'Oui' : 'Non') . "\n";

// Tester Post::isLikedBy()
$post = new \App\Models\Post();
echo "La méthode Post::isLikedBy() existe-t-elle ? " . (method_exists($post, 'isLikedBy') ? 'Oui' : 'Non') . "\n";

// Si on arrive ici, le script s'est exécuté avec succès
echo "Tests terminés !\n"; 