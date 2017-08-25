# slim-valiadation
Abstract Slim middleware to allow request simple validation.

Simply extend the class for each route you want to validate, write the
rules, and add the middleware to the route:
```php
<?php
// Request class
namespace Requests;

class FooRequest extends \MadeSimple\Slim\Middleware\Validation
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
use Controllers;
use Requests;

$app->post('/route/path/foo', Controllers\Foo::class . ':foo')
    ->add(Requests\FooRequest::class);
```

If the request fails validation then a 422 response is automatically returned.

## Official Documentation
Simple Validator: https://github.com/cangelis/simple-validator