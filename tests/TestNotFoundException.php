<?php

namespace MadeSimple\Slim\Middleware\Tests;

use Psr\Container\NotFoundExceptionInterface;

class TestNotFoundException extends \RuntimeException implements NotFoundExceptionInterface
{

}