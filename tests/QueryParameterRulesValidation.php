<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;

class QueryParameterRulesValidation extends Validation
{
    protected  function getPathRules(): array
    {
        return [];
    }
    protected  function getQueryParameterRules(array $routeArguments): array
    {
        return [
            'param' => 'is:numeric|min:' . $routeArguments['minimum']
        ];
    }
    protected  function getParsedBodyRules(array $routeArguments): array
    {
        return [];
    }
}