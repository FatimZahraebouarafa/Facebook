<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Générer des données aléatoires pour le test
$name = 'Test User ' . Str::random(5);
$email = 'test_' . Str::random(5) . '@example.com';
$password = 'password123';

echo "Tentative d'enregistrement d'un nouvel utilisateur :\n";
echo "Nom: $name\n";
echo "Email: $email\n";
echo "Mot de passe: $password\n";
echo "------------------------------------------------------\n";

try {
    // Vérifier si l'email existe déjà
    $existingUser = User::where('email', $email)->first();
    if ($existingUser) {
        echo "ERREUR: Un utilisateur avec cet email existe déjà.\n";
        exit;
    }
    
    // Créer le nouvel utilisateur
    $user = User::create([
        'name' => $name,
        'email' => $email,
        'password' => Hash::make($password),
    ]);
    
    if ($user) {
        echo "SUCCÈS: Utilisateur créé avec l'ID: {$user->id}\n";
        
        // Vérifier que le mot de passe est correctement haché
        $passwordInfo = password_get_info($user->password);
        echo "Vérification du mot de passe: " . 
             (($passwordInfo['algo'] === PASSWORD_BCRYPT) ? "BCRYPT (correct)" : "NON-BCRYPT (problème)") . "\n";
             
        // Compter le nombre total d'utilisateurs
        $totalUsers = User::count();
        echo "Nombre total d'utilisateurs dans la base de données: $totalUsers\n";
    } else {
        echo "ERREUR: Échec de la création de l'utilisateur pour une raison inconnue.\n";
    }
} catch (\Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
} 