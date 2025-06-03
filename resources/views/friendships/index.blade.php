@extends('layouts.app')

@section('title', 'Mes amis')

@section('styles')
<style>
    .friend-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        overflow: hidden;
    }
    
    .friend-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .friend-img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .btn-action {
        border-radius: 20px;
        font-weight: 500;
        padding: 5px 15px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-3 col-md-4">
        <!-- Navigation -->
        <div class="card nav-card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">Navigation</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('home') }}" class="list-group-item list-group-item-action nav-link-custom">
                    <i class="fas fa-newspaper me-2"></i> Fil d'actualité
                </a>
                <a href="{{ route('friends.index') }}" class="list-group-item list-group-item-action nav-link-custom active">
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
    </div>
    
    <div class="col-lg-9 col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Mes amis</h5>
            </div>
            <div class="card-body">
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
                
                @if($friends->count() > 0)
                    <div class="row">
                        @foreach($friends as $friend)
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card friend-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $friend->profile_picture ? asset('storage/' . $friend->profile_picture) : 'https://via.placeholder.com/64' }}" class="rounded-circle friend-img me-3" alt="{{ $friend->name }}">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $friend->name }}</h5>
                                                <p class="text-muted small mb-2">{{ $friend->email }}</p>
                                                
                                                <div class="d-flex flex-wrap align-items-center mt-2">
                                                    <a href="{{ route('profile.show', $friend) }}" class="btn btn-sm btn-primary btn-action me-2 mb-2">
                                                        <i class="fas fa-eye me-1"></i> Voir profil
                                                    </a>
                                                    <form action="{{ route('friends.remove', $friend) }}" method="POST" class="mb-2">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('Êtes-vous sûr de vouloir retirer cette personne de vos amis ?')">
                                                            <i class="fas fa-user-times me-1"></i> Retirer
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info alert-custom">
                        <i class="fas fa-info-circle me-2"></i> Vous n'avez pas encore d'amis. 
                        <a href="{{ route('users.index') }}" class="alert-link">Recherchez des utilisateurs</a> pour commencer à vous connecter.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 