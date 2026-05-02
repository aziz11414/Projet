<?php

namespace App\Http\Controllers;

use App\Services\FastApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\IOFactory;
use Smalot\PdfParser\Parser;

class AiController extends Controller
{
    public function uploadCv(Request $request, FastApiService $fastApi)
    {
        $request->validate([
            'cv' => 'required|file|mimes:txt,pdf,doc,docx|max:10240',
        ]);

        $file = $request->file('cv');

        if (!$file) {
            throw ValidationException::withMessages([
                'cv' => ['Le fichier CV est requis.'],
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());

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

        $response = $fastApi->parseCv([
            'text' => $text,
        ]);

        return response()->json($response);
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