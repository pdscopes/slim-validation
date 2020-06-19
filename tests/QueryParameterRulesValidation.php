<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;
use Psr\Http\Message\ServerRequestInterface as Request;

class QueryParameterRulesValidation extends Validation
{
    protected  function getPathRules(): array
    {
        return [];
    }
    protected  function getQueryParameterRules(Request $request): array
    {
        return [
            // If using validation as a function then the request has the minimum attribute
            // If using validation as middleware then the route arguments need to be extracted from the routing results
            'param' => 'is:numeric|min:' . ($request->getAttribute('minimum') ?? $this->getRouteArguments($request)['minimum'])
        ];
    }
    protected  function getParsedBodyRules(Request $request): array
    {
        return [];
    }
}