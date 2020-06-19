<?php

namespace MadeSimple\Slim\Middleware\Tests;

use MadeSimple\Slim\Middleware\Validation;
use Psr\Http\Message\ServerRequestInterface as Request;

class PathRulesValidation extends Validation
{
    protected  function getPathRules(): array
    {
        return [
            'argument' => 'is:numeric',
        ];
    }
    protected  function getQueryParameterRules(Request $request): array
    {
        return [];
    }
    protected  function getParsedBodyRules(Request $request): array
    {
        return [];
    }
}