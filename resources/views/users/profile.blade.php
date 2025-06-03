@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="profile-header">
    <div class="cover-photo" id="cover-photo">
    </div>
    <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://via.placeholder.com/150' }}" alt="{{ $user->name }}" class="profile-pic">
    
    <div class="profile-info">
        <div class="d-flex justify-content-between">
            <div>
                <h2>{{ $user->name }}</h2>
                @if($user->bio)
                    <p class="text-muted">{{ $user->bio }}</p>
                @endif
            </div>
            
            @if(Auth::id() === $user->id)
                <a href="{{ route('profile.edit') }}" class="btn btn-primary">Modifier mon profil</a>
            @else
                <div>
                    @if($isFriend)
                        <form action="{{ route('friends.remove', $user) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Supprimer cet ami ?')">Retirer de mes amis</button>
                        </form>
                    @else
                        <form action="{{ route('friends.request', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary">Ajouter en ami</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
        
        <div class="mt-3">
            <a href="{{ route('profile.show', $user) }}" class="btn btn-outline-primary active">Publications</a>
            <a href="#" class="btn btn-outline-primary">À propos</a>
            <a href="#" class="btn btn-outline-primary">Amis</a>
            <a href="#" class="btn btn-outline-primary">Photos</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <!-- Informations sur l'utilisateur -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>À propos</h5>
            </div>
            <div class="card-body">
                <p><i class="fas fa-envelope me-2"></i> {{ $user->email }}</p>
                <p><i class="fas fa-calendar-alt me-2"></i> Membre depuis {{ $user->created_at->format('F Y') }}</p>
                @if($user->bio)
                    <p><i class="fas fa-info-circle me-2"></i> {{ $user->bio }}</p>
                @endif
            </div>
        </div>
        
        <!-- Amis -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Amis</h5>
                <a href="#" class="btn btn-sm btn-link">Voir tout</a>
            </div>
            <div class="card-body">
                <p class="text-muted">Cette fonctionnalité sera bientôt disponible.</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <!-- Publications de l'utilisateur -->
        @if(Auth::id() === $user->id)
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Créer une publication</h5>
                    <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <textarea class="form-control" name="content" rows="3" placeholder="Quoi de neuf, {{ $user->name }} ?"></textarea>
                            @error('content')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Ajouter une image</label>
                            <input type="file" class="form-control" id="image" name="image">
                            @error('image')
                                <div class="text-danger">{{ $message ?? 'Format d\'image invalide.' }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Publier</button>
                    </form>
                </div>
            </div>
        @endif
        
        <!-- Liste des publications -->
        @if($posts->count() > 0)
            @foreach($posts as $post)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <img src="{{ $post->user->profile_picture ? asset('storage/' . $post->user->profile_picture) : 'https://via.placeholder.com/40' }}" class="rounded-circle me-2" width="40" height="40" alt="{{ $post->user->name }}">
                            <a href="{{ route('profile.show', $post->user) }}">{{ $post->user->name }}</a>
                        </div>
                        <small class="text-muted">{{ $post->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $post->content }}</p>
                        @if($post->image_path)
                            <img src="{{ asset('storage/' . $post->image_path) }}" class="img-fluid rounded mb-3" alt="Publication image">
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <span class="me-3">
                                    <i class="fas fa-thumbs-up text-primary"></i> {{ $post->likes_count }}
                                </span>
                                <span>
                                    <i class="fas fa-comment text-primary"></i> {{ $post->comments->count() }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between mb-3">
                            <form action="{{ route('posts.toggle-like', $post) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm {{ $post->isLikedBy(Auth::user()) ? 'btn-primary' : 'btn-outline-primary' }}">
                                    <i class="fas fa-thumbs-up"></i> J'aime
                                </button>
                            </form>
                            <button class="btn btn-sm btn-outline-primary comment-toggle" data-target="comment-form-{{ $post->id }}">
                                <i class="fas fa-comment"></i> Commenter
                            </button>
                            @if(Auth::id() === $post->user_id)
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton{{ $post->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $post->id }}">
                                        <li><a class="dropdown-item" href="{{ route('posts.edit', $post) }}">Modifier</a></li>
                                        <li>
                                            <form action="{{ route('posts.destroy', $post) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?')">Supprimer</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Formulaire de commentaire (initialement caché) -->
                        <div class="comment-form" id="comment-form-{{ $post->id }}" style="display: none;">
                            <form action="{{ route('comments.store', $post) }}" method="POST">
                                @csrf
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" name="content" placeholder="Ajouter un commentaire...">
                                    <button class="btn btn-primary" type="submit">Envoyer</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Commentaires -->
                        @if($post->comments->count() > 0)
                            <div class="comment-section">
                                @foreach($post->comments as $comment)
                                    <div class="d-flex mb-2">
                                        <img src="{{ $comment->user->profile_picture ? asset('storage/' . $comment->user->profile_picture) : 'https://via.placeholder.com/30' }}" class="rounded-circle me-2" width="30" height="30" alt="{{ $comment->user->name }}">
                                        <div class="bg-light p-2 rounded flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $comment->user->name }}</strong>
                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-0">{{ $comment->content }}</p>
                                            
                                            @if(Auth::id() === $comment->user_id)
                                                <div class="mt-1 text-end">
                                                    <small>
                                                        <a href="#" class="edit-comment-link" data-comment-id="{{ $comment->id }}" data-comment-content="{{ $comment->content }}">Modifier</a> |
                                                        <form class="d-inline" action="{{ route('comments.destroy', $comment) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link p-0 text-danger" style="font-size: 0.8rem;" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                                                        </form>
                                                    </small>
                                                </div>
                                                
                                                <!-- Formulaire d'édition de commentaire (caché par défaut) -->
                                                <div class="edit-comment-form" id="edit-comment-form-{{ $comment->id }}" style="display: none;">
                                                    <form action="{{ route('comments.update', $comment) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="input-group mt-2">
                                                            <input type="text" class="form-control" name="content" value="{{ $comment->content }}">
                                                            <button class="btn btn-sm btn-primary" type="submit">Modifier</button>
                                                            <button type="button" class="btn btn-sm btn-secondary cancel-edit" data-comment-id="{{ $comment->id }}">Annuler</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $posts->links() }}
            </div>
        @else
            <div class="alert alert-info">
                {{ Auth::id() === $user->id ? 'Vous n\'avez' : $user->name . ' n\'a' }} pas encore publié de contenu.
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Appliquer l'image de couverture
        const coverPhoto = document.getElementById('cover-photo');
        const coverUrl = "{{ $user->cover_photo ? asset('storage/' . $user->cover_photo) : 'https://via.placeholder.com/1200x300?text=Cover+Photo' }}";
        coverPhoto.style.backgroundImage = "url('" + coverUrl + "')";
        
        // Afficher/masquer le formulaire de commentaire
        document.querySelectorAll('.comment-toggle').forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetElement = document.getElementById(targetId);
                if (targetElement) {
                    targetElement.style.display = targetElement.style.display === 'none' ? 'block' : 'none';
                }
            });
        });
        
        // Afficher le formulaire d'édition de commentaire
        document.querySelectorAll('.edit-comment-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const commentId = this.getAttribute('data-comment-id');
                const form = document.getElementById('edit-comment-form-' + commentId);
                if (form) {
                    form.style.display = 'block';
                }
            });
        });
        
        // Cacher le formulaire d'édition de commentaire
        document.querySelectorAll('.cancel-edit').forEach(function(button) {
            button.addEventListener('click', function() {
                const commentId = this.getAttribute('data-comment-id');
                const form = document.getElementById('edit-comment-form-' + commentId);
                if (form) {
                    form.style.display = 'none';
                }
            });
        });
    });
</script>
@endsection 