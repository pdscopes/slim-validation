<?php

namespace MadeSimple\Slim\Middleware;

use Psr\Container\ContainerInterface;
use MadeSimple\Validator\Validator;
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
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $validator = new Validator();

        // Validate the request
        $routeInfo = $request->getAttribute('routeInfo');
        $routeArgs = $routeInfo[2];

        $validator->validate($routeArgs, $this->getPathRules());
        // If path validation failed
        if ($validator->hasErrors()) {
            return $this->invalidPathResponse($response);
        }

        $validator->validate($request->getQueryParams(), $this->getQueryParameterRules($routeArgs));
        // If query parameter validation failed
        if ($validator->hasErrors()) {
            return $this->invalidBodyResponse($response, $validator->getProcessedErrors());
        }

        $validator->validate($request->getParsedBody(), $this->getParsedBodyRules($routeArgs));
        // If parsed body validation failed
        if ($validator->hasErrors()) {
            return $this->invalidBodyResponse($response, $validator->getProcessedErrors());
        }

        return $next($request, $response);
    }

    /**
     * 404 status code with a JSON encoded body of the errors.
     *
     * @param Response  $response
     *
     * @return Response
     */
    protected function invalidPathResponse(Response $response)
    {
        return $response
            ->withStatus(404)
            ->withJson(['code' => 404, 'message' => 'Not Found']);
    }

    /**
     * 422 status code with a JSON encoded body of the errors.
     *
     * @param Response  $response
     * @param array     $errors
     *
     * @return Response
     */
    protected function invalidBodyResponse(Response $response, array $errors)
    {
        return $response
            ->withStatus(422)
            ->withJson(['code' => 422] + $errors);
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