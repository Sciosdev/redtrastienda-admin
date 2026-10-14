<?php

namespace Tests\Unit;

use App\Http\Controllers\RestAPI\v1\SurveyController;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Services\SurveyService;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SurveyServiceTest extends TestCase
{
    private Capsule $database;
    private SurveyService $service;
    private SurveyController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $container = new Container();
        Container::setInstance($container);
        $this->database = new Capsule($container);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $container->instance('db', $this->database->getDatabaseManager());
        $container->instance('db.schema', $this->database->getConnection()->getSchemaBuilder());
        $container->instance('validator', new Factory(new Translator(new ArrayLoader(), 'es'), $container));
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->id();
        });
        Capsule::table('users')->insert([['id' => 1], ['id' => 2]]);
        $this->migration()->up();
        $this->service = new SurveyService();
        $this->controller = new SurveyController($this->service);
    }

    protected function tearDown(): void
    {
        $this->database->getConnection()->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        parent::tearDown();
    }

    public function testMigrationLoadsOneActiveDemoWithoutFakeResponsesAndCanRunAgain(): void
    {
        $demo = Survey::query()->firstOrFail();
        $this->assertTrue($demo->is_active);
        $this->assertCount(3, $demo->questions);
        $this->assertSame(0, SurveyResponse::query()->count());
        $this->service->setActive(survey: $demo, active: false);
        $this->migration()->up();
        $this->assertSame(1, Survey::query()->count());
        $this->assertFalse($demo->fresh()->is_active);
    }

    public function testCreateTrimsContentAndAssignsStableQuestionIds(): void
    {
        $survey = $this->service->create([
            'title' => ' Nueva encuesta ', 'description' => ' Descripción ', 'is_active' => false,
            'questions' => [['text' => ' ¿Qué necesitas? ', 'options' => [' Precio ', ' Variedad '], 'allow_other' => true]],
        ]);
        $this->assertSame('Nueva encuesta', $survey->title);
        $this->assertSame('Descripción', $survey->description);
        $this->assertSame([['id' => 'q1', 'text' => '¿Qué necesitas?', 'options' => ['Precio', 'Variedad'], 'allow_other' => true]], $survey->questions);
        $this->assertFalse($survey->is_active);
    }

    public function testCreateRejectsDuplicateOptionsAfterTrimming(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->create([
            'title' => 'Encuesta', 'is_active' => true,
            'questions' => [['text' => 'Pregunta', 'options' => ['Precio', ' Precio '], 'allow_other' => true]],
        ]);
    }

    public function testOtherAnswerIsStoredWithOnlyTheAuthenticatedCustomer(): void
    {
        $survey = Survey::query()->firstOrFail();
        $answers = $this->answers();
        $answers[0] = ['question_id' => 'q1', 'option' => 'other', 'other_text' => ' Capacitación '];
        $request = $this->request(customerId: 1, data: ['answers' => array_reverse($answers), 'customer_id' => 2]);
        $response = $this->controller->store(request: $request, id: $survey->id);
        $this->assertSame(201, $response->getStatusCode());
        $body = $response->getData(true);
        $this->assertTrue($body['answered']);
        $this->assertNotNull($body['answered_at']);
        $this->assertSame('Capacitación', $body['answers'][0]['other_text']);
        $stored = SurveyResponse::query()->firstOrFail();
        $this->assertSame(1, $stored->customer_id);
        $this->assertSame('q1', $stored->answers[0]['question_id']);
        $otherCustomer = $this->controller->show(request: $this->request(customerId: 2), id: $survey->id)->getData(true);
        $this->assertFalse($otherCustomer['answered']);
        $this->assertNull($otherCustomer['answers']);
    }

    public function testDuplicateSubmissionIs409AndDoesNotOverwriteAnswers(): void
    {
        $survey = Survey::query()->firstOrFail();
        $request = $this->request(customerId: 1, data: ['answers' => $this->answers()]);
        $this->assertSame(201, $this->controller->store(request: $request, id: $survey->id)->getStatusCode());
        $this->assertSame(409, $this->controller->store(request: $request, id: $survey->id)->getStatusCode());
        $this->assertSame(1, SurveyResponse::query()->count());
        $this->assertSame('0', SurveyResponse::query()->firstOrFail()->answers[0]['option']);
    }

    public function testClosedSurveyRemainsVisibleOnlyToTheCustomerWhoAnswered(): void
    {
        $survey = Survey::query()->firstOrFail();
        $this->service->submit(survey: $survey, customerId: 1, answers: $this->answers());
        $this->service->setActive(survey: $survey, active: false);
        $answeredList = $this->controller->index(request: $this->request(customerId: 1))->getData(true);
        $unansweredList = $this->controller->index(request: $this->request(customerId: 2))->getData(true);
        $this->assertCount(1, $answeredList['data']);
        $this->assertSame(0, $answeredList['pending_count']);
        $this->assertSame([], $unansweredList['data']);
        $this->assertSame(200, $this->controller->show(request: $this->request(customerId: 1), id: $survey->id)->getStatusCode());
        $this->assertSame(404, $this->controller->show(request: $this->request(customerId: 2), id: $survey->id)->getStatusCode());
        $this->assertSame(409, $this->controller->store(request: $this->request(customerId: 2, data: ['answers' => $this->answers()]), id: $survey->id)->getStatusCode());
        $this->assertSame(1, SurveyResponse::query()->count());
    }

    public function testClosingSurveyRejectsSubmissionUsingAStaleActiveModel(): void
    {
        $survey = Survey::query()->firstOrFail();
        $this->service->setActive(survey: $survey, active: false);
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Esta encuesta está cerrada.');
        $this->service->submit(survey: $survey, customerId: 1, answers: $this->answers());
    }

    public function testPendingCountAndResponseOwnershipAreCustomerSpecific(): void
    {
        $survey = Survey::query()->firstOrFail();
        $this->service->submit(survey: $survey, customerId: 1, answers: $this->answers());
        $first = $this->controller->index(request: $this->request(customerId: 1))->getData(true);
        $second = $this->controller->index(request: $this->request(customerId: 2))->getData(true);
        $this->assertSame(0, $first['pending_count']);
        $this->assertSame(1, $second['pending_count']);
        $this->assertNull($second['data'][0]['answers']);
    }

    public function testInvalidQuestionSetsOptionsAndOtherTextReturn422WithoutSaving(): void
    {
        $survey = Survey::query()->firstOrFail();
        $invalidSets = [
            array_slice($this->answers(), 0, 2),
            [['question_id' => 'q1', 'option' => '0'], ['question_id' => 'q1', 'option' => '0'], ['question_id' => 'q3', 'option' => '0']],
            [['question_id' => 'unknown', 'option' => '0'], $this->answers()[1], $this->answers()[2]],
            [['question_id' => 'q1', 'option' => '9'], $this->answers()[1], $this->answers()[2]],
            [['question_id' => 'q1', 'option' => 0], $this->answers()[1], $this->answers()[2]],
            [['question_id' => 'q1', 'option' => 'other', 'other_text' => '   '], $this->answers()[1], $this->answers()[2]],
            [['question_id' => 'q1', 'option' => 'other', 'other_text' => str_repeat('x', 1001)], $this->answers()[1], $this->answers()[2]],
            [['question_id' => 'q1', 'option' => '0', 'other_text' => 'No corresponde'], $this->answers()[1], $this->answers()[2]],
            [['question_id' => 'q1', 'option' => 'other', 'other_text' => ['invalid']], $this->answers()[1], $this->answers()[2]],
            'invalid',
        ];
        foreach ($invalidSets as $answers) {
            $response = $this->controller->store(request: $this->request(customerId: 1, data: ['answers' => $answers]), id: $survey->id);
            $this->assertSame(422, $response->getStatusCode());
            $this->assertSame(0, SurveyResponse::query()->count());
        }
    }

    public function testOtherCannotBeUsedIfQuestionDisallowsIt(): void
    {
        $survey = $this->service->create([
            'title' => 'Sin Otros', 'is_active' => true,
            'questions' => [['text' => 'Pregunta', 'options' => ['A', 'B'], 'allow_other' => false]],
        ]);
        $this->expectException(ValidationException::class);
        $this->service->submit(survey: $survey, customerId: 1, answers: [['question_id' => 'q1', 'option' => 'other', 'other_text' => 'Respuesta']]);
    }

    public function testDatabaseUniqueConstraintRejectsASecondResponse(): void
    {
        $survey = Survey::query()->firstOrFail();
        $this->service->submit(survey: $survey, customerId: 1, answers: $this->answers());
        $this->expectException(UniqueConstraintViolationException::class);
        SurveyResponse::query()->create(['survey_id' => $survey->id, 'customer_id' => 1, 'answers' => $this->answers()]);
    }

    public function testAllSurveyRoutesRequireTheApiCustomerGuard(): void
    {
        $router = new Router(new Dispatcher(), Container::getInstance());
        Route::swap($router);
        require __DIR__ . '/../../routes/rest_api/v1/api.php';

        foreach ([['GET', '/v1/customer/surveys'], ['GET', '/v1/customer/surveys/1'], ['POST', '/v1/customer/surveys/1/responses']] as [$method, $path]) {
            $route = $router->getRoutes()->match(Request::create($path, $method));
            $this->assertContains('auth:api', $route->gatherMiddleware());
        }
    }

    private function migration(): object
    {
        return require __DIR__ . '/../../database/migrations/2026_10_14_100000_create_anpec_surveys_tables.php';
    }

    private function answers(): array
    {
        return [
            ['question_id' => 'q1', 'option' => '0', 'other_text' => null],
            ['question_id' => 'q2', 'option' => '1', 'other_text' => null],
            ['question_id' => 'q3', 'option' => '2', 'other_text' => null],
        ];
    }

    private function request(int $customerId, array $data = []): Request
    {
        $request = Request::create('/api/v1/customer/surveys', 'POST', $data);
        $request->setUserResolver(fn () => (object) ['id' => $customerId]);

        return $request;
    }
}
