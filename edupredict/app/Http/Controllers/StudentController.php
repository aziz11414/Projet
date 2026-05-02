<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $predictions = Student::where('user_id', $user->id)->latest()->paginate(8);
        $totalPredictions = Student::where('user_id', $user->id)->count();
        $successfulPredictions = Student::where('user_id', $user->id)->where('result', 'Reussi')->count();
        $successRate = $totalPredictions > 0
            ? round(($successfulPredictions / $totalPredictions) * 100, 1)
            : 0;

        return view('student.dashboard', [
            'predictions' => $predictions,
            'stats' => [
                'total' => $totalPredictions,
                'success' => $successfulPredictions,
                'success_rate' => $successRate,
                'average_probability' => round((float) Student::where('user_id', $user->id)->avg('probability'), 1),
            ],
        ]);
    }

    public function predict(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:15', 'max:25'],
            'study_time' => ['required', 'integer', 'min:1', 'max:4'],
            'failures' => ['required', 'integer', 'min:0', 'max:3'],
            'absence' => ['required', 'integer', 'min:0'],
            'filiere' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $response = Http::timeout(10)
                ->acceptJson()
                ->post(config('services.predict_api.url'), [
                    'age' => $validated['age'],
                    'studytime' => $validated['study_time'],
                    'failures' => $validated['failures'],
                    'absences' => $validated['absence'],
                ])
                ->throw()
                ->json();
        } catch (ConnectionException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'api' => "Impossible de joindre l'API de prediction sur " . config('services.predict_api.url') . ". Lance le service FastAPI avec `" . config('services.predict_api.start_command') . "` puis reessaie.",
                ]);
        } catch (RequestException $exception) {
            return back()
                ->withInput()
                ->withErrors([
                    'api' => "L'API de prediction a repondu avec une erreur. Verifiez le service sur " . config('services.predict_api.url') . " puis reessaie.",
                ]);
        }

        $prediction = (int) ($response['prediction'] ?? 0);
        $probability = isset($response['probability_success'])
            ? round(((float) $response['probability_success']) * 100, 2)
            : null;
        $result = $prediction === 1 ? 'Reussi' : 'Echoue';

        Student::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'age' => $validated['age'],
            'study_time' => $validated['study_time'],
            'failures' => $validated['failures'],
            'absence' => $validated['absence'],
            'filiere' => $validated['filiere'] ?? '',
            'probability' => $probability,
            'result' => $result,
        ]);

        return redirect()->route('student.dashboard')->with([
            'result' => $result,
            'probability' => $probability,
        ]);
    }
}
