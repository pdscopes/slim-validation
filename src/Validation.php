<?php

namespace MadeSimple\Slim\Middleware;

use Psr\Container\ContainerInterface;
use MadeSimple\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

/**
 * Class Validation
 *
 * @package MadeSimple\Slim\Middleware
 * @author  Peter Scopes <peter.scopes@gmail.com>
 */
abstract class Validation implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $ci;

    /**
     * Middleware constructor.
     *
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     * @see Validation::process()
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->process($request, $handler);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->validate($request);

        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return Validator
     * @throws HttpNotFoundException
     * @throws HttpUnprocessableEntityException
     */
    public function validate(ServerRequestInterface $request): Validator
    {
        $validator = $this->getValidator();

        // Get all of the route's parsed arguments e.g. ['name' => 'John']
        $routeArguments = $this->getRouteArguments($request);

        // Validate the request path
        $validator->validate($routeArguments, $this->getPathRules());
        if ($validator->hasErrors()) {
            throw new HttpNotFoundException($request);
        }

        // Validate the request query parameters
        $validator->validate($request->getQueryParams(), $this->getQueryParameterRules($routeArguments));
        if ($validator->hasErrors()) {
            throw new HttpUnprocessableEntityException($request, $validator->getProcessedErrors());
        }

        // Validate the request parsed body
        $validator->validate($request->getParsedBody(), $this->getParsedBodyRules($routeArguments));
        if ($validator->hasErrors()) {
            throw new HttpUnprocessableEntityException($request, $validator->getProcessedErrors());
        }

        return $validator;
    }

    /**
     * @return Validator
     */
    protected function getValidator(): Validator
    {
        return $this->ci->has('validator') ? $this->ci->get('validator') : new Validator();
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getRouteArguments(ServerRequestInterface $request): array
    {
        $routeContext = RouteContext::fromRequest($request);
        $routingResults = $routeContext->getRoutingResults();
        return $routingResults->getRouteArguments();
    }

    /**
     * @return array Rule set for the request path.
     */
    protected abstract function getPathRules(): array;

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the query parameters.
     */
    protected abstract function getQueryParameterRules(array $routeArguments): array;

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the parsed body.
     */
    protected abstract function getParsedBodyRules(array $routeArguments): array;
}