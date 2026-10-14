<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\SurveyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyController extends Controller
{
    public function __construct(private readonly SurveyService $surveyService)
    {
    }

    public function index(): View
    {
        $surveys = Survey::withCount('responses')->latest('id')->paginate(20);

        return view('admin-views.surveys.index', compact('surveys'));
    }

    public function create(): View
    {
        return view('admin-views.surveys.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'questions' => ['required', 'array', 'min:1', 'max:20'],
            'questions.*.text' => ['required', 'string', 'max:500'],
            'questions.*.options_text' => ['required', 'string', 'max:2100'],
            'questions.*.allow_other' => ['required', 'boolean'],
        ]);

        $data['questions'] = array_map(static fn (array $question): array => [
            'text' => $question['text'],
            'options' => array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $question['options_text'])),
                static fn (string $option): bool => $option !== '',
            )),
            'allow_other' => (bool) $question['allow_other'],
        ], $data['questions']);

        $survey = $this->surveyService->create(data: $data);

        return redirect()->route('admin.surveys.responses', $survey->id)
            ->with('survey_success', translate('encuesta_creada'));
    }

    public function status(Request $request, Survey $survey): RedirectResponse
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $this->surveyService->setActive(survey: $survey, active: (bool) $data['is_active']);

        return back()->with('survey_success', translate('encuesta_estado_actualizado'));
    }

    public function responses(Survey $survey): View
    {
        $responses = $survey->responses()->with('customer')->latest('id')->paginate(20);

        return view('admin-views.surveys.responses', compact('survey', 'responses'));
    }

    public function export(Survey $survey): StreamedResponse
    {
        return response()->streamDownload(function () use ($survey): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            $headers = [translate('encuesta_tendero'), translate('email'), translate('encuesta_fecha')];
            foreach ($survey->questions as $question) {
                $headers[] = $question['text'];
            }
            fputcsv($output, array_map([$this, 'csvCell'], $headers), ',', '"', '');

            $survey->responses()->with('customer')->chunkById(200, function ($responses) use ($survey, $output): void {
                foreach ($responses as $response) {
                    $answers = collect($response->answers)->keyBy('question_id');
                    $row = [
                        trim(($response->customer?->f_name ?? '') . ' ' . ($response->customer?->l_name ?? '')) ?: '#' . $response->customer_id,
                        $response->customer?->email ?? '',
                        $response->created_at->format('Y-m-d H:i:s'),
                    ];
                    foreach ($survey->questions as $question) {
                        $answer = $answers->get($question['id']);
                        $row[] = $answer && $answer['option'] === 'other'
                            ? translate('encuesta_otros') . ': ' . $answer['other_text']
                            : ($question['options'][(int) ($answer['option'] ?? -1)] ?? '');
                    }
                    fputcsv($output, array_map([$this, 'csvCell'], $row), ',', '"', '');
                }
            });
            fclose($output);
        }, 'encuesta-' . $survey->id . '-respuestas.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvCell(string $value): string
    {
        return preg_match('/^[\s\x{FEFF}]*[=+@-]/u', $value) ? "'" . $value : $value;
    }
}
