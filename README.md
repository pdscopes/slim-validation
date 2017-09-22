# slim-validation
Abstract Slim middleware to allow request simple validation.

Simply extend the class for each route you want to validate, write the
rules, and add the middleware to the route:
```php
<?php
// Request class
namespace Requests;

class FooPostRequest extends \MadeSimple\Slim\Middleware\Validation
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
```php
<?php
// Routes

$app->post('/route/path/foo', \Controllers\Foo::class . ':post')
    ->add(\Requests\FooPostRequest::class);
```


If the request fails validation of the path rules then check the container for a `notFoundHandler` otherwise throw `Slim\Exception\NotFoundException`. If there is a handler then `notFoundHandler($request, $response)` is called.
If the request fails validation of either query parameters or parsed body rules then check the container for an `invalidRequestHandler` otherwise throw `MadeSimple\Slim\Middleware\InvalidRequestException`. If there is a handler then `invalidRequestHandler($request, $response, $errors)` is called.

## Official Documentation
Simple Validator: https://github.com/pdscopes/php-form-validator