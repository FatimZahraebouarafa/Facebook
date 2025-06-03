@extends('layouts.app')

@section('title', 'Fil d\'actualité')

@section('styles')
<style>
    /* Styles personnalisés pour la page d'accueil */
    body {
        background-color: #f0f2f5;
    }
    
    .profile-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        border: none;
        overflow: hidden;
    }
    
    .profile-header {
        position: relative;
        height: 80px;
        background-color: #4267B2;
    }
    
    .profile-img {
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 4px solid white;
        object-fit: cover;
        background-color: #fff;
    }
    
    .profile-info {
        margin-top: 45px;
        text-align: center;
        padding: 10px;
    }
    
    .nav-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border: none;
        overflow: hidden;
    }
    
    .nav-link-custom {
        padding: 12px 15px;
        transition: background-color 0.3s;
        border-left: 3px solid transparent;
    }
    
    .nav-link-custom:hover {
        background-color: #f0f2f5;
        border-left: 3px solid #4267B2;
    }
    
    .nav-link-custom.active {
        background-color: #e7f3ff;
        color: #1877f2;
        border-left: 3px solid #1877f2;
        font-weight: 500;
    }
    
    .post-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        border: none;
        overflow: hidden;
    }
    
    .post-header {
        padding: 12px 15px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f0f2f5;
    }
    
    .post-content {
        padding: 15px;
    }
    
    .post-img {
        width: 100%;
        border-radius: 5px;
        margin-top: 10px;
    }
    
    .action-buttons {
        display: flex;
        border-top: 1px solid #f0f2f5;
        border-bottom: 1px solid #f0f2f5;
    }
    
    .action-btn {
        flex: 1;
        text-align: center;
        padding: 8px;
        background: transparent;
        border: none;
        border-radius: 0;
        transition: background-color 0.2s;
    }
    
    .action-btn:hover {
        background-color: #f0f2f5;
    }
    
    .comment-section {
        padding: 10px 15px;
        max-height: 300px;
        overflow-y: auto;
    }
    
    .comment-bubble {
        background-color: #f0f2f5;
        border-radius: 18px;
        padding: 8px 12px;
        margin-bottom: 8px;
        position: relative;
    }
    
    .comment-form {
        padding: 10px 15px;
        background-color: #f7f7f7;
    }
    
    .suggestion-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        border: none;
        overflow: hidden;
    }
    
    .create-post-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        border: none;
        overflow: hidden;
    }
    
    .create-post-header {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f2f5;
    }
    
    .btn-post {
        background-color: #1877f2;
        color: white;
        border-radius: 5px;
        font-weight: 500;
    }
    
    .btn-post:hover {
        background-color: #166fe5;
    }
    
    .btn-like {
        color: #1877f2;
    }
    
    .btn-like.active {
        color: white;
        background-color: #1877f2;
    }
    
    .btn-comment {
        color: #65676b;
    }
    
    .alert-custom {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border: none;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-4">
        <!-- Profil utilisateur -->
        <div class="card profile-card mb-4">
            @if(Auth::check())
                <div class="profile-header">
                    <img class="profile-img" src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://via.placeholder.com/80' }}" alt="Photo de profil">
                </div>
                <div class="profile-info">
                    <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted small mb-3">{{ Auth::user()->email }}</p>
                    <p class="small mb-3">{{ Auth::user()->bio ?? 'Aucune bio' }}</p>
                    <a href="{{ route('profile.show', Auth::user()) }}" class="btn btn-sm btn-primary rounded-pill px-4">Voir mon profil</a>
                </div>
            @else
                <div class="profile-header">
                    <img class="profile-img" src="https://via.placeholder.com/80" alt="Photo de profil">
                </div>
                <div class="profile-info">
                    <h5 class="mb-1">Invité</h5>
                    <p class="text-muted small mb-3">Non connecté</p>
                    <p class="small mb-3">Connectez-vous pour accéder à toutes les fonctionnalités.</p>
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary rounded-pill px-4">Connexion</a>
                </div>
            @endif
        </div>
        
        <!-- Navigation -->
        <div class="card nav-card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Navigation</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('home') }}" class="list-group-item list-group-item-action nav-link-custom active">
                    <i class="fas fa-newspaper me-2"></i> Fil d'actualité
                </a>
                <a href="{{ route('friends.index') }}" class="list-group-item list-group-item-action nav-link-custom">
                    <i class="fas fa-users me-2"></i> Mes amis
                </a>
                <a href="{{ route('friends.requests') }}" class="list-group-item list-group-item-action nav-link-custom">
                    <i class="fas fa-user-plus me-2"></i> Demandes d'amitié
                </a>
                @if(Route::has('users.index'))
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action nav-link-custom">
                    <i class="fas fa-search me-2"></i> Rechercher des amis
                </a>
                @endif
            </div>
        </div>
        
        <!-- Suggestions d'amis (version mobile) -->
        <div class="card suggestion-card d-md-none mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Suggestions d'amis</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Retrouvez des amis pour enrichir votre expérience.</p>
                @if(Route::has('users.index'))
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-search me-2"></i> Rechercher des amis
                </a>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 col-md-8">
        <!-- Messages d'alerte -->
        @if(session('error'))
        <div class="alert alert-danger alert-custom mb-4">
            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
        </div>
        @endif
        
        @if(session('success'))
        <div class="alert alert-success alert-custom mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
        @endif
        
        <!-- Formulaire de publication -->
        @if(Auth::check())
        <div class="card create-post-card mb-4">
            <div class="create-post-header">
                <h6 class="mb-0">Créer une publication</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" id="post-form">
                    @csrf
                    <div class="d-flex mb-3">
                        <img src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://via.placeholder.com/40' }}" class="rounded-circle me-2" width="40" height="40" alt="{{ Auth::user()->name }}">
                        <textarea class="form-control" name="content" rows="3" placeholder="Quoi de neuf, {{ Auth::user()->name }} ?"></textarea>
                    </div>
                    @error('content')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror
                    
                    <div class="mb-3">
                        <label for="image" class="form-label small">
                            <i class="fas fa-image me-1"></i> Ajouter une image
                        </label>
                        <input type="file" class="form-control form-control-sm" id="image" name="image">
                        @error('image')
                            <div class="text-danger small mt-1">{{ $message ?? 'Le fichier image est invalide.' }}</div>
                        @enderror
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-post px-4">
                            <i class="fas fa-paper-plane me-1"></i> Publier
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif
        
        <!-- Liste des publications -->
        @if(isset($posts) && $posts->count() > 0)
            @foreach($posts as $post)
                @if($post->user)
                <div class="card post-card mb-4">
                    <div class="post-header">
                        <img src="{{ $post->user->profile_picture ? asset('storage/' . $post->user->profile_picture) : 'https://via.placeholder.com/40' }}" class="rounded-circle me-2" width="40" height="40" alt="{{ $post->user->name }}">
                        <div class="flex-grow-1">
                            <a href="{{ route('profile.show', $post->user) }}" class="text-decoration-none text-dark fw-bold">{{ $post->user->name }}</a>
                            <div class="text-muted small">{{ $post->created_at->diffForHumans() }}</div>
                        </div>
                        @if(Auth::check() && Auth::id() === $post->user_id)
                        <div class="dropdown">
                            <button class="btn btn-sm text-muted" type="button" id="dropdownMenuButton{{ $post->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $post->id }}">
                                <li><a class="dropdown-item" href="{{ route('posts.edit', $post) }}"><i class="fas fa-edit me-2"></i> Modifier</a></li>
                                <li>
                                    <form action="{{ route('posts.destroy', $post) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">
                                            <i class="fas fa-trash-alt me-2"></i> Supprimer
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>
                    
                    <div class="post-content">
                        <p>{{ $post->content }}</p>
                        @if($post->image_path)
                            <img src="{{ asset('storage/' . $post->image_path) }}" class="post-img" alt="Image de publication">
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center mt-3 text-muted small">
                            <div>
                                @if($post->likes_count > 0)
                                <span>
                                    <i class="fas fa-thumbs-up text-primary"></i> {{ $post->likes_count ?? 0 }}
                                </span>
                                @endif
                            </div>
                            <div>
                                @if($post->comments && $post->comments->count() > 0)
                                <span>{{ $post->comments->count() }} commentaire(s)</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        @if(Auth::check())
                            <form action="{{ route('posts.toggle-like', $post) }}" method="POST" class="m-0 flex-grow-1">
                                @csrf
                                <button type="submit" class="action-btn btn-like {{ $post->isLikedBy(Auth::user()) ? 'active' : '' }}">
                                    <i class="fas fa-thumbs-up me-1"></i> J'aime
                                </button>
                            </form>
                            <button class="action-btn btn-comment comment-toggle" data-target="comment-form-{{ $post->id }}">
                                <i class="fas fa-comment me-1"></i> Commenter
                            </button>
                        @else
                            <button class="action-btn btn-like" disabled>
                                <i class="fas fa-thumbs-up me-1"></i> J'aime
                            </button>
                            <button class="action-btn btn-comment" disabled>
                                <i class="fas fa-comment me-1"></i> Commenter
                            </button>
                        @endif
                    </div>
                    
                    @if(Auth::check())
                    <!-- Formulaire de commentaire (initialement caché) -->
                    <div class="comment-form" id="comment-form-{{ $post->id }}" style="display: none;">
                        <form action="{{ route('comments.store', $post) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" class="form-control" name="content" placeholder="Ajouter un commentaire...">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                    
                    <!-- Commentaires -->
                    @if($post->comments && $post->comments->count() > 0)
                        <div class="comment-section">
                            @foreach($post->comments as $comment)
                                @if($comment->user)
                                <div class="d-flex mb-3">
                                    <img src="{{ $comment->user->profile_picture ? asset('storage/' . $comment->user->profile_picture) : 'https://via.placeholder.com/30' }}" class="rounded-circle me-2 mt-1" width="30" height="30" alt="{{ $comment->user->name }}">
                                    <div class="flex-grow-1">
                                        <div class="comment-bubble">
                                            <div class="d-flex justify-content-between">
                                                <span class="fw-bold">{{ $comment->user->name }}</span>
                                                <small class="text-muted ms-2">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0 mt-1">{{ $comment->content }}</p>
                                        </div>
                                        
                                        @if(Auth::check() && Auth::id() === $comment->user_id)
                                            <div class="mt-1 text-end">
                                                <small>
                                                    <a href="#" class="text-muted edit-comment-link" data-comment-id="{{ $comment->id }}" data-comment-content="{{ $comment->content }}">Modifier</a> &middot;
                                                    <form class="d-inline" action="{{ route('comments.destroy', $comment) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link p-0 text-muted" style="font-size: 0.8rem;" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                                                    </form>
                                                </small>
                                            </div>
                                            
                                            <!-- Formulaire d'édition de commentaire (caché par défaut) -->
                                            <div class="edit-comment-form mt-2" id="edit-comment-form-{{ $comment->id }}" style="display: none;">
                                                <form action="{{ route('comments.update', $comment) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="content" value="{{ $comment->content }}">
                                                        <button class="btn btn-sm btn-primary" type="submit">Modifier</button>
                                                        <button type="button" class="btn btn-sm btn-light cancel-edit" data-comment-id="{{ $comment->id }}">Annuler</button>
                                                    </div>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            @endforeach
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center my-4">
                {{ $posts->links() }}
            </div>
        @else
            <div class="alert alert-info alert-custom">
                <i class="fas fa-info-circle me-2"></i> Aucune publication à afficher. Commencez par ajouter des amis ou créer une publication !
            </div>
        @endif
    </div>
    
    <div class="col-lg-3 d-none d-lg-block">
        <!-- Suggestions d'amis -->
        <div class="card suggestion-card">
            <div class="card-header bg-white">
                <h6 class="mb-0">Suggestions d'amis</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">Retrouvez des amis pour enrichir votre expérience.</p>
                @if(Route::has('users.index'))
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-primary w-100">
                    <i class="fas fa-search me-2"></i> Rechercher des amis
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Afficher/masquer le formulaire de commentaire
        $('.comment-toggle').click(function() {
            var targetId = $(this).data('target');
            $('#' + targetId).toggle();
        });
        
        // Afficher le formulaire d'édition de commentaire
        $('.edit-comment-link').click(function(e) {
            e.preventDefault();
            var commentId = $(this).data('comment-id');
            $('#edit-comment-form-' + commentId).show();
        });
        
        // Cacher le formulaire d'édition de commentaire
        $('.cancel-edit').click(function() {
            var commentId = $(this).data('comment-id');
            $('#edit-comment-form-' + commentId).hide();
        });
    });
</script>
@endsection 