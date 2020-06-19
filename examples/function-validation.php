<?php

require __DIR__ . '/../vendor/autoload.php';

use MadeSimple\Slim\Middleware\HttpUnprocessableEntityException;
use MadeSimple\Slim\Middleware\Validation;
use Psr\Http\Message\ServerRequestInterface as Request;

class SessionPostValidation extends Validation
{
    /**
     * @return array Rule set for the request path.
     */
    protected function getPathRules(): array
    {
        return [
        ];
    }

    /**
     * @param Request $request
     *
     * @return array Rule set for the query parameters.
     */
    protected function getQueryParameterRules(Request $request): array
    {
        return [];
    }

    /**
     * @param Request $request
     *
     * @return array Rule set for the parsed body.
     */
    protected function getParsedBodyRules(Request $request): array
    {
        return [
            'username' => 'required|email',
            'password' => 'required-with:username|min-str-len:3',
        ];
    }
}

// Set up
$requests = [
    // This request has an invalid username, will return 422
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('POST', '/session')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody([
            'username' => 'username+example.com',
            'password' => '123abc',
        ]),
    // This request body is valid with invalid credentials, will return 401
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('POST', '/session')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody([
            'username' => 'username@example.com',
            'password' => 'wrong',
        ]),
    // This request body is valid with valid credentials, will return 201
    $request = (new \Slim\Psr7\Factory\ServerRequestFactory())->createServerRequest('POST', '/session')
        ->withHeader('Content-Type', 'application/json')
        ->withParsedBody([
            'username' => 'username@example.com',
            'password' => '123abc',
        ]),
];

\Slim\Factory\AppFactory::setContainer(new \DI\Container());
$app = \Slim\Factory\AppFactory::create();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, false, false);
$errorMiddleware->setErrorHandler(
    [HttpUnprocessableEntityException::class, \Slim\Exception\HttpUnauthorizedException::class],
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

$app->post('/session', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    (new SessionPostValidation($this))->validate($request);

    $users = [
        'username@example.com' => '123abc',
    ];

    $bodyParams = $request->getParsedBody();
    $username = $bodyParams['username'];
    $password = $bodyParams['password'];
    if (!array_key_exists($username, $users) || $users[$username] !== $password) {
        throw new \Slim\Exception\HttpUnauthorizedException($request);
    }

    $response->getBody()->write(json_encode(['token' => bin2hex(random_bytes(8))]));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
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
