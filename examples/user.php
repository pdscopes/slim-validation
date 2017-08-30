<?php

require __DIR__ . '/../vendor/autoload.php';

use MadeSimple\Arrays\ArrDots;

class UserPutValidation extends \MadeSimple\Slim\Middleware\Validation
{
    /**
     * @return array Rule set for the request path.
     */
    protected function getPathRules()
    {
        return [
            'user' => 'is:int|min:1'
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
$env = \Slim\Http\Environment::mock([
    'REQUEST_METHOD' => 'PUT',
    'REQUEST_URI'    => '/post/15/comment',
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
    'firstName' => 'Joe',
    'lastName'  => 'Bloggs',
    'password'  => '',
    'confirm'   => '',
    'preferences.emails'  => 'yes',
    'access.submission.0' => 3,
    'access.report.0'     => 2,
];
$request->getBody()->write(json_encode(ArrDots::explode($data)));
$request->getBody()->rewind();


$settings = [
    'environment' => $env,
    'settings'    => [
        'determineRouteBeforeAppMiddleware' => true,
    ],
];
$app = new \Slim\App($settings);
$app->post('/post/{post}/comment', function ($request, $response) {
    /** @var \Slim\Http\Request $request */
    /** @var \Slim\Http\Response $response */
    return $response->withStatus(204);
})->add(new UserPutValidation($app->getContainer()));

$response = $app($request, new \Slim\Http\Response());

$response->getBody()->rewind();
echo <<<EOT
Code:   {$response->getStatusCode()}
Reason: {$response->getReasonPhrase()}
Body:
{$response->getBody()->getContents()}

EOT;
