# MadeSimple: slim-validation
[![Build Status](https://travis-ci.org/pdscopes/slim-validation.svg?branch=master)](https://travis-ci.org/pdscopes/slim-validation)

Slim Validation is an abstract class to be extended to perform per route validation.
Slim Validation allows two types of validation models:

* Validation as a function
* Validation as a middleware

Validation as a function is the recommended use case.
Since Slim v4 has become more flexible there is no longer a straight forward method for extracting the request arguments
as a middleware; this means that `getPathRules` checks are no longer supported by the validation as a middleware.

In both cases simply extend the abstract class `\MadeSimple\Slim\Middleware\Validation` and implement the three abstract methods:

1. `getPathRules` - allows you to validate the request path.
2. `getQueryParameterRules` - allows you to validate the query parameters of the request.
3. `getParsedBodyRules` - allows you to validate the parsed body of the request.

Slim Validation considers requests which violate the path rules as `404 Not Found`. Requests which violate either query
parameter or parsed body rules should return as `422 Unprocessable Entity`.

For example:
```php
<?php
// Request class
namespace Requests;

class ModelRequestValidation extends \MadeSimple\Slim\Middleware\Validation
{
    /**
     * @return array Rule set for the request path.
     */
    protected function getPathRules(): array
    {
        return [
            // The model locator must be a uuid and pass your custom acl check
            'locator' => 'uuid|custom_acl_check'
        ];
    }

    protected function getQueryParameterRules(array $routeArguments): array
    {
        return [];
    }

    protected function getParsedBodyRules(array $routeArguments): array
    {
        return [];
    }
}

class PaginatedRequestValidation extends \MadeSimple\Slim\Middleware\Validation
{
    protected function getPathRules(): array
    {
        return [];
    }

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the query parameters.
     */
    protected function getQueryParameterRules(array $routeArguments): array
    {
        return [
            // The page must be at least 1
            'page'  => 'is:int|min:1',
            // The items per page must at least 5 but no more than 100
            'limit' => 'is:int|min:5|max:100',
        ];
    }

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the parsed body.
     */
    protected function getParsedBodyRules(array $routeArguments): array
    {
        return [];
    }
}

class PostRequestValidation extends \MadeSimple\Slim\Middleware\Validation
{
    protected function getPathRules(): array
    {
        return [];
    }

    protected function getQueryParameterRules(array $routeArguments): array
    {
        return [];
    }

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the parsed body.
     */
    protected function getParsedBodyRules(array $routeArguments): array
    {
        return [
            // The current password is required
            'current_password' => 'required',
            // The updated password is required and must meet your custom password strength test
            'updated_password' => 'required|custom_password_strength_test',
            // The confirm password is required and must equal the `updated_password` property
            'confirm_password' => 'required|equals:updated_password',
        ];
    }
}
```


## Validation as a function
Validation as a function is the recommended method.
If the request fails path rules validation then a `Slim\Exception\HttpNotFoundException` is thrown.
If the request fails query parameter or parsed body validation then a `MadeSimple\Slim\Middleware\InvalidRequestException` is thrown.
To use validation as a function you create the validation class as above and call the `validate` method in the route's callable, for example:
```php
<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

//...
$app = new \Slim\App;
//...

$app->post('/route/path/foo', function (Request $request, Response $response) {
    // Validate the request immediately, if it is invalid then it will throw an exception
    (new \Request\FooPostValidation($this))->validate($request);

    // ...
});

```


## Validation as a middleware
To use validation as a middleware you create the validation class as above and add it as a middleware for the routes you wish to apply that validation to, for example:
```php
<?php
// Routes

$app->post('/route/path/foo', \Controller\Foo::class . ':post')
    ->add(\Request\FooPostValidation::class);
```

If the request fails validation of either query parameters or parsed body rules then check the container for an `invalidRequestHandler` otherwise throw `MadeSimple\Slim\Middleware\InvalidRequestException`. If there is a handler then `invalidRequestHandler($request, $response, $errors)` is called.




## Official Documentation
* Simple Validator: https://github.com/pdscopes/php-form-validator
* Slim Framework: https://www.slimframework.com/
