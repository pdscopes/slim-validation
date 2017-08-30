<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;

class QueryParameterRulesValidation extends Validation
{
    protected  function getPathRules()
    {
        return [];
    }
    protected  function getQueryParameterRules(array $routeArguments)
    {
        return [
            'param' => 'is:int|min:' . $routeArguments['minimum']
        ];
    }
    protected  function getParsedBodyRules(array $routeArguments)
    {
        return [];
    }
}