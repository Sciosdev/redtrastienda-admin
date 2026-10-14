<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SurveyService
{
    public function create(array $data): Survey
    {
        $validated = Validator::make($data, [
            'title' => 'required|string|max:160',
            'description' => 'nullable|string|max:2000',
            'is_active' => 'required|boolean',
            'questions' => 'required|array|min:1|max:20',
        ])->validate();

        $title = trim($validated['title']);
        if ($title === '') {
            throw ValidationException::withMessages(['title' => 'Escribe el título de la encuesta.']);
        }

        return Survey::query()->create([
            'title' => $title,
            'description' => isset($validated['description']) ? trim($validated['description']) : null,
            'is_active' => (bool) $validated['is_active'],
            'questions' => $this->normalizeQuestions(questions: $validated['questions']),
        ]);
    }

    public function normalizeQuestions(array $questions): array
    {
        Validator::make(['questions' => $questions], [
            'questions' => 'required|array|min:1|max:20',
            'questions.*' => 'required|array',
            'questions.*.text' => 'required|string|max:500',
            'questions.*.options' => 'required|array|min:2|max:10',
            'questions.*.options.*' => 'required|string|max:200',
            'questions.*.allow_other' => 'required|boolean',
        ])->validate();

        if (!array_is_list($questions)) {
            throw ValidationException::withMessages(['questions' => 'Las preguntas deben enviarse como una lista.']);
        }

        $normalized = [];
        foreach ($questions as $index => $question) {
            $text = trim($question['text']);
            $options = array_map(fn (string $option) => trim($option), $question['options']);
            if ($text === '' || !array_is_list($options) || in_array('', $options, true) || count(array_unique($options)) !== count($options)) {
                throw ValidationException::withMessages(["questions.$index" => 'Escribe la pregunta y al menos dos opciones diferentes, sin campos vacíos.']);
            }
            $normalized[] = [
                'id' => 'q' . ($index + 1),
                'text' => $text,
                'options' => $options,
                'allow_other' => (bool) $question['allow_other'],
            ];
        }

        return $normalized;
    }

    public function setActive(Survey $survey, bool $active): Survey
    {
        return DB::transaction(function () use ($survey, $active) {
            $lockedSurvey = Survey::query()->lockForUpdate()->findOrFail($survey->id);
            $lockedSurvey->update(['is_active' => $active]);

            return $lockedSurvey;
        });
    }

    public function normalizeAnswers(Survey $survey, array $answers): array
    {
        $questions = $survey->questions;
        Validator::make(['answers' => $answers], [
            'answers' => 'required|array|size:' . count($questions),
            'answers.*' => 'required|array:question_id,option,other_text',
            'answers.*.question_id' => 'required|string|max:20|distinct:strict',
            'answers.*.option' => 'required|string|max:10',
            'answers.*.other_text' => 'nullable|string|max:1000',
        ])->validate();

        if (!array_is_list($answers)) {
            throw ValidationException::withMessages(['answers' => 'Las respuestas deben enviarse como una lista.']);
        }

        $byQuestion = array_column($answers, null, 'question_id');
        $normalized = [];
        foreach ($questions as $question) {
            $answer = $byQuestion[$question['id']] ?? null;
            if ($answer === null) {
                throw ValidationException::withMessages(['answers' => 'Responde cada pregunta de la encuesta una sola vez.']);
            }

            $option = $answer['option'];
            $otherText = isset($answer['other_text']) ? trim($answer['other_text']) : null;
            if ($option === 'other') {
                if (!$question['allow_other'] || $otherText === null || $otherText === '') {
                    throw ValidationException::withMessages(['answers' => 'Al elegir Otros, escribe tu respuesta en una pregunta que permita esa opción.']);
                }
            } elseif (!in_array($option, array_map('strval', array_keys($question['options'])), true) || ($otherText !== null && $otherText !== '')) {
                throw ValidationException::withMessages(['answers' => 'Selecciona una opción válida. El texto libre se permite únicamente al elegir Otros.']);
            }

            $normalized[] = ['question_id' => $question['id'], 'option' => $option, 'other_text' => $option === 'other' ? $otherText : null];
        }

        return $normalized;
    }

    public function submit(Survey $survey, int $customerId, array $answers): SurveyResponse
    {
        try {
            return DB::transaction(function () use ($survey, $customerId, $answers) {
                $lockedSurvey = Survey::query()->lockForUpdate()->findOrFail($survey->id);
                if ($lockedSurvey->responses()->where('customer_id', $customerId)->exists()) {
                    throw new HttpException(409, 'Ya respondiste esta encuesta.');
                }
                if (!$lockedSurvey->is_active) {
                    throw new HttpException(409, 'Esta encuesta está cerrada.');
                }

                return $lockedSurvey->responses()->create([
                    'customer_id' => $customerId,
                    'answers' => $this->normalizeAnswers(survey: $lockedSurvey, answers: $answers),
                ]);
            });
        } catch (UniqueConstraintViolationException $exception) {
            if ($survey->responses()->where('customer_id', $customerId)->exists()) {
                throw new HttpException(409, 'Ya respondiste esta encuesta.', $exception);
            }
            throw $exception;
        }
    }

    public function serialize(Survey $survey, ?SurveyResponse $response = null): array
    {
        return [
            'id' => $survey->id,
            'title' => $survey->title,
            'description' => $survey->description,
            'is_active' => $survey->is_active,
            'questions' => $survey->questions,
            'answered' => $response !== null,
            'answered_at' => $response?->created_at?->toIso8601String(),
            'answers' => $response?->answers,
        ];
    }
}
