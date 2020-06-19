<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\HttpUnprocessableEntityException;
use MadeSimple\Slim\Middleware\Validation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Psr7\Factory\ServerRequestFactory;

class IntegrationFunctionTest extends TestCase
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
     * @param string $className
     * @param array $methods
     * @return Validation|MockObject
     */
    protected function stubValidation(string $className, array $methods = [])
    {
        return $this->getMockForAbstractClass(
            $className,
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
     * @throws HttpUnprocessableEntityException
     */
    public function testPathRulesSuccess()
    {
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/');
        $validation = $this->stubValidation(PathRulesValidation::class, ['getRouteArguments']);
        $validation
            ->expects($this->once())
            ->method('getRouteArguments')
            ->with($request)
            ->willReturn(['argument' => 4]);

        $validator = $validation->validate($request);
        $this->assertFalse($validator->hasErrors());
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     */
    public function testPathRulesFailure()
    {
        $this->expectException(HttpNotFoundException::class);
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/');
        $validation = $this->stubValidation(PathRulesValidation::class, ['getRouteArguments']);
        $validation
            ->expects($this->once())
            ->method('getRouteArguments')
            ->with($request)
            ->willReturn(['argument' => 'value']);

        $validation->validate($request);
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     */
    public function testQueryRulesSuccess()
    {
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/?param=4');
        $validation = $this->stubValidation(QueryParameterRulesValidation::class, ['getRouteArguments']);
        $validation
            ->expects($this->exactly(2))
            ->method('getRouteArguments')
            ->with($request)
            ->willReturn(['minimum' => 2]);

        $validator  = $validation->validate($request);
        $this->assertFalse($validator->hasErrors());
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     */
    public function testQueryRulesFailure()
    {
        $this->expectException(HttpUnprocessableEntityException::class);
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/?param=4');
        $validation = $this->stubValidation(QueryParameterRulesValidation::class, ['getRouteArguments']);

        $validation->validate($request->withAttribute('minimum', 5));
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     */
    public function testBodyRulesSuccess()
    {
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['field' => 4]);
        $validation = $this->stubValidation(ParsedBodyRulesValidation::class, ['getRouteArguments']);
        $validation
            ->expects($this->exactly(2))
            ->method('getRouteArguments')
            ->with($request)
            ->willReturn(['minimum' => 2]);

        $validator  = $validation->validate($request);
        $this->assertFalse($validator->hasErrors());
    }

    /**
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     */
    public function testBodyRulesFailure()
    {
        $this->expectException(HttpUnprocessableEntityException::class);
        $request    = (new ServerRequestFactory())->createServerRequest('POST', '/')
            ->withParsedBody(['field' => 4]);
        $validation = $this->stubValidation(ParsedBodyRulesValidation::class, ['getRouteArguments']);

        $validation->validate($request->withAttribute('minimum', 5));
    }
}