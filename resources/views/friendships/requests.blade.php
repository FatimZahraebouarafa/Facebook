@extends('layouts.app')

@section('title', 'Demandes d\'amitié')

@section('styles')
<style>
    .request-card {
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        overflow: hidden;
    }
    
    .request-card:hover {
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
    
    .btn-accept {
        border-radius: 20px;
        font-weight: 500;
        padding: 5px 15px;
        background-color: #4CAF50;
        border-color: #4CAF50;
    }
    
    .btn-accept:hover {
        background-color: #43A047;
        border-color: #43A047;
    }
    
    .btn-reject {
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
                <a href="{{ route('friends.requests') }}" class="list-group-item list-group-item-action nav-link-custom active">
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
                <h5 class="mb-0">Demandes d'amitié en attente</h5>
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
                
                @if($pendingRequests->count() > 0)
                    <div class="row">
                        @foreach($pendingRequests as $request)
                            <div class="col-lg-6 col-md-12 mb-4">
                                <div class="card request-card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $request->user->profile_picture ? asset('storage/' . $request->user->profile_picture) : 'https://via.placeholder.com/64' }}" class="rounded-circle user-img me-3" alt="{{ $request->user->name }}">
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1">{{ $request->user->name }}</h5>
                                                <p class="text-muted small mb-2">{{ $request->user->email }}</p>
                                                <p class="text-muted small mb-3">
                                                    <i class="fas fa-clock me-1"></i> Demande reçue {{ $request->created_at->diffForHumans() }}
                                                </p>
                                                
                                                <div class="d-flex flex-wrap">
                                                    <form action="{{ route('friends.accept', $request) }}" method="POST" class="me-2 mb-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-sm btn-success btn-accept">
                                                            <i class="fas fa-check me-1"></i> Accepter
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('friends.reject', $request) }}" method="POST" class="mb-2">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger btn-reject">
                                                            <i class="fas fa-times me-1"></i> Refuser
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
                        <i class="fas fa-info-circle me-2"></i> Vous n'avez pas de demandes d'amitié en attente.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 