<?php

namespace MadeSimple\Slim\Middleware;

use MadeSimple\Validator\Validator;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteContext;

class InlineValidation
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
     * @param array $rules
     * @return InlineValidation
     * @throws HttpNotFoundException
     */
    public function validatePath(ServerRequestInterface $request, array $rules): InlineValidation
    {
        $validator = $this->getValidator();

        // Get all of the route's parsed arguments e.g. ['name' => 'John']
        $routeArguments = $this->getRouteArguments($request);

        // Validate the request path
        $validator->validate($routeArguments, $rules);
        if ($validator->hasErrors()) {
            throw new HttpNotFoundException($request);
        }

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $rules
     * @return InlineValidation
     * @throws HttpUnprocessableEntityException
     */
    public function validateQuery(ServerRequestInterface $request, array $rules): InlineValidation
    {
        $validator = $this->getValidator();

        // Validate the request query parameters
        $validator->validate($request->getQueryParams(), $rules);
        if ($validator->hasErrors()) {
            throw new HttpUnprocessableEntityException($request, $validator->getProcessedErrors());
        }

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $rules
     * @return InlineValidation
     * @throws HttpUnprocessableEntityException
     */
    public function validateParsedBody(ServerRequestInterface $request, array $rules): InlineValidation
    {
        $validator = $this->getValidator();

        // Validate the request parsed body
        $validator->validate($request->getParsedBody(), $rules);
        if ($validator->hasErrors()) {
            throw new HttpUnprocessableEntityException($request, $validator->getProcessedErrors());
        }

        return $this;
    }

    /**
     * @return Validator
     */
    public function getValidator(): Validator
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
}