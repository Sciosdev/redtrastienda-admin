<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\SurveyController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Survey;
use App\Services\SurveyService;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;
use Illuminate\Contracts\View\Factory as ViewFactoryContract;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Encryption\Encrypter;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Route;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Compilers\BladeCompiler;
use PHPUnit\Framework\TestCase;

class SurveyAdminTest extends TestCase
{
    private Application $app;
    private Capsule $database;
    private SurveyController $controller;
    private SurveyService $service;
    private Router $router;
    private Store $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = new Application(dirname(__DIR__, 2));
        $this->app->instance('config', new Repository(['app' => ['locale' => 'es', 'fallback_locale' => 'es']]));
        $this->app->instance('events', new Dispatcher($this->app));
        $this->app->instance('translator', new Translator(new FileLoader(new Filesystem(), $this->app->basePath('resources/lang')), 'es'));
        $this->app->instance('validator', new Factory($this->app['translator'], $this->app));
        $this->session = new Store('survey-tests', new ArraySessionHandler(120));
        $this->session->start();
        $this->session->put('local', 'es');
        $this->app->instance('session', $this->session);
        $this->app->instance('session.store', $this->session);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);

        $this->database = new Capsule($this->app);
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'foreign_key_constraints' => true]);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $this->app->instance('db', $this->database->getDatabaseManager());
        $this->app->instance('db.schema', $this->database->getConnection()->getSchemaBuilder());
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('f_name')->nullable();
            $table->string('l_name')->nullable();
            $table->string('email')->nullable();
        });
        Capsule::table('users')->insert(['id' => 1, 'f_name' => 'Ana', 'l_name' => 'Tienda', 'email' => 'ana@example.test']);
        (require $this->app->basePath('database/migrations/2026_10_14_100000_create_anpec_surveys_tables.php'))->up();

        $request = Request::create('http://localhost/admin/surveys', 'POST');
        $request->setLaravelSession($this->session);
        $this->app->instance('request', $request);
        $this->router = new Router($this->app['events'], $this->app);
        Route::swap($this->router);
        $this->router->middleware('web')->group($this->app->basePath('routes/admin/routes.php'));
        $this->router->getRoutes()->refreshNameLookups();
        $url = new UrlGenerator($this->router->getRoutes(), $request);
        $redirector = new Redirector($url);
        $redirector->setSession($this->session);
        $this->app->instance('url', $url);
        $this->app->instance('redirect', $redirector);
        $this->app->instance(ResponseFactoryContract::class, new ResponseFactory($this->createMock(ViewFactoryContract::class), $redirector));
        (new FoundationServiceProvider($this->app))->registerRequestValidation();
        $this->service = new SurveyService();
        $this->controller = new SurveyController($this->service);
    }

    protected function tearDown(): void
    {
        $this->database->getConnection()->disconnect();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Application::setInstance(null);
        parent::tearDown();
    }

    public function testAdminStoreAcceptsOptionsByLinesAndOtherFlag(): void
    {
        $response = $this->controller->store(Request::create('/admin/surveys', 'POST', [
            'title' => 'Encuesta nueva', 'description' => 'Descripción', 'is_active' => '1',
            'questions' => [
                ['text' => 'Primera pregunta', 'options_text' => " Precio \r\n\r\n Variedad \nEntrega", 'allow_other' => '1'],
                ['text' => 'Segunda pregunta', 'options_text' => "Sí\rNo", 'allow_other' => '0'],
            ],
        ]));
        $survey = Survey::query()->latest('id')->firstOrFail();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/admin/surveys/' . $survey->id . '/responses', $response->getTargetUrl());
        $this->assertTrue($survey->is_active);
        $this->assertSame(['Precio', 'Variedad', 'Entrega'], $survey->questions[0]['options']);
        $this->assertTrue($survey->questions[0]['allow_other']);
        $this->assertSame(['Sí', 'No'], $survey->questions[1]['options']);
        $this->assertFalse($survey->questions[1]['allow_other']);
        $this->assertSame('Encuesta guardada correctamente.', $this->session->get('survey_success'));
    }

    public function testAdminStoreRejectsDuplicateOptionLines(): void
    {
        $this->expectException(ValidationException::class);
        $this->controller->store(Request::create('/admin/surveys', 'POST', [
            'title' => 'Inválida', 'is_active' => '1',
            'questions' => [['text' => 'Pregunta', 'options_text' => "Precio\n Precio ", 'allow_other' => '1']],
        ]));
    }

    public function testAdminStatusClosesAndPublishesSurvey(): void
    {
        $survey = Survey::query()->firstOrFail();
        $this->controller->status(Request::create('/admin/surveys/' . $survey->id . '/status', 'POST', ['is_active' => '0']), $survey);
        $this->assertFalse($survey->fresh()->is_active);
        $this->controller->status(Request::create('/admin/surveys/' . $survey->id . '/status', 'POST', ['is_active' => '1']), $survey);
        $this->assertTrue($survey->fresh()->is_active);
    }

    public function testCsvExportsNormalAndOtherAnswersAndEscapesFormulaCells(): void
    {
        Capsule::table('users')->where('id', 1)->update(['f_name' => '=Tienda', 'l_name' => '', 'email' => '@customer.example']);
        $survey = $this->service->create([
            'title' => 'Exportación', 'is_active' => true,
            'questions' => [
                ['text' => '=Pregunta', 'options' => ['=1+1', 'Normal'], 'allow_other' => false],
                ['text' => 'Respuesta libre', 'options' => ['A', 'B'], 'allow_other' => true],
            ],
        ]);
        $otherText = "Necesito \"crédito\", apoyo\ny capacitación";
        $this->service->submit(survey: $survey, customerId: 1, answers: [
            ['question_id' => 'q1', 'option' => '0'],
            ['question_id' => 'q2', 'option' => 'other', 'other_text' => $otherText],
        ]);
        $response = $this->controller->export($survey);
        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertSame('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        $input = fopen('php://memory', 'w+');
        fwrite($input, substr($csv, 3));
        rewind($input);
        $headers = fgetcsv($input, 0, ',', '"', '');
        $row = fgetcsv($input, 0, ',', '"', '');
        fclose($input);
        $this->assertSame("'=Pregunta", $headers[3]);
        $this->assertSame("'=Tienda", $row[0]);
        $this->assertSame("'@customer.example", $row[1]);
        $this->assertSame("'=1+1", $row[3]);
        $this->assertSame('Otros: ' . $otherText, $row[4]);
    }

    public function testAdminRoutesHaveWebAdminAndPeopleGuardsAndBindSurvey(): void
    {
        $survey = Survey::query()->firstOrFail();
        foreach ([
            ['GET', '/admin/surveys'], ['GET', '/admin/surveys/create'], ['POST', '/admin/surveys'],
            ['POST', '/admin/surveys/' . $survey->id . '/status'],
            ['GET', '/admin/surveys/' . $survey->id . '/responses'],
            ['GET', '/admin/surveys/' . $survey->id . '/export'],
        ] as [$method, $path]) {
            $route = $this->router->getRoutes()->match(Request::create($path, $method));
            $this->assertContains('web', $route->gatherMiddleware());
            $this->assertContains('admin', $route->gatherMiddleware());
            $this->assertContains('module:people', $route->gatherMiddleware());
            if ($route->hasParameter('survey')) {
                $this->router->substituteImplicitBindings($route);
                $this->assertInstanceOf(Survey::class, $route->parameter('survey'));
                $this->assertSame($survey->id, $route->parameter('survey')->id);
            }
        }
    }

    public function testSurveyPostWithoutCsrfTokenIsRejected(): void
    {
        $this->app->instance('env', 'production');
        $request = Request::create('/admin/surveys', 'POST', ['title' => 'Invalid']);
        $request->setLaravelSession($this->session);
        $middleware = new VerifyCsrfToken($this->app, new Encrypter(str_repeat('a', 32), 'AES-256-CBC'));
        $this->expectException(TokenMismatchException::class);
        $middleware->handle($request, fn () => null);
    }

    public function testSurveyViewsCompileToValidPhp(): void
    {
        $compiler = new BladeCompiler(new Filesystem(), $this->app->storagePath('framework/views'));
        foreach (['index', 'create', 'responses'] as $name) {
            $source = file_get_contents($this->app->basePath('resources/views/admin-views/surveys/' . $name . '.blade.php'));
            $compiled = $compiler->compileString($source);
            $this->assertNotEmpty(token_get_all($compiled, TOKEN_PARSE));
        }
    }
}
