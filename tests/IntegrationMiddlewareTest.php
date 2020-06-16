<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\HttpUnprocessableEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

class IntegrationMiddlewareTest extends TestCase
{
    /**
     * @var \Slim\App
     */
    private $app;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up the application
        $this->app = AppFactory::create(null, new TestContainer());
        $this->app->get('/{minimum}/{argument}', function (ServerRequestInterface $request, ResponseInterface $response) {
            $response->getBody()->write('success');
            return $response->withStatus(200);
        });
    }

    public function testPathRulesSuccess()
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/2/4');

        $this->app->add(new PathRulesValidation($this->app->getContainer()));
        $this->app->addRoutingMiddleware();
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testPathRulesFailure()
    {
        $this->expectException(HttpNotFoundException::class);
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/2/abc');

        $this->app->add(new PathRulesValidation($this->app->getContainer()));
        $this->app->addRoutingMiddleware();
        $this->app->handle($request);
    }

    public function testQueryRulesSuccess()
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/2/4?param=2');

        $this->app->add(new QueryParameterRulesValidation($this->app->getContainer()));
        $this->app->addRoutingMiddleware();
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testQueryRulesFailure()
    {
        $this->expectException(HttpUnprocessableEntityException::class);
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/5/4?param=4');

        $this->app->add(new QueryParameterRulesValidation($this->app->getContainer()));
        $this->app->addRoutingMiddleware();
        $this->app->handle($request);
    }

    public function testBodyRulesSuccess()
    {
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/2/4')
            ->withParsedBody(['field' => 4]);

        $this->app->add(new ParsedBodyRulesValidation($this->app->getContainer()));
        $this->app->addRoutingMiddleware();
        $response = $this->app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testBodyRulesFailure()
    {
        $this->expectException(HttpUnprocessableEntityException::class);
        $request = (new ServerRequestFactory())->createServerRequest('GET', '/5/4')
            ->withParsedBody(['field' => 4]);

        $this->app->add(new ParsedBodyRulesValidation($this->app->getContainer()));
        $this->app->addRoutingMiddleware();
        $this->app->handle($request);
    }
}