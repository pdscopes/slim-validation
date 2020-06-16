<?php

require __DIR__ . '/../vendor/autoload.php';

use \MadeSimple\Slim\Middleware\HttpUnprocessableEntityException;
use \MadeSimple\Slim\Middleware\Validation;

class UserPutValidation extends Validation
{
    /**
     * @return array Rule set for the request path.
     */
    protected function getPathRules(): array
    {
        return [
            'post' => 'is:numeric|min:1'
        ];
    }

    /**
     * @param array $routeArguments Route arguments
     *
     * @return array Rule set for the query parameters.
     */
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
            'firstName' => 'required|is:string|min-str-len:1|human-name',
            'lastName'  => 'required|is:string|min-str-len:1|human-name',
            'password'  => 'required-with:confirm',
            'confirm'   => 'required-with:password|equals:password',
            'preferences.emails'  => 'in:yes,no',
            'access.submission'   => 'present|is:array',
            'access.submission.*' => 'is:int|min:1',
            'access.report'       => 'present|is:array',
            'access.report.*'     => 'is:int|min:1',
        ];
    }
}

// Set up
$requests = [
    // This request has an invalid body, will return 422
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('PUT', '/post/15/comment')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody([
            'firstName' => 'Joe',
            'lastName'  => 'Bloggs',
            'password'  => '',
            'confirm'   => '',
            'preferences' => ['emails' => 'yes'],
            'access'    => [
                'report' => [2],
            ],
        ]),
    // This request path is invalid, will return 404
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('PUT', '/post/abc/comment')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody(new \stdClass()),
    // This request body is valid, will return 204
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('PUT', '/post/15/comment')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody([
            'firstName' => 'Joe',
            'lastName'  => 'Bloggs',
            'password'  => '',
            'confirm'   => '',
            'preferences' => ['emails' => 'yes'],
            'access'    => [
                'submission' => [3],
                'report' => [2],
            ],
        ]),
];

\Slim\Factory\AppFactory::setContainer(new \DI\Container());
$app = \Slim\Factory\AppFactory::create();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorMiddleware->setErrorHandler(
    [HttpUnprocessableEntityException::class, \Slim\Exception\HttpNotFoundException::class],
    function (\Psr\Http\Message\ServerRequestInterface $request, Throwable $exception, bool $displayErrorDetails) {
        $response = new \Slim\Psr7\Response();
        $jsonBody = [];
        if ($displayErrorDetails) {
            $jsonBody['message'] = $exception->getMessage();
        }
        if ($exception instanceof HttpUnprocessableEntityException) {
            $jsonBody = array_merge($jsonBody, $exception->getErrors());
        }
        $response->getBody()->write(json_encode($jsonBody));
        return $response->withStatus($exception->getCode());
    }
);

$app->put('/post/{post}/comment', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    return $response->withStatus(204);
})->add(new UserPutValidation($app->getContainer()));

/** @var \Psr\Http\Message\ServerRequestInterface $request */
foreach ($requests as $request) {
    $response = $app->handle($request);

    $request->getBody()->write(json_encode($request->getParsedBody()));
    $response->getBody()->rewind();
    echo <<<EOT
<<<<<<<<<< REQUEST
{$request->getMethod()} {$request->getRequestTarget()}
{$request->getBody()}

>>>>>>>>>> RESPONSE
Code:   {$response->getStatusCode()}
Reason: {$response->getReasonPhrase()}
Body:
{$response->getBody()->getContents()}


EOT;
}
