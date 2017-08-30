<?php

require __DIR__ . '/../vendor/autoload.php';

class SessionPostValidation extends \MadeSimple\Slim\Middleware\Validation
{
    /**
     * @return array Rule set for the request path.
     */
    protected function getPathRules()
    {
        return [
        ];
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
        return [
            'username' => 'required|email',
            'password' => 'required-with:username|min-str-len:3',
        ];
    }
}

// Set up
$env = \Slim\Http\Environment::mock([
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/session',
    'QUERY_STRING'   => '',
    'CONTENT_TYPE'   => 'application/json',
]);
$uri     = \Slim\Http\Uri::createFromEnvironment($env);
$headers = \Slim\Http\Headers::createFromEnvironment($env);
$cookies = [];
$_server = $env->all();
$body    = new \Slim\Http\RequestBody();
$uploadedFiles = \Slim\Http\UploadedFile::createFromEnvironment($env);
$request = new \Slim\Http\Request('POST', $uri, $headers, $cookies, $_server, $body, $uploadedFiles);

// Write request data
$data = [
    'username' => 'username@example.com',
    'password' => '123abc',
];
$request->getBody()->write(json_encode($data));
$request->getBody()->rewind();


$settings = [
    'environment' => $env,
    'settings'    => [
        'determineRouteBeforeAppMiddleware' => true,
    ],
];
$app = new \Slim\App($settings);
$app->post('/session', function ($request, $response) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */
    $users = [
        'username@example.com' => '123abc',
    ];

    $username = $request->getParsedBodyParam('username');
    $password = $request->getParsedBodyParam('password');
    if (!array_key_exists($username, $users) || $users[$username] !== $password) {
        return $response->withJson(['message' => 'Username/password was bad'], 401);
    }

    return $response->withJson(['token' => '987-654-321'], 201);
})->add(new SessionPostValidation($app->getContainer()));

$response = $app($request, new \Slim\Http\Response());

$response->getBody()->rewind();
echo <<<EOT
Code:   {$response->getStatusCode()}
Reason: {$response->getReasonPhrase()}
Body:
{$response->getBody()->getContents()}

EOT;
