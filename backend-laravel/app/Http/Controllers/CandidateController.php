<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Services\FastApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $query = Candidate::query()->latest();

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
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

        $perPage = (int) $request->integer('per_page', 8);
        $perPage = max(1, min($perPage, 50));

        return response()->json(
            $query->paginate($perPage)
        );
    }

    public function store(Request $request, FastApiService $fastApi)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => ['nullable', 'regex:/^\+216\d{8}$/'],
            'cv' => 'required|file|mimes:txt,pdf,doc,docx|max:10240',
        ]);

        $file = $request->file('cv');

        if (!$file) {
            throw ValidationException::withMessages([
                'cv' => ['Le fichier CV est requis.'],
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->store('cvs', 'public');

        $text = match ($extension) {
            'txt' => $this->extractTextFromTxt($file->getRealPath()),
            'pdf' => $this->extractTextFromPdf($file->getRealPath()),
            'docx' => $this->extractTextFromDocx($file->getRealPath()),
            'doc' => throw ValidationException::withMessages([
                'cv' => ['Le format .doc est ancien. Utilise plutôt .docx, .pdf ou .txt.'],
            ]),
            default => throw ValidationException::withMessages([
                'cv' => ['Format de fichier non supporté.'],
            ]),
        };

        if (trim($text) === '') {
            throw ValidationException::withMessages([
                'cv' => ['Impossible d’extraire le texte du fichier CV.'],
            ]);
        }

        $aiResult = $fastApi->parseCv([
            'text' => $text,
        ]);

        $candidate = Candidate::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'cv_path' => $path,
            'extracted_text' => $text,
            'summary' => $aiResult['summary'] ?? null,
        ]);

        return response()->json([
            'message' => 'Candidate created successfully.',
            'candidate' => $candidate,
            'ai_result' => $aiResult,
        ], 201);
    }

    public function show(Candidate $candidate)
    {
        return response()->json($candidate);
    }

    public function destroy(Candidate $candidate)
    {
        if ($candidate->cv_path && Storage::disk('public')->exists($candidate->cv_path)) {
            Storage::disk('public')->delete($candidate->cv_path);
        }

        $candidate->delete();

        return response()->json([
            'message' => 'Candidat supprimé avec succès.',
        ]);
    }

    private function extractTextFromTxt(string $path): string
    {
        return file_get_contents($path) ?: '';
    }

    private function extractTextFromPdf(string $path): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($path);

        return $pdf->getText();
    }

    private function extractTextFromDocx(string $path): string
    {
        $phpWord = IOFactory::load($path, 'Word2007');
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof Text) {
                    $text .= $element->getText() . ' ';
                }
            }
        }

        return trim($text);
    }
}