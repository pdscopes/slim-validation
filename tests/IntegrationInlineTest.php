<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\HttpUnprocessableEntityException;
use MadeSimple\Slim\Middleware\InlineValidation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ServerRequestFactory;

class IntegrationInlineTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    private $ci;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ci = new TestContainer();
    }

    /**
     * @param array $methods
     * @return InlineValidation|MockObject
     */
    protected function stubValidation(array $methods = [])
    {
        return $this->getMockForAbstractClass(
            InlineValidation::class,
            [$this->ci],
            '',
            true,
            true,
            true,
            $methods
        );
    }

    /**
     * @throws HttpNotFoundException
     */
    public function testPathRulesSuccess()
    {
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/');
        $validation = $this->stubValidation(['getRouteArguments']);
        $validation
            ->expects($this->once())
            ->method('getRouteArguments')
            ->with($request)
            ->willReturn(['argument' => 4]);

        $validation->validatePath($request, [
            'argument' => 'is:numeric',
        ]);
        $this->assertFalse($validation->getValidator()->hasErrors());
    }

    /**
     * @throws HttpNotFoundException
     */
    public function testPathRulesFailure()
    {
        $this->expectException(HttpNotFoundException::class);
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/');
        $validation = $this->stubValidation(['getRouteArguments']);
        $validation
            ->expects($this->once())
            ->method('getRouteArguments')
            ->with($request)
            ->willReturn(['argument' => 'value']);

        $validation->validatePath($request, [
            'argument' => 'is:numeric',
        ]);
    }

    /**
     * @throws HttpUnprocessableEntityException
     */
    public function testQueryRulesSuccess()
    {
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/?param=4');
        $routeArguments = ['minimum' => 2];
        $validation = $this->stubValidation();

        $validation->validateQuery($request, [
            'param' => 'is:numeric|min:' . $routeArguments['minimum']
        ]);
        $this->assertFalse($validation->getValidator()->hasErrors());
    }

    /**
     * @throws HttpUnprocessableEntityException
     */
    public function testQueryRulesFailure()
    {
        $this->expectException(HttpUnprocessableEntityException::class);
        $routeArguments = ['minimum' => 5];
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/?param=4');
        $validation = $this->stubValidation();

        $validation->validateQuery($request, [
            'param' => 'is:numeric|min:' . $routeArguments['minimum']
        ]);
    }

    /**
     * @throws HttpUnprocessableEntityException
     */
    public function testBodyRulesSuccess()
    {
        $routeArguments = ['minimum' => 2];
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['field' => 4]);
        $validation = $this->stubValidation();

        $validation->validateParsedBody($request, [
            'field' => 'is:int|min:' . $routeArguments['minimum']
        ]);
        $this->assertFalse($validation->getValidator()->hasErrors());
    }

    /**
     * @throws HttpUnprocessableEntityException
     */
    public function testBodyRulesFailure()
    {
        $this->expectException(HttpUnprocessableEntityException::class);
        $routeArguments = ['minimum' => 5];
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['field' => 4]);
        $validation = $this->stubValidation();

        $validation->validateParsedBody($request, [
            'field' => 'is:int|min:' . $routeArguments['minimum']
        ]);
    }
}