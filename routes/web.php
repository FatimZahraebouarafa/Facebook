<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FriendshipController;
use Illuminate\Support\Facades\Route;

// Route d'accueil non authentifiée
Route::get('/', function () {
    return redirect()->route('login');
})->name('welcome');

// Routes d'authentification
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Routes d'inscription
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Routes protégées par authentification
Route::middleware('auth')->group(function () {
    // Page d'accueil (flux d'actualités)
    Route::get('/home', [PostController::class, 'feed'])->name('home');
    
    // Routes pour les publications
    Route::resource('posts', PostController::class);
    
    // Routes pour les commentaires
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    // Routes pour les likes
    Route::post('/posts/{post}/like', [LikeController::class, 'like'])->name('posts.like');
    Route::delete('/posts/{post}/unlike', [LikeController::class, 'unlike'])->name('posts.unlike');
    Route::post('/posts/{post}/toggle-like', [LikeController::class, 'toggle'])->name('posts.toggle-like');
    
    // Routes pour les profils utilisateurs
    Route::get('/profile/{user}', [UserController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [UserController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [UserController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [UserController::class, 'editPassword'])->name('profile.edit-password');
    Route::put('/profile/password', [UserController::class, 'updatePassword'])->name('profile.update-password');
    
    // Routes pour les amitiés
    Route::get('/friends', [FriendshipController::class, 'index'])->name('friends.index');
    Route::get('/friends/requests', [FriendshipController::class, 'requests'])->name('friends.requests');
    Route::post('/friends/request/{user}', [FriendshipController::class, 'sendRequest'])->name('friends.request');
    Route::put('/friends/accept/{friendship}', [FriendshipController::class, 'acceptRequest'])->name('friends.accept');
    Route::put('/friends/reject/{friendship}', [FriendshipController::class, 'rejectRequest'])->name('friends.reject');
    Route::delete('/friends/remove/{user}', [FriendshipController::class, 'removeFriend'])->name('friends.remove');
    
    // Recherche d'utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});