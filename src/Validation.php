<?php

namespace MadeSimple\Slim\Middleware;

use Psr\Container\ContainerInterface;
use MadeSimple\Validator\Validator;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Validation
 *
 * @package MadeSimple\Slim\Middleware
 * @author  Peter Scopes <peter.scopes@gmail.com>
 */
abstract class Validation
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
     * @param Request  $request
     * @param Response $response
     * @param \Closure $next
     * @throws NotFoundException
     * @throws InvalidRequestException
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $validator = $this->getValidator();

        // Validate the request
        $routeInfo = $request->getAttribute('routeInfo');
        $routeArgs = $routeInfo[2];

        $validator->validate($routeArgs, $this->getPathRules());
        // If path validation failed
        if ($validator->hasErrors()) {
            if (!$this->ci->has('notFoundHandler')) {
                throw new NotFoundException($request, $response);
            }
            return $this->ci['notFoundHandler']($request, $response);
        }

        $validator->validate($request->getQueryParams(), $this->getQueryParameterRules($routeArgs));
        // If query parameter validation failed
        if ($validator->hasErrors()) {
            if (!$this->ci->has('invalidRequestHandler')) {
                throw new InvalidRequestException($request, $response, $validator->getProcessedErrors());
            }
            return $this->ci['invalidRequestHandler']($request, $response, $validator->getProcessedErrors());
        }

        $validator->validate($request->getParsedBody(), $this->getParsedBodyRules($routeArgs));
        // If parsed body validation failed
        if ($validator->hasErrors()) {
            if (!$this->ci->has('invalidRequestHandler')) {
                throw new InvalidRequestException($request, $response, $validator->getProcessedErrors());
            }
            return $this->ci['invalidRequestHandler']($request, $response, $validator->getProcessedErrors());
        }

        return $next($request, $response);
    }

    /**
     * @param \Slim\Http\Request  $request
     * @param \Slim\Http\Response $response
     * @return \MadeSimple\Validator\Validator
     * @throws \MadeSimple\Slim\Middleware\InvalidRequestException
     * @throws \Slim\Exception\NotFoundException
     */
    public function validate(Request $request, Response $response)
    {
        // Extract the route arguments
        $routeInfo = $request->getAttribute('routeInfo');
        $routeArgs = $routeInfo[2];

        $validator = $this->getValidator();

        // Validate the request path
        $validator->validate($routeArgs, $this->getPathRules());
        if ($validator->hasErrors()) {
            throw new NotFoundException($request, $response);
        }

        // Validate the request query parameters
        $validator->validate($request->getQueryParams(), $this->getQueryParameterRules($routeArgs));
        if ($validator->hasErrors()) {
            throw new InvalidRequestException($request, $response, $validator->getProcessedErrors());
        }

        // Validate the request parsed body
        $validator->validate($request->getParsedBody(), $this->getParsedBodyRules($routeArgs));
        if ($validator->hasErrors()) {
            throw new InvalidRequestException($request, $response, $validator->getProcessedErrors());
        }

        return $validator;
    }

    /**
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->ci->has('validator') ? $this->ci['validator'] : new Validator();
    }

    /**
     * @return array Rule set for the request path.
     */
    protected abstract function getPathRules();

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the query parameters.
     */
    protected abstract function getQueryParameterRules(array $routeArguments);

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the parsed body.
     */
    protected abstract function getParsedBodyRules(array $routeArguments);
}