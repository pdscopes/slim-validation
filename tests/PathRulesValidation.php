<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;

class PathRulesValidation extends Validation
{
    protected  function getPathRules(): array
    {
        return [
            'argument' => 'is:numeric',
        ];
    }
    protected  function getQueryParameterRules(array $routeArguments): array
    {
        return [];
    }
    protected  function getParsedBodyRules(array $routeArguments): array
    {
        return [];
    }
}