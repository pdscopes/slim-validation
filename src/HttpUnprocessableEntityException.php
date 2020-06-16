<?php

namespace MadeSimple\Slim\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpSpecializedException;

class HttpUnprocessableEntityException extends HttpSpecializedException
{
    protected $code = 422;
    protected $message = 'Unprocessable Entity.';
    protected $title = '422 Unprocessable Entity';
    protected $description = 'The parsed request body is invalid.';

    /**
     * @var array
     */
    protected $errors;

    /**
     * Create new exception
     *
     * @param ServerRequestInterface $request
     * @param array $errors
     */
    public function __construct(ServerRequestInterface $request, array $errors)
    {
        parent::__construct($request);
        $this->errors = $errors;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
