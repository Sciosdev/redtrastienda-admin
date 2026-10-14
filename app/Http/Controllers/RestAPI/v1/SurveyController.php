<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Services\SurveyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SurveyController extends Controller
{
    public function __construct(private readonly SurveyService $surveyService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $customerId = (int) $request->user('api')->id;
        $surveys = Survey::query()
            ->where(function ($query) use ($customerId) {
                $query->where('is_active', true)->orWhereHas('responses', fn ($response) => $response->where('customer_id', $customerId));
            })
            ->with(['responses' => fn ($query) => $query->where('customer_id', $customerId)])
            ->latest('id')->get();
        $data = $surveys->map(fn (Survey $survey) => $this->surveyService->serialize(survey: $survey, response: $survey->responses->first()))->values();

        return new JsonResponse(['data' => $data, 'pending_count' => $data->where('is_active', true)->where('answered', false)->count()]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $survey = Survey::query()->find($id);
        if (!$survey) {
            return new JsonResponse(['message' => 'Encuesta no disponible.'], 404);
        }
        $response = $survey->responses()->where('customer_id', $request->user('api')->id)->first();
        if (!$survey->is_active && !$response) {
            return new JsonResponse(['message' => 'Encuesta no disponible.'], 404);
        }

        return new JsonResponse($this->surveyService->serialize(survey: $survey, response: $response));
    }

    public function store(Request $request, int $id): JsonResponse
    {
        $survey = Survey::query()->find($id);
        if (!$survey) {
            return new JsonResponse(['message' => 'Encuesta no disponible.'], 404);
        }

        try {
            $validated = Validator::make($request->all(), ['answers' => 'required|array'])->validate();
            $response = $this->surveyService->submit(survey: $survey, customerId: (int) $request->user('api')->id, answers: $validated['answers']);

            return new JsonResponse($this->surveyService->serialize(survey: $survey->fresh(), response: $response), 201);
        } catch (ValidationException $exception) {
            return new JsonResponse(['message' => 'Revisa las respuestas de la encuesta.', 'errors' => $exception->errors()], 422);
        } catch (HttpException $exception) {
            return new JsonResponse(['message' => $exception->getMessage()], $exception->getStatusCode());
        }
    }
}
