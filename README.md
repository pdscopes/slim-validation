# MadeSimple: slim-validation
[![Build Status](https://travis-ci.org/pdscopes/slim-validation.svg?branch=master)](https://travis-ci.org/pdscopes/slim-validation)

Slim Validation is an abstract class to be extended to perform per route validation. Slim Validation allows two types of validation models:

* Valiation as a middleware
* Validation as a function

In both cases simply extend the abstract class `\MadeSimple\Slim\Middleware\Validation` and implement the three abstract methods:

1. `getPathRules` - allows you to validate the request path. Paths with invalid rules are considered as not found.
2. `getQueryParameterRules` - allows you to validate the query parameters of the request.
3. `getParsedBodyRules` - allows you to validate the parsed body of the request.

For example:
```php
<?php
// Request class
namespace Requests;

class FooPostValidation extends \MadeSimple\Slim\Middleware\Validation
{
    /**
     * @return array Rule set for the request path.
     */
    protected function getPathRules()
    {
        return [];
    }

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the query parameters.
     */
    protected function getQueryParameterRules(array $routeArguments)
    {
        return [];
    }

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the parsed body.
     */
    protected function getParsedBodyRules(array $routeArguments)
    {
        return [];
    }
}
```


## Validation as a middleware
To use validation as a middleware you create the validation class as above then add it as a middleware for the routes you wish to apply that validation to, for example:
```php
<?php
// Routes

$app->post('/route/path/foo', \Controllers\Foo::class . ':post')
    ->add(\Requests\FooPostValidation::class);
```

If the request fails validation of the path rules then check the container for a `notFoundHandler` otherwise throw `Slim\Exception\NotFoundException`. If there is a handler then `notFoundHandler($request, $response)` is called.
If the request fails validation of either query parameters or parsed body rules then check the container for an `invalidRequestHandler` otherwise throw `MadeSimple\Slim\Middleware\InvalidRequestException`. If there is a handler then `invalidRequestHandler($request, $response, $errors)` is called.


## Validation as a function
To use validation as a function you create the validation class as above
then call the `validate` method in the route's callable, for example:
```php
<?php

//...
$app = new \Slim\App;
//...

$app->post('/route/path/foo', function ($request, $response) {
    (new FooPostValidation($this))->validate($request, $response);

    // ...
});

```

If the request fails path rules validation then a `Slim\Exception\NotFoundException` is thrown.
If the request fails query parameter or parsed body validation then a `MadeSimple\Slim\Middleware\InvalidRequestException` is thrown.


## Official Documentation
* Simple Validator: https://github.com/pdscopes/php-form-validator
* Slim Framework: https://www.slimframework.com/
