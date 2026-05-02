<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\JobController;
use App\Services\FastApiService;
use Illuminate\Support\Facades\Route;

Route::get('/ai/health', function (FastApiService $fastApiService) {
    return response()->json($fastApiService->health());
});

Route::post('/ai/test-parse-cv', function (FastApiService $fastApiService) {
    return response()->json($fastApiService->parseCv([
        'text' => 'Senior React and Laravel developer with API experience',
    ]));
});

Route::post('/ai/test-match', function (FastApiService $fastApiService) {
    return response()->json($fastApiService->matchCandidateToJob([
        'candidate_text' => 'React Laravel JavaScript REST API',
        'job_description' => 'We need a React Laravel developer with FastAPI and Docker',
        'required_skills' => ['React', 'Laravel', 'FastAPI', 'Docker'],
    ]));
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/ai/upload-cv', [AiController::class, 'uploadCv']);

Route::get('/candidates', [CandidateController::class, 'index']);
Route::post('/candidates', [CandidateController::class, 'store']);
Route::get('/candidates/{candidate}', [CandidateController::class, 'show']);
Route::delete('/candidates/{candidate}', [CandidateController::class, 'destroy']);

Route::get('/jobs', [JobController::class, 'index']);
Route::post('/jobs', [JobController::class, 'store']);
Route::get('/jobs/{job}', [JobController::class, 'show']);
Route::put('/jobs/{job}', [JobController::class, 'update']);
Route::delete('/jobs/{job}', [JobController::class, 'destroy']);
Route::get('/jobs/{job}/ranking', [JobController::class, 'ranking']);