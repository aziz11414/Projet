<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - EduPredict</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: linear-gradient(145deg, #f8f1e8, #e9f3ef); color: #1d2935; }
    .glass-card { background: rgba(255,252,247,.84); backdrop-filter: blur(14px); border: 1px solid rgba(255,255,255,.75); box-shadow: 0 24px 80px rgba(17,44,39,.10); }
    .hero { background: linear-gradient(160deg, rgba(13,59,53,.97), rgba(28,107,90,.9)); color: #fffdf8; }
    .stat-value { font-size: 2.3rem; font-weight: 700; }
    .result-pill { border-radius: 999px; padding: .45rem .8rem; font-weight: 700; font-size: .82rem; }
    .result-success { background: #def4e5; color: #17603c; }
    .result-danger { background: #fde4df; color: #8b301f; }
    .chart-card canvas { max-height: 320px; }
    .chart-kicker { color: #65737f; font-size: .82rem; text-transform: uppercase; letter-spacing: .08em; }
</style>
</head>
<body>
<div class="container py-4 py-lg-5">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <div class="text-secondary">Bienvenue, {{ auth()->user()->name }}</div>
            <h1 class="h2 fw-bold mb-0">Dashboard Admin</h1>
        </div>
        <div class="d-flex gap-2">
            <span class="badge text-bg-dark rounded-pill px-3 py-2">Role: Admin</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-dark rounded-pill px-4" type="submit">Deconnexion</button>
            </form>
        </div>
    </div>

    <section class="glass-card hero rounded-5 p-4 p-lg-5 mb-4">
        <div class="row g-4 align-items-end">
            <div class="col-lg-8">
                <h2 class="display-6 fw-bold mb-3">Pilote l'activite des predictions et la performance globale des etudiants.</h2>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="small text-white-50">Taux de reussite global</div>
                <div class="display-5 fw-bold">{{ $stats['success_rate'] }}%</div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="glass-card rounded-5 p-4 h-100">
                <div class="text-secondary small mb-2">Predictions</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="glass-card rounded-5 p-4 h-100">
                <div class="text-secondary small mb-2">Etudiants</div>
                <div class="stat-value">{{ $stats['students_count'] }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="glass-card rounded-5 p-4 h-100">
                <div class="text-secondary small mb-2">Admins</div>
                <div class="stat-value">{{ $stats['admins_count'] }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="glass-card rounded-5 p-4 h-100">
                <div class="text-secondary small mb-2">Moyenne absences</div>
                <div class="stat-value">{{ $stats['average_absences'] }}</div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="glass-card rounded-5 p-4 h-100">
                <h2 class="h4 fw-bold mb-3">Repartition des resultats</h2>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-secondary">Reussites</span>
                    <strong>{{ $stats['success'] }}</strong>
                </div>
                <div class="progress mb-4" style="height: 14px;">
                    <div class="progress-bar bg-success" style="width: {{ $stats['success_rate'] }}%"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-secondary">Echecs</span>
                    <strong>{{ $stats['failure'] }}</strong>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="glass-card chart-card rounded-5 p-4 h-100">
                <div class="chart-kicker mb-2">Activite</div>
                <h2 class="h4 fw-bold mb-3">Predictions sur les 7 derniers jours</h2>
                <canvas id="predictionsByDayChart"></canvas>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="glass-card chart-card rounded-5 p-4 h-100">
                <div class="chart-kicker mb-2">Performance</div>
                <h2 class="h4 fw-bold mb-3">Reussite vs echec</h2>
                <canvas id="resultsDistributionChart"></canvas>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="glass-card chart-card rounded-5 p-4 h-100">
                <div class="chart-kicker mb-2">Segmentation</div>
                <h2 class="h4 fw-bold mb-3">Top filieres par volume</h2>
                <canvas id="filiereBreakdownChart"></canvas>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="glass-card chart-card rounded-5 p-4 h-100">
                <div class="chart-kicker mb-2">Qualite</div>
                <h2 class="h4 fw-bold mb-3">Probabilite moyenne de reussite</h2>
                <canvas id="averageProbabilityChart"></canvas>
            </div>
        </div>
    </section>

    <section class="glass-card rounded-5 p-4">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="h3 fw-bold mb-2">Historique complet des predictions</h2>
                <p class="text-secondary mb-0">Toutes les entrees enregistrees dans le systeme.</p>
            </div>
            <span class="badge text-bg-light rounded-pill px-3 py-2">{{ $students->total() }} lignes</span>
        </div>

        @if ($students->count())
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Etudiant</th>
                        <th>Compte</th>
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
                        <td class="small text-secondary">{{ $student->user?->email ?? 'Compte non lie' }}</td>
                        <td>{{ $student->filiere ?: 'Non renseignee' }}</td>
                        <td class="small text-secondary">
                            Etude: {{ $student->study_time }}/4<br>
                            Echecs: {{ $student->failures }}<br>
                            Absences: {{ $student->absence }}
                        </td>
                        <td>
                            <span class="result-pill {{ $student->result === 'Reussi' ? 'result-success' : 'result-danger' }}">{{ $student->result }}</span>
                            <div class="small text-secondary mt-2">{{ $student->probability !== null ? number_format((float) $student->probability, 2) . '%' : 'N/A' }}</div>
                        </td>
                        <td class="small text-secondary">{{ $student->created_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $students->links() }}</div>
        @else
        <div class="alert alert-light border rounded-4 mb-0">Aucune prediction enregistree pour le moment.</div>
        @endif
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const predictionsByDay = @json($charts['predictions_by_day']);
    const resultsDistribution = @json($charts['results_distribution']);
    const filiereBreakdown = @json($charts['filiere_breakdown']);
    const averageProbabilityByDay = @json($charts['average_probability_by_day']);

    const baseGridColor = 'rgba(29, 41, 53, 0.08)';
    const baseTextColor = '#5f6c76';

    new Chart(document.getElementById('predictionsByDayChart'), {
        type: 'line',
        data: {
            labels: predictionsByDay.map(item => item.label),
            datasets: [{
                label: 'Predictions',
                data: predictionsByDay.map(item => item.count),
                borderColor: '#1c6b5a',
                backgroundColor: 'rgba(28, 107, 90, 0.12)',
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: baseTextColor },
                    grid: { color: baseGridColor }
                },
                x: {
                    ticks: { color: baseTextColor },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    new Chart(document.getElementById('resultsDistributionChart'), {
        type: 'doughnut',
        data: {
            labels: resultsDistribution.labels,
            datasets: [{
                data: resultsDistribution.data,
                backgroundColor: ['#2e8b74', '#d7725f'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: baseTextColor, usePointStyle: true, boxWidth: 10 }
                }
            }
        }
    });

    new Chart(document.getElementById('filiereBreakdownChart'), {
        type: 'bar',
        data: {
            labels: filiereBreakdown.map(item => item.label),
            datasets: [{
                label: 'Predictions',
                data: filiereBreakdown.map(item => item.count),
                backgroundColor: ['#1c6b5a', '#2f8c74', '#59a792', '#8bc7b6', '#cbe9de'],
                borderRadius: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: baseTextColor },
                    grid: { color: baseGridColor }
                },
                x: {
                    ticks: { color: baseTextColor },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    new Chart(document.getElementById('averageProbabilityChart'), {
        type: 'bar',
        data: {
            labels: averageProbabilityByDay.map(item => item.label),
            datasets: [{
                label: 'Probabilite moyenne (%)',
                data: averageProbabilityByDay.map(item => item.average),
                backgroundColor: '#d5a65a',
                borderRadius: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { color: baseTextColor },
                    grid: { color: baseGridColor }
                },
                x: {
                    ticks: { color: baseTextColor },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>
</body>
</html>
