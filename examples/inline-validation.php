<?php

require __DIR__ . '/../vendor/autoload.php';


use \MadeSimple\Slim\Middleware\HttpUnprocessableEntityException;
use \MadeSimple\Slim\Middleware\InlineValidation;

// Set up
$requests = [
    // This request has an invalid body, will return 422
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('PUT', '/users/00000000-0000-0000-0000-000000000000')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody([
            'password' => '123abc',
            'confirm'  => 'abc123',
        ]),
    // This request path is invalid, will return 404
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('PUT', '/users/not-uuid')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody(new \stdClass()),
    // This request body is valid, will return 204
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('PUT', '/users/00000000-0000-0000-0000-000000000000')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody([
            'password' => '123abc',
            'confirm'  => '123abc',
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

$app->put('/users/{locator}', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    (new InlineValidation($this))
        ->validatePath($request, [
            'locator' => 'uuid'
        ])
        ->validateParsedBody($request, [
            'password'  => 'required-with:confirm',
            'confirm'   => 'required-with:password|equals:password',
        ]);

    return $response->withStatus(204);
});

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