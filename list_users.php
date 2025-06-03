<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "Liste des utilisateurs enregistrés dans la base de données:\n";
echo "------------------------------------------------------\n";

try {
    $users = User::all();
    
    if ($users->count() > 0) {
        echo "ID\tNom\t\t\tEmail\t\t\tDate d'inscription\n";
        echo "------------------------------------------------------\n";
        
        foreach ($users as $user) {
            echo "{$user->id}\t{$user->name}\t\t{$user->email}\t\t{$user->created_at}\n";
        }
        
        echo "\nNombre total d'utilisateurs: {$users->count()}\n";
    } else {
        echo "Aucun utilisateur n'est enregistré dans la base de données.\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
} 