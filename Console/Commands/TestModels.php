<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Post;

class TestModels extends Command
{
    protected $signature = 'test:models';
    protected $description = 'Test si les méthodes des modèles existent';

    public function handle()
    {
        $this->info('Test des méthodes des modèles');

        // Tester User::friends()
        $user = new User();
        $this->info('La méthode User::friends() existe-t-elle ? ' . (method_exists($user, 'friends') ? 'Oui' : 'Non'));

        // Tester User::isFriendWith()
        $this->info('La méthode User::isFriendWith() existe-t-elle ? ' . (method_exists($user, 'isFriendWith') ? 'Oui' : 'Non'));

        // Tester User::save()
        $this->info('La méthode User::save() existe-t-elle ? ' . (method_exists($user, 'save') ? 'Oui' : 'Non'));

        // Tester Post::isLikedBy()
        $post = new Post();
        $this->info('La méthode Post::isLikedBy() existe-t-elle ? ' . (method_exists($post, 'isLikedBy') ? 'Oui' : 'Non'));

        $this->info('Tests terminés !');
        
        return Command::SUCCESS;
    }
} 