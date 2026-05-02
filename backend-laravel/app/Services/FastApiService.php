<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FastApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.fastapi.url', 'http://127.0.0.1:8000');
    }

    public function health(): array
    {
        return Http::get($this->baseUrl . '/health')->json() ?? [];
    }

    public function parseCv(array $payload = []): array
    {
        return Http::timeout(30)
            ->post($this->baseUrl . '/parse-cv', $payload)
            ->json() ?? [];
    }

    public function matchCandidateToJob(array $payload = []): array
    {
        return Http::timeout(30)
            ->post($this->baseUrl . '/match-candidate-job', $payload)
            ->json() ?? [];
    }
}