<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SocialNet') }} - @yield('title', 'Accueil')</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1877f2;
            --secondary-color: #4267B2;
            --light-bg: #f0f2f5;
            --dark-text: #1c1e21;
            --light-text: #65676b;
            --white: #ffffff;
            --shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            padding-top: 70px;
            color: var(--dark-text);
        }
        
        .navbar {
            background-color: var(--white);
            box-shadow: var(--shadow);
        }
        
        .navbar-brand {
            font-weight: 600;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
        }
        
        .navbar-nav .nav-link {
            color: var(--dark-text) !important;
            font-weight: 500;
            position: relative;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--primary-color) !important;
        }
        
        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
        }
        
        .navbar-nav .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .nav-icon {
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #166fe5;
            border-color: #166fe5;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: var(--shadow);
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow);
            border-radius: 10px;
            overflow: hidden;
        }
        
        .dropdown-item {
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .dropdown-item:hover {
            background-color: var(--light-bg);
        }
        
        .profile-header {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            position: relative;
        }
        
        .cover-photo {
            height: 200px;
            background-size: cover;
            background-position: center;
            border-radius: 10px 10px 0 0;
        }
        
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            position: absolute;
            top: 125px;
            left: 20px;
            object-fit: cover;
            box-shadow: var(--shadow);
        }
        
        .profile-info {
            margin-top: 80px;
            margin-left: 180px;
        }
        
        .post-form {
            margin-bottom: 20px;
        }
        
        .comment-section {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 0 0 10px 10px;
        }
        
        .like-button, .comment-button {
            cursor: pointer;
            color: var(--light-text);
            transition: all 0.3s ease;
        }
        
        .like-button:hover, .comment-button:hover {
            color: var(--primary-color);
        }
        
        .like-button.active {
            color: var(--primary-color);
        }
        
        .friend-item {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .friend-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .search-form {
            position: relative;
        }
        
        .search-input {
            border-radius: 20px;
            padding-left: 40px;
            background-color: var(--light-bg);
            border: none;
        }
        
        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="fab fa-facebook-square"></i> SocialNet
            </a>
            
            @auth
            <div class="d-flex d-lg-none ms-auto me-2">
                <a href="{{ route('profile.show', Auth::user()) }}" class="btn btn-sm btn-light rounded-circle me-2">
                    <i class="fas fa-user"></i>
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            @else
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            @endauth
            
            <div class="collapse navbar-collapse" id="navbarNav">
                @auth
                <form class="d-none d-lg-flex me-auto search-form" action="{{ route('users.index') }}" method="GET">
                    <i class="fas fa-search search-icon"></i>
                    <input type="search" class="form-control search-input" name="search" placeholder="Rechercher des amis..." aria-label="Rechercher">
                </form>
                @endauth
                
                <ul class="navbar-nav mx-auto">
                    @auth
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="fas fa-home nav-icon"></i><span class="d-lg-none ms-2">Accueil</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('friends.index') ? 'active' : '' }}" href="{{ route('friends.index') }}">
                            <i class="fas fa-users nav-icon"></i><span class="d-lg-none ms-2">Amis</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('friends.requests') ? 'active' : '' }}" href="{{ route('friends.requests') }}">
                            <i class="fas fa-user-plus nav-icon"></i><span class="d-lg-none ms-2">Demandes d'amitié</span>
                        </a>
                    </li>
                    @if(Route::has('users.index'))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="fas fa-search nav-icon"></i><span class="d-lg-none ms-2">Rechercher</span>
                        </a>
                    </li>
                    @endif
                    @endauth
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    @guest
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-primary text-white px-3 me-2" href="{{ route('login') }}">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-sm btn-outline-primary px-3" href="{{ route('register') }}">Inscription</a>
                    </li>
                    @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://via.placeholder.com/30' }}" class="rounded-circle me-2" width="30" height="30" alt="Profile Picture">
                            <span class="d-none d-lg-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show', Auth::user()) }}">
                                    <i class="fas fa-user-circle me-2"></i> Mon profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-cog me-2"></i> Paramètres du profil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit-password') }}">
                                    <i class="fas fa-key me-2"></i> Changer de mot de passe
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="py-4">
        <div class="container">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Configurer le jeton CSRF pour toutes les requêtes AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Fermeture automatique des alertes après 5 secondes
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Gestion des likes avec AJAX
            $('.action-btn.btn-like').on('click', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                var url = form.attr('action');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        // Mettre à jour l'interface utilisateur
                        if (response.liked) {
                            form.find('.action-btn.btn-like').addClass('active');
                        } else {
                            form.find('.action-btn.btn-like').removeClass('active');
                        }
                        
                        // Mettre à jour le compteur de likes
                        var postCard = form.closest('.post-card');
                        var likesCounter = postCard.find('.fa-thumbs-up').parent();
                        
                        if (response.likesCount > 0) {
                            if (likesCounter.length) {
                                likesCounter.html('<i class="fas fa-thumbs-up text-primary"></i> ' + response.likesCount);
                            } else {
                                postCard.find('.post-content .d-flex.justify-content-between .text-muted').first().html('<i class="fas fa-thumbs-up text-primary"></i> ' + response.likesCount);
                            }
                        } else {
                            likesCounter.empty();
                        }
                    },
                    error: function(xhr) {
                        console.error('Erreur lors du traitement du like', xhr);
                        
                        // En cas d'erreur, soumettre le formulaire normalement
                        form.submit();
                    }
                });
            });
            
            // Amélioration des soumissions de formulaire pour les publications
            $('#post-form').on('submit', function() {
                // Désactiver le bouton de soumission pour éviter les doubles soumissions
                $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Publication en cours...');
            });
            
            // Amélioration des demandes d'amitié
            $('.send-friend-request').on('click', function() {
                var btn = $(this);
                var form = btn.closest('form');
                var url = form.attr('action');
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Envoi...');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function(response) {
                        // Remplacer le bouton par un badge "Demande envoyée"
                        btn.replaceWith('<span class="badge-pending"><i class="fas fa-clock me-1"></i> Demande en attente</span>');
                    },
                    error: function(xhr) {
                        console.error('Erreur lors de l\'envoi de la demande d\'amitié', xhr);
                        btn.prop('disabled', false).html('<i class="fas fa-user-plus me-1"></i> Ajouter');
                        
                        // En cas d'erreur, soumettre le formulaire normalement
                        form.submit();
                    }
                });
                
                return false;
            });
            
            // Aperçu de l'image à télécharger
            $('#image').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        if ($('#image-preview').length) {
                            $('#image-preview').attr('src', e.target.result).show();
                        } else {
                            $('<div class="mt-3"><img id="image-preview" src="' + e.target.result + '" class="img-fluid rounded" style="max-height: 200px;"></div>').insertAfter($(this).closest('.mb-3'));
                        }
                    }.bind(this);
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
    @yield('scripts')
</body>
</html> 