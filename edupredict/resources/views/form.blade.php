<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EduPredict Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    :root {
        --brand-900: #0d3b35;
        --brand-700: #1c6b5a;
        --brand-500: #2d8b74;
        --brand-100: #dff3eb;
        --sand-100: #f6f0e7;
        --sand-200: #efe6d8;
        --ink-900: #1d2935;
    }

    body {
        min-height: 100vh;
        color: var(--ink-900);
        background:
            radial-gradient(circle at top left, rgba(223, 243, 235, 0.95), transparent 28%),
            radial-gradient(circle at right 18%, rgba(240, 207, 178, 0.65), transparent 22%),
            linear-gradient(135deg, #f8f4ec 0%, var(--sand-100) 45%, #eef6f2 100%);
    }

    .app-shell {
        max-width: 1320px;
    }

    .glass-card {
        background: rgba(255, 252, 247, 0.82);
        backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.75);
        box-shadow: 0 24px 80px rgba(17, 44, 39, 0.12);
    }

    .hero-panel {
        background:
            linear-gradient(160deg, rgba(13, 59, 53, 0.96), rgba(28, 107, 90, 0.9)),
            linear-gradient(180deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0));
        color: #fdf8f0;
        overflow: hidden;
        position: relative;
    }

    .hero-panel::after {
        content: "";
        position: absolute;
        width: 340px;
        height: 340px;
        right: -120px;
        top: -100px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.18), transparent 65%);
    }

    .badge-soft {
        background: rgba(255, 255, 255, 0.12);
        color: #f7efe6;
        border: 1px solid rgba(255, 255, 255, 0.16);
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-size: 0.72rem;
    }

    .stat-card {
        border: 0;
        border-radius: 1.5rem;
    }

    .stat-value {
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        font-weight: 700;
        line-height: 1;
        color: var(--brand-900);
    }

    .section-title {
        color: var(--brand-900);
        letter-spacing: -0.03em;
    }

    .form-label {
        font-weight: 600;
        color: #31414d;
    }

    .form-control {
        border-radius: 1rem;
        border-color: rgba(28, 107, 90, 0.14);
        padding: 0.9rem 1rem;
        background: rgba(255, 255, 255, 0.78);
    }

    .form-control:focus {
        border-color: rgba(28, 107, 90, 0.45);
        box-shadow: 0 0 0 0.25rem rgba(45, 139, 116, 0.12);
    }

    .btn-brand {
        background: linear-gradient(135deg, var(--brand-700), var(--brand-500));
        border: none;
        color: #fff;
        border-radius: 999px;
        padding: 0.95rem 1.4rem;
        font-weight: 700;
        box-shadow: 0 18px 34px rgba(28, 107, 90, 0.22);
    }

    .btn-brand:hover {
        color: #fff;
        background: linear-gradient(135deg, #155246, #26765f);
    }

    .table thead th {
        color: #60707c;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        border-bottom-color: rgba(49, 65, 77, 0.08);
    }

    .table tbody td {
        vertical-align: middle;
        border-bottom-color: rgba(49, 65, 77, 0.08);
    }

    .table tbody tr:last-child td {
        border-bottom: 0;
    }

    .result-pill {
        border-radius: 999px;
        padding: 0.45rem 0.8rem;
        font-weight: 700;
        font-size: 0.82rem;
    }

    .result-success {
        background: #def4e5;
        color: #17603c;
    }

    .result-danger {
        background: #fde4df;
        color: #8b301f;
    }

    .history-table {
        min-width: 860px;
    }

    @media (max-width: 991.98px) {
        .hero-copy {
            max-width: 100%;
        }
    }
</style>
</head>
<body>
<div class="container py-4 py-lg-5">
    <div class="app-shell mx-auto">
        <section class="glass-card hero-panel rounded-5 p-4 p-lg-5 mb-4">
            <div class="row g-4 align-items-end position-relative">
                <div class="col-lg-7">
                    <span class="badge badge-soft rounded-pill px-3 py-2 mb-3">Admin Dashboard</span>
                    <h1 class="display-4 fw-bold mb-3">EduPredict pilote les predictions et l'historique des etudiants.</h1>
                    <p class="hero-copy fs-5 text-white-50 mb-0">
                        Un seul espace pour lancer les analyses, suivre les tendances de reussite
                        et consulter les donnees deja enregistrees par Laravel.
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="glass-card rounded-4 p-3 h-100">
                                <div class="text-white-50 small mb-2">Predictions</div>
                                <div class="fs-2 fw-bold">{{ $stats['total'] }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="glass-card rounded-4 p-3 h-100">
                                <div class="text-white-50 small mb-2">Taux de reussite</div>
                                <div class="fs-2 fw-bold">{{ $stats['success_rate'] }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="glass-card stat-card rounded-5 p-4 h-100">
                    <div class="text-secondary small mb-2">Total des predictions</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="text-secondary mt-2">Historique global en base</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="glass-card stat-card rounded-5 p-4 h-100">
                    <div class="text-secondary small mb-2">Predictions reussies</div>
                    <div class="stat-value">{{ $stats['success'] }}</div>
                    <div class="text-secondary mt-2">Etudiants classes "Reussi"</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="glass-card stat-card rounded-5 p-4 h-100">
                    <div class="text-secondary small mb-2">Predictions en echec</div>
                    <div class="stat-value">{{ $stats['failure'] }}</div>
                    <div class="text-secondary mt-2">Etudiants classes "Echoue"</div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="glass-card stat-card rounded-5 p-4 h-100">
                    <div class="text-secondary small mb-2">Moyenne absences</div>
                    <div class="stat-value">{{ $stats['average_absences'] }}</div>
                    <div class="text-secondary mt-2">Tendance des absences</div>
                </div>
            </div>
        </section>

        <section class="row g-4 align-items-start mb-4">
            <div class="col-xl-5">
                <div class="glass-card rounded-5 p-4 p-lg-4 h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                        <div>
                            <h2 class="section-title h3 mb-2">Nouvelle prediction</h2>
                            <p class="text-secondary mb-0">Saisissez les donnees d'un etudiant puis lancez l'analyse du modele.</p>
                        </div>
                        <span class="badge text-bg-light rounded-pill px-3 py-2">API Python</span>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger border-0 rounded-4 mb-4">
                        <div class="fw-semibold mb-1">Verification necessaire</div>
                        @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    @if (session('result'))
                    <div class="alert {{ session('result') === 'Reussi' ? 'alert-success' : 'alert-danger' }} border-0 rounded-4 mb-4">
                        <div class="fw-semibold">Resultat : {{ session('result') }}</div>
                        @if (session()->has('probability') && session('probability') !== null)
                        <div>Probabilite de reussite : {{ session('probability') }}%</div>
                        @endif
                    </div>
                    @endif

                    <form method="POST" action="/predict" class="row g-3">
                        @csrf

                        <div class="col-12">
                            <label for="name" class="form-label">Nom de l'etudiant</label>
                            <input id="name" type="text" name="name" class="form-control" placeholder="Ex: Amina Ben Ali" value="{{ old('name') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="age" class="form-label">Age</label>
                            <input id="age" type="number" name="age" min="15" max="25" class="form-control" placeholder="18" value="{{ old('age') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="filiere" class="form-label">Filiere</label>
                            <input id="filiere" type="text" name="filiere" class="form-control" placeholder="Ex: Informatique" value="{{ old('filiere') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="study_time" class="form-label">Temps d'etude</label>
                            <input id="study_time" type="number" name="study_time" min="1" max="4" class="form-control" placeholder="2" value="{{ old('study_time') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="failures" class="form-label">Nombre d'echecs</label>
                            <input id="failures" type="number" name="failures" min="0" max="3" class="form-control" placeholder="0" value="{{ old('failures') }}">
                        </div>

                        <div class="col-12">
                            <label for="absence" class="form-label">Absences</label>
                            <input id="absence" type="number" name="absence" min="0" class="form-control" placeholder="4" value="{{ old('absence') }}">
                        </div>

                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-brand btn-lg">Lancer la prediction</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-xl-7">
                <div class="glass-card rounded-5 p-4 h-100">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <h2 class="section-title h3 mb-2">Historique des predictions</h2>
                            <p class="text-secondary mb-0">Les dernieres donnees enregistrees apparaissent ici.</p>
                        </div>
                        <span class="badge text-bg-light rounded-pill px-3 py-2">{{ $students->total() }} enregistrements</span>
                    </div>

                    @if ($students->count())
                    <div class="table-responsive">
                        <table class="table history-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Etudiant</th>
                                    <th>Filiere</th>
                                    <th>Profil</th>
                                    <th>Prediction</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $student->name }}</div>
                                        <div class="text-secondary small">{{ $student->age }} ans</div>
                                    </td>
                                    <td>{{ $student->filiere ?: 'Non renseignee' }}</td>
                                    <td class="small text-secondary">
                                        Etude: {{ $student->study_time }}/4<br>
                                        Echecs: {{ $student->failures }}<br>
                                        Absences: {{ $student->absence }}
                                    </td>
                                    <td>
                                        <span class="result-pill {{ $student->result === 'Reussi' ? 'result-success' : 'result-danger' }}">
                                            {{ $student->result }}
                                        </span>
                                        <div class="small text-secondary mt-2">
                                            {{ $student->probability !== null ? number_format($student->probability, 2) . '%' : 'Probabilite indisponible' }}
                                        </div>
                                    </td>
                                    <td class="small text-secondary">{{ $student->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $students->links() }}
                    </div>
                    @else
                    <div class="rounded-4 border border-secondary-subtle bg-light p-4 text-secondary">
                        Aucune prediction n'a encore ete enregistree. Lance la premiere analyse pour alimenter le dashboard.
                    </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
