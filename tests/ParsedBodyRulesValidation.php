<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;

class ParsedBodyRulesValidation extends Validation
{
    protected  function getPathRules(): array
    {
        return [];
    }
    protected  function getQueryParameterRules(array $routeArguments): array
    {
        return [];
    }
    protected  function getParsedBodyRules(array $routeArguments): array
    {
        return [
            'field' => 'is:int|min:' . $routeArguments['minimum']
        ];
    }
}