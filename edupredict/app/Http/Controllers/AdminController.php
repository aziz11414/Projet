<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $students = Student::with('user')->latest()->paginate(10);
        $allPredictions = Student::query()
            ->select(['id', 'filiere', 'result', 'probability', 'created_at'])
            ->get();
        $totalPredictions = Student::count();
        $successfulPredictions = Student::where('result', 'Reussi')->count();
        $failurePredictions = Student::where('result', 'Echoue')->count();
        $successRate = $totalPredictions > 0
            ? round(($successfulPredictions / $totalPredictions) * 100, 1)
            : 0;

        $sevenDayPeriod = collect(CarbonPeriod::create(
            now()->subDays(6)->startOfDay(),
            '1 day',
            now()->startOfDay()
        ));

        $predictionsByDay = $sevenDayPeriod->map(function (Carbon $date) use ($allPredictions): array {
            $count = $allPredictions
                ->filter(fn (Student $student) => $student->created_at?->isSameDay($date))
                ->count();

            return [
                'label' => $date->format('d M'),
                'count' => $count,
            ];
        });

        $resultsDistribution = [
            'labels' => ['Reussites', 'Echecs'],
            'data' => [$successfulPredictions, $failurePredictions],
        ];

        $filiereBreakdown = $allPredictions
            ->groupBy(fn (Student $student) => $student->filiere ?: 'Non renseignee')
            ->map(fn (Collection $group, string $filiere) => [
                'label' => $filiere,
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();

        $averageProbabilityByDay = $sevenDayPeriod->map(function (Carbon $date) use ($allPredictions): array {
            $dayPredictions = $allPredictions
                ->filter(fn (Student $student) => $student->created_at?->isSameDay($date) && $student->probability !== null);

            $average = $dayPredictions->count() > 0
                ? round((float) $dayPredictions->avg('probability'), 1)
                : 0;

            return [
                'label' => $date->format('d M'),
                'average' => $average,
            ];
        });

        return view('admin.dashboard', [
            'students' => $students,
            'stats' => [
                'total' => $totalPredictions,
                'success' => $successfulPredictions,
                'failure' => $failurePredictions,
                'success_rate' => $successRate,
                'average_absences' => round((float) Student::avg('absence'), 1),
                'students_count' => User::where('role', 'student')->count(),
                'admins_count' => User::where('role', 'admin')->count(),
            ],
            'charts' => [
                'predictions_by_day' => $predictionsByDay,
                'results_distribution' => $resultsDistribution,
                'filiere_breakdown' => $filiereBreakdown,
                'average_probability_by_day' => $averageProbabilityByDay,
            ],
        ]);
    }
}
