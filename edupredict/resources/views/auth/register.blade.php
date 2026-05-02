<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription - EduPredict</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        min-height: 100vh;
        background: linear-gradient(145deg, #eff6f3, #f8f1e8);
    }
    .auth-card {
        max-width: 540px;
        background: rgba(255,255,255,.84);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255,255,255,.75);
        box-shadow: 0 24px 70px rgba(20, 58, 52, .12);
    }
</style>
</head>
<body class="d-flex align-items-center justify-content-center p-4">
    <div class="auth-card rounded-5 p-4 p-lg-5 w-100">
        <span class="badge rounded-pill text-bg-primary-subtle text-primary mb-3">Compte Etudiant</span>
        <h1 class="h2 fw-bold mb-2">Inscription</h1>
        <p class="text-secondary mb-4">Cree un compte pour acceder a l'interface de prediction etudiante.</p>

        @if ($errors->any())
        <div class="alert alert-danger rounded-4 border-0">
            @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register.perform') }}" class="row g-3">
            @csrf
            <div class="col-12">
                <label class="form-label" for="name">Nom</label>
                <input class="form-control form-control-lg rounded-4" id="name" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="email">Email</label>
                <input class="form-control form-control-lg rounded-4" id="email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">Mot de passe</label>
                <input class="form-control form-control-lg rounded-4" id="password" type="password" name="password" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirmation</label>
                <input class="form-control form-control-lg rounded-4" id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <div class="col-12 d-grid">
                <button class="btn btn-primary btn-lg rounded-pill" type="submit">Creer le compte</button>
            </div>
        </form>

        <div class="text-secondary small mt-4">
            Deja inscrit ?
            <a href="{{ route('login') }}" class="text-decoration-none">Se connecter</a>
        </div>
    </div>
</body>
</html>
