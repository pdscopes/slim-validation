<?php

namespace MadeSimple\Slim\Middleware;

use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class InvalidRequestException extends Exception
{
    /**
     * A request object
     *
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * A response object to send to the HTTP client
     *
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var array
     */
    protected $errors;

    /**
     * Create new exception
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $errors
     */
    public function __construct(ServerRequestInterface $request, ResponseInterface $response, array $errors)
    {
        parent::__construct();
        $this->request = $request;
        $this->response = $response;
        $this->errors = $errors;
    }

    /**
     * Get request
     *
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get response
     *
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}