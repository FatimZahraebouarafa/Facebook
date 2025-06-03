@extends('layouts.app')

@section('title', 'Recherche d\'utilisateurs')

@section('styles')
<style>
    .user-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        overflow: hidden;
    }
    
    .user-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    
    .user-img {
        width: 64px;
        height: 64px;
        object-fit: cover;
        border: 3px solid #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .search-container {
        position: relative;
        margin-bottom: 30px;
    }
    
    .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #65676b;
        z-index: 10;
    }
    
    .search-input {
        padding-left: 45px;
        border-radius: 30px;
        border: none;
        background-color: #f0f2f5;
        height: 50px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
    
    .search-btn {
        border-radius: 30px;
        padding: 10px 25px;
        font-weight: 500;
    }
    
    .badge-friend {
        background-color: #4267B2;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.8rem;
    }
    
    .badge-pending {
        background-color: #f7b928;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.8rem;
    }
    
    .btn-add-friend {
        border-radius: 20px;
        font-weight: 500;
        padding: 5px 15px;
    }
    
    .btn-view-profile {
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
                <a href="{{ route('friends.index') }}" class="list-group-item list-group-item-action nav-link-custom">
                    <i class="fas fa-users me-2"></i> Mes amis
                </a>
                <a href="{{ route('friends.requests') }}" class="list-group-item list-group-item-action nav-link-custom">
                    <i class="fas fa-user-plus me-2"></i> Demandes d'amitié
                </a>
                <a href="{{ route('users.index') }}" class="list-group-item list-group-item-action nav-link-custom active">
                    <i class="fas fa-search me-2"></i> Rechercher des amis
                </a>
            </div>
        </div>
        
        <!-- Profile card (mobile only) -->
        <div class="card profile-card d-md-none mb-4">
            @if(Auth::check())
                <div class="profile-header">
                    <img class="profile-img" src="{{ Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : 'https://via.placeholder.com/80' }}" alt="Photo de profil">
                </div>
                <div class="profile-info">
                    <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted small mb-3">{{ Auth::user()->email }}</p>
                    <a href="{{ route('profile.show', Auth::user()) }}" class="btn btn-sm btn-primary rounded-pill px-4">Voir mon profil</a>
                </div>
            @endif
        </div>
    </div>
    
    <div class="col-lg-9 col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Rechercher des amis</h5>
                
                @if(isset($users) && $users->count() > 0)
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="sortDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-sort me-1"></i> Trier par
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="sortDropdown">
                        <li>
                            <a class="dropdown-item {{ request('sort') == 'name' && request('order') == 'asc' ? 'active' : '' }}" 
                               href="{{ route('users.index', ['search' => $search, 'sort' => 'name', 'order' => 'asc']) }}">
                                Nom (A-Z)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request('sort') == 'name' && request('order') == 'desc' ? 'active' : '' }}" 
                               href="{{ route('users.index', ['search' => $search, 'sort' => 'name', 'order' => 'desc']) }}">
                                Nom (Z-A)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request('sort') == 'created_at' && request('order') == 'desc' ? 'active' : '' }}" 
                               href="{{ route('users.index', ['search' => $search, 'sort' => 'created_at', 'order' => 'desc']) }}">
                                Plus récent
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request('sort') == 'created_at' && request('order') == 'asc' ? 'active' : '' }}" 
                               href="{{ route('users.index', ['search' => $search, 'sort' => 'created_at', 'order' => 'asc']) }}">
                                Plus ancien
                            </a>
                        </li>
                    </ul>
                </div>
                @endif
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
                
                <form action="{{ route('users.index') }}" method="GET">
                    <div class="search-container">
                        <i class="fas fa-search search-icon"></i>
                        <div class="input-group">
                            <input type="text" class="form-control search-input" name="search" value="{{ $search ?? '' }}" placeholder="Rechercher des personnes par nom ou email...">
                            <button class="btn btn-primary search-btn" type="submit">Rechercher</button>
                        </div>
                    </div>
                </form>
                
                @if(isset($users) && $users->count() > 0)
                    <div class="row">
                        @foreach($users as $user)
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card user-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : 'https://via.placeholder.com/64' }}" class="rounded-circle user-img me-3" alt="{{ $user->name }}">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $user->name }}</h5>
                                                <p class="text-muted small mb-2">{{ $user->email }}</p>
                                                
                                                @if(isset($user->mutual_friends_count) && $user->mutual_friends_count > 0)
                                                <p class="text-muted small mb-2">
                                                    <i class="fas fa-user-friends me-1"></i> {{ $user->mutual_friends_count }} 
                                                    {{ $user->mutual_friends_count > 1 ? 'amis communs' : 'ami commun' }}
                                                </p>
                                                @endif
                                                
                                                <div class="d-flex flex-wrap align-items-center mt-2">
                                                    @if(isset($user->is_friend) && $user->is_friend)
                                                        <span class="badge-friend me-2 mb-2">
                                                            <i class="fas fa-check me-1"></i> Ami
                                                        </span>
                                                        <form class="d-inline mb-2" action="{{ route('friends.remove', $user) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                                                <i class="fas fa-user-times me-1"></i> Retirer
                                                            </button>
                                                        </form>
                                                    @elseif(isset($user->has_pending_request) && $user->has_pending_request)
                                                        <span class="badge-pending mb-2">
                                                            <i class="fas fa-clock me-1"></i> Demande en attente
                                                        </span>
                                                    @else
                                                        <form action="{{ route('friends.request', $user) }}" method="POST" class="mb-2 me-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-primary btn-add-friend send-friend-request">
                                                                <i class="fas fa-user-plus me-1"></i> Ajouter
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <a href="{{ route('profile.show', $user) }}" class="btn btn-sm btn-light btn-view-profile mb-2">
                                                        <i class="fas fa-eye me-1"></i> Voir profil
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="d-flex justify-content-center mt-4">
                        {{ $users->links() }}
                    </div>
                @else
                    <div class="alert alert-info alert-custom mt-4">
                        <i class="fas fa-info-circle me-2"></i> Aucun utilisateur trouvé. Essayez avec d'autres termes de recherche.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 