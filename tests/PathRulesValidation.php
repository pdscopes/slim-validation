<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;

class PathRulesValidation extends Validation
{
    protected  function getPathRules()
    {
        return [
            'argument' => 'is:int',
        ];
    }
    protected  function getQueryParameterRules(array $routeArguments)
    {
        return [];
    }
    protected  function getParsedBodyRules(array $routeArguments)
    {
        return [];
    }
}