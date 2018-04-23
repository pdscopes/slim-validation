<?php

namespace MadeSimple\Slim\Middleware\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class IntegrationMiddlewareTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $ci;

    /**
     * @var callable
     */
    private $next;

    /**
     * @var Request|\PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    protected function setUp()
    {
        parent::setUp();
        $this->ci = new Container();
        $this->ci['notFoundHandler'] = function () {
            return function ($request, $response) {
                return $response->withStatus(404);
            };
        };
        $this->ci['invalidRequestHandler'] = function () {
            return function ($request, $response) {
                return $response->withStatus(422);
            };
        };
        $this->next = function ($request, $response) {
            return $response;
        };
        $this->request  = $this->createMock(Request::class);
        $this->response = new Response();
    }



    public function testPathRulesSuccess()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['argument' => 4]]);

        $validation = new PathRulesValidation($this->ci);
        $response   = $validation($this->request, new Response(), $this->next);

        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testPathRulesFailure()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['argument' => 'value']]);

        $validation = new PathRulesValidation($this->ci);
        $response   = $validation($this->request, new Response(), $this->next);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testQueryRulesSuccess()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 2]]);
        $this->request->method('getQueryParams')->willReturn(['param' => 4]);

        $validation = new QueryParameterRulesValidation($this->ci);
        $response   = $validation($this->request, new Response(), $this->next);

        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testQueryRulesFailure()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 5]]);
        $this->request->method('getQueryParams')->willReturn(['param' => 4]);

        $validation = new QueryParameterRulesValidation($this->ci);
        $response   = $validation($this->request, new Response(), $this->next);

        $this->assertEquals(422, $response->getStatusCode());
    }

    public function testBodyRulesSuccess()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 2]]);
        $this->request->method('getQueryParams')->willReturn([]);
        $this->request->method('getParsedBody')->willReturn(['field' => 4]);

        $validation = new ParsedBodyRulesValidation($this->ci);
        $response   = $validation($this->request, new Response(), $this->next);

        $this->assertEquals(200, $response->getStatusCode());
    }
    public function testBodyRulesFailure()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 5]]);
        $this->request->method('getQueryParams')->willReturn([]);
        $this->request->method('getParsedBody')->willReturn(['field' => 4]);

        $validation = new ParsedBodyRulesValidation($this->ci);
        $response   = $validation($this->request, new Response(), $this->next);

        $this->assertEquals(422, $response->getStatusCode());
    }
}