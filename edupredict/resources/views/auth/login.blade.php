<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion - EduPredict</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        min-height: 100vh;
        background: linear-gradient(145deg, #f6f0e8, #e6f2ed);
    }
    .auth-card {
        max-width: 460px;
        background: rgba(255,255,255,.82);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255,255,255,.7);
        box-shadow: 0 24px 70px rgba(20, 58, 52, .12);
    }
</style>
</head>
<body class="d-flex align-items-center justify-content-center p-4">
    <div class="auth-card rounded-5 p-4 p-lg-5 w-100">
        <span class="badge rounded-pill text-bg-success-subtle text-success mb-3">EduPredict Access</span>
        <h1 class="h2 fw-bold mb-2">Connexion</h1>
        <p class="text-secondary mb-4">Connecte-toi pour acceder a l'espace etudiant ou admin.</p>

        @if ($errors->any())
        <div class="alert alert-danger rounded-4 border-0">
            @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login.perform') }}" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label" for="email">Email</label>
                <input class="form-control form-control-lg rounded-4" id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="password">Mot de passe</label>
                <input class="form-control form-control-lg rounded-4" id="password" type="password" name="password" required>
            </div>
            <div class="col-12 form-check ms-1">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">Se souvenir de moi</label>
            </div>
            <div class="col-12 d-grid">
                <button class="btn btn-success btn-lg rounded-pill" type="submit">Se connecter</button>
            </div>
        </form>

        <div class="text-secondary small mt-4">
            Pas de compte ?
            <a href="{{ route('register') }}" class="text-decoration-none">Creer un compte etudiant</a>
        </div>
    </div>
</body>
</html>
