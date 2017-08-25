<?php

namespace MadeSimple\Slim\Middleware;

use Psr\Container\ContainerInterface;
use SimpleValidator\Validator;
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
        // Validate the request
        $routeArgs = $request->getAttribute('route')->getArguments();

        $result = Validator::validate($routeArgs, $this->getPathRules());
        // If path validation failed
        if (!$result->isSuccess()) {
            return $this->invalidPathResponse($response, $result->getErrors());
        }

        $result = Validator::validate($request->getQueryParams(), $this->getQueryParameterRules($routeArgs));
        // If query parameter validation failed
        if (!$result->isSuccess()) {
            return $this->invalidBodyResponse($response, $result->getErrors());
        }
        $result = Validator::validate($request->getParsedBody(), $this->getParsedBodyRules($routeArgs));
        // If parsed body validation failed
        if (!$result->isSuccess()) {
            return $this->invalidBodyResponse($response, $result->getErrors());
        }

        return $next($request, $response);
    }

    /**
     * 404 status code with a JSON encoded body of the errors.
     *
     * @param Response  $response
     * @param array     $errors
     *
     * @return Response
     */
    protected function invalidPathResponse(Response $response, array $errors)
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
            ->withJson(['code' => 422, 'errors' => $errors]);
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