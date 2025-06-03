<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: Helvetica, Arial, sans-serif; }
        .register-container { max-width: 400px; margin: 50px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, .1), 0 8px 16px rgba(0, 0, 0, .1); }
        .register-header { color: #1877f2; font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .btn-register { background-color: #42b72a; border: none; color: white; width: 100%; padding: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-header">Créer un compte</div>
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                
                <div class="mb-3">
                    <input type="text" class="form-control" name="name" 
                           placeholder="Nom complet" required>
                </div>
                
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" 
                           placeholder="Adresse email" required>
                </div>
                
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" 
                           placeholder="Mot de passe" required>
                </div>
                
                <div class="mb-3">
                    <input type="password" class="form-control" name="password_confirmation" 
                           placeholder="Confirmez le mot de passe" required>
                </div>
                
                <button type="submit" class="btn btn-register">S'inscrire</button>
            </form>
            
            <hr>
            
            <div class="text-center">
                <a href="{{ route('login') }}" class="btn btn-primary" 
                   style="background-color: #1877f2; border: none;">
                    Vous avez déjà un compte? Connectez-vous
                </a>
            </div>
        </div>
    </div>
</body>
</html>