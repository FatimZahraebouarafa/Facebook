<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Mon Application</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Déconnexion</button>
            </form>
        </div>
    </nav>
    
    <div class="container mt-5">
        <div class="alert alert-success">
            Bienvenue ! Vous êtes connecté en tant que <strong>{{ session('user_email') }}</strong>
        </div>
        <p>Ceci est une page d'accueil simple. Vous pouvez la personnaliser comme vous le souhaitez.</p>
    </div>
</body>
</html>