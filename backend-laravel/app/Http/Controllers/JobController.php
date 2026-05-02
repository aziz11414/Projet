<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Job;
use App\Models\MatchScore;
use App\Services\FastApiService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        return response()->json(Job::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'required_skills' => 'nullable|array',
            'required_skills.*' => 'string|max:100',
            'status' => 'nullable|string|max:50',
        ]);

        $job = Job::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'required_skills' => $validated['required_skills'] ?? [],
            'status' => $validated['status'] ?? 'open',
        ]);

        return response()->json([
            'message' => 'Offre créée avec succès.',
            'job' => $job,
        ], 201);
    }

    public function show(Job $job)
    {
        return response()->json($job);
    }

    public function update(Request $request, Job $job)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'required_skills' => 'nullable|array',
            'required_skills.*' => 'string|max:100',
            'status' => 'nullable|string|max:50',
        ]);

        $job->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'required_skills' => $validated['required_skills'] ?? [],
            'status' => $validated['status'] ?? 'open',
        ]);

        return response()->json([
            'message' => 'Offre modifiée avec succès.',
            'job' => $job->fresh(),
        ]);
    }

    public function destroy(Job $job)
    {
        MatchScore::where('job_id', $job->id)->delete();
        $job->delete();

        return response()->json([
            'message' => 'Offre supprimée avec succès.',
        ]);
    }

    public function ranking(Job $job, Request $request, FastApiService $fastApi)
    {
        $query = Candidate::query();

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('extracted_text', 'like', "%{$search}%");
            });
        }

        if ($request->filled('skill')) {
            $skill = $request->string('skill')->toString();

            $query->where(function ($q) use ($skill) {
                $q->where('summary', 'like', "%{$skill}%")
                    ->orWhere('extracted_text', 'like', "%{$skill}%");
            });
        }

        $candidates = $query->get();
        $ranking = [];

        foreach ($candidates as $candidate) {
            $match = $fastApi->matchCandidateToJob([
                'candidate_text' => $candidate->extracted_text ?: ($candidate->summary ?? ''),
                'job_description' => $job->description,
                'required_skills' => $job->required_skills ?? [],
            ]);

            $score = $match['score'] ?? 0;
            $matchedSkills = $match['matched_skills'] ?? [];
            $missingSkills = $match['missing_skills'] ?? [];
            $notes = $match['notes'] ?? null;
            $experienceLevel = $match['experience_level'] ?? null;
            $yearsExperience = $match['years_of_experience_estimate'] ?? null;

            MatchScore::updateOrCreate(
                [
                    'candidate_id' => $candidate->id,
                    'job_id' => $job->id,
                ],
                [
                    'score' => $score,
                    'matched_skills' => $matchedSkills,
                    'missing_skills' => $missingSkills,
                    'notes' => $notes,
                ]
            );

            $ranking[] = [
                'candidate' => $candidate,
                'score' => $score,
                'matched_skills' => $matchedSkills,
                'missing_skills' => $missingSkills,
                'notes' => $notes,
                'experience_level' => $experienceLevel,
                'years_of_experience_estimate' => $yearsExperience,
            ];
        }

        usort($ranking, fn ($a, $b) => $b['score'] <=> $a['score']);

        return response()->json([
            'job' => $job,
            'count' => count($ranking),
            'ranking' => $ranking,
        ]);
    }
}