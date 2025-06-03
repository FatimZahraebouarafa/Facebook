<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Helvetica, Arial, sans-serif;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1), 0 8px 16px rgba(0, 0, 0, .1);
        }
        .login-header {
            color: #1877f2;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-login {
            background-color: #1877f2;
            border: none;
            color: white;
            width: 100%;
            padding: 10px;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #166fe5;
        }
        .form-control:focus {
            border-color: #1877f2;
            box-shadow: 0 0 0 2px #e7f3ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-header">Connexion</div>
            
            @if($errors->any())
                <div class="alert alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Adresse email" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
                </div>
                <button type="submit" class="btn btn-login">Se connecter</button>
            </form>
            
            <div class="text-center mt-3">
                <a href="#" style="color: #1877f2; text-decoration: none;">Mot de passe oublié ?</a>
            </div>
            
            <hr>
            
            <div class="text-center">
                <a href="{{ route('register') }}" class="btn btn-success" style="background-color: #42b72a; border: none;">
                    Créer un nouveau compte
                </a>
            </div>
        </div>
    </div>
</body>
</html>