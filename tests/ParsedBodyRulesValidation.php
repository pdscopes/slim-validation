<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;

class ParsedBodyRulesValidation extends Validation
{
    protected  function getPathRules()
    {
        return [];
    }
    protected  function getQueryParameterRules(array $routeArguments)
    {
        return [];
    }
    protected  function getParsedBodyRules(array $routeArguments)
    {
        return [
            'field' => 'is:int|min:' . $routeArguments['minimum']
        ];
    }
}