<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Espace Etudiant - EduPredict</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: linear-gradient(145deg, #f5efe6, #ebf5f1); color: #1d2935; }
    .glass-card { background: rgba(255,252,247,.84); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,.75); box-shadow: 0 24px 80px rgba(17,44,39,.10); }
    .hero { background: linear-gradient(160deg, rgba(17,66,58,.97), rgba(35,120,99,.9)); color: #fffdf8; }
    .stat-value { font-size: 2.3rem; font-weight: 700; }
    .result-pill { border-radius: 999px; padding: .45rem .8rem; font-weight: 700; font-size: .82rem; }
    .result-success { background: #def4e5; color: #17603c; }
    .result-danger { background: #fde4df; color: #8b301f; }
 </style>
</head>
<body>
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="text-secondary">Bienvenue, {{ auth()->user()->name }}</div>
            <h1 class="h2 fw-bold mb-0">Interface Etudiant</h1>
        </div>
        <div class="d-flex gap-2">
            <span class="badge text-bg-success rounded-pill px-3 py-2">Role: Etudiant</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-dark rounded-pill px-4" type="submit">Deconnexion</button>
            </form>
        </div>
    </div>

    <section class="glass-card hero rounded-5 p-4 p-lg-5 mb-4">
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">Lance une prediction et suis ton historique personnel.</h2>
                <p class="mb-0 text-white-50">Ton espace regroupe les analyses, les probabilites et les resultats de reussite en un seul endroit.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="small text-white-50">Taux de reussite personnel</div>
                <div class="display-5 fw-bold">{{ $stats['success_rate'] }}%</div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="glass-card rounded-5 p-4 h-100">
                <div class="text-secondary small mb-2">Predictions realisees</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card rounded-5 p-4 h-100">
                <div class="text-secondary small mb-2">Predictions reussies</div>
                <div class="stat-value">{{ $stats['success'] }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="glass-card rounded-5 p-4 h-100">
                <div class="text-secondary small mb-2">Probabilite moyenne</div>
                <div class="stat-value">{{ $stats['average_probability'] }}%</div>
            </div>
        </div>
    </section>

    <section class="row g-4">
        <div class="col-xl-5">
            <div class="glass-card rounded-5 p-4">
                <h2 class="h3 fw-bold mb-2">Nouvelle prediction</h2>
                <p class="text-secondary mb-4">Saisis tes informations academiques pour lancer l'analyse.</p>

                @if ($errors->any())
                <div class="alert alert-danger rounded-4 border-0">
                    @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                    @endforeach
                </div>
                @endif

                @if (session('result'))
                <div class="alert {{ session('result') === 'Reussi' ? 'alert-success' : 'alert-danger' }} rounded-4 border-0">
                    <div class="fw-semibold">Resultat : {{ session('result') }}</div>
                    @if (session()->has('probability') && session('probability') !== null)
                    <div>Probabilite de reussite : {{ session('probability') }}%</div>
                    @endif
                </div>
                @endif

                <form method="POST" action="{{ route('student.predict') }}" class="row g-3">
                    @csrf
                    <div class="col-12">
                        <label class="form-label" for="name">Nom</label>
                        <input class="form-control form-control-lg rounded-4" id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="age">Age</label>
                        <input class="form-control form-control-lg rounded-4" id="age" type="number" name="age" min="15" max="25" value="{{ old('age') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="filiere">Filiere</label>
                        <input class="form-control form-control-lg rounded-4" id="filiere" type="text" name="filiere" value="{{ old('filiere') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="study_time">Temps d'etude</label>
                        <input class="form-control form-control-lg rounded-4" id="study_time" type="number" name="study_time" min="1" max="4" value="{{ old('study_time') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="failures">Nombre d'echecs</label>
                        <input class="form-control form-control-lg rounded-4" id="failures" type="number" name="failures" min="0" max="3" value="{{ old('failures') }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="absence">Absences</label>
                        <input class="form-control form-control-lg rounded-4" id="absence" type="number" name="absence" min="0" value="{{ old('absence') }}" required>
                    </div>
                    <div class="col-12 d-grid">
                        <button class="btn btn-success btn-lg rounded-pill" type="submit">Predire ma reussite</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="glass-card rounded-5 p-4">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-2">Mon historique</h2>
                        <p class="text-secondary mb-0">Tes dernieres predictions enregistrees.</p>
                    </div>
                    <span class="badge text-bg-light rounded-pill px-3 py-2">{{ $predictions->total() }} entrees</span>
                </div>

                @if ($predictions->count())
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Filiere</th>
                                <th>Profil</th>
                                <th>Resultat</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($predictions as $prediction)
                            <tr>
                                <td>{{ $prediction->filiere ?: 'Non renseignee' }}</td>
                                <td class="small text-secondary">
                                    Age: {{ $prediction->age }}<br>
                                    Etude: {{ $prediction->study_time }}/4<br>
                                    Echecs: {{ $prediction->failures }}<br>
                                    Absences: {{ $prediction->absence }}
                                </td>
                                <td>
                                    <span class="result-pill {{ $prediction->result === 'Reussi' ? 'result-success' : 'result-danger' }}">{{ $prediction->result }}</span>
                                    <div class="small text-secondary mt-2">{{ $prediction->probability !== null ? number_format((float) $prediction->probability, 2) . '%' : 'N/A' }}</div>
                                </td>
                                <td class="small text-secondary">{{ $prediction->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $predictions->links() }}</div>
                @else
                <div class="alert alert-light border rounded-4 mb-0">Aucune prediction enregistree pour le moment.</div>
                @endif
            </div>
        </div>
    </section>
</div>
</body>
</html>
