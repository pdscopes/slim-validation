<?php

namespace MadeSimple\Slim\Middleware\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class IntegrationFunctionTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $ci;

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
        $this->request  = $this->createMock(Request::class);
        $this->response = new Response();
    }



    public function testPathRulesSuccess()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['argument' => 4]]);

        $validation = new PathRulesValidation($this->ci);
        $validator  = $validation->validate($this->request, new Response());

        $this->assertFalse($validator->hasErrors());
    }
    /**
     * @expectedException \Slim\Exception\NotFoundException
     */
    public function testPathRulesFailure()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['argument' => 'value']]);

        $validation = new PathRulesValidation($this->ci);
        $validation->validate($this->request, new Response());
    }

    public function testQueryRulesSuccess()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 2]]);
        $this->request->method('getQueryParams')->willReturn(['param' => 4]);

        $validation = new QueryParameterRulesValidation($this->ci);
        $validator  = $validation->validate($this->request, new Response());

        $this->assertFalse($validator->hasErrors());
    }
    /**
     * @expectedException \MadeSimple\Slim\Middleware\InvalidRequestException
     */
    public function testQueryRulesFailure()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 5]]);
        $this->request->method('getQueryParams')->willReturn(['param' => 4]);

        $validation = new QueryParameterRulesValidation($this->ci);
        $validation->validate($this->request, new Response());
    }

    public function testBodyRulesSuccess()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 2]]);
        $this->request->method('getQueryParams')->willReturn([]);
        $this->request->method('getParsedBody')->willReturn(['field' => 4]);

        $validation = new ParsedBodyRulesValidation($this->ci);
        $validator  = $validation->validate($this->request, new Response());

        $this->assertFalse($validator->hasErrors());
    }
    /**
     * @expectedException \MadeSimple\Slim\Middleware\InvalidRequestException
     */
    public function testBodyRulesFailure()
    {
        $this->request->method('getAttribute')->willReturn([2 => ['minimum' => 5]]);
        $this->request->method('getQueryParams')->willReturn([]);
        $this->request->method('getParsedBody')->willReturn(['field' => 4]);

        $validation = new ParsedBodyRulesValidation($this->ci);
        $validation->validate($this->request, new Response());
    }
}