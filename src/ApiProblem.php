<?php
namespace LosMiddleware\ApiProblem;

use LosMiddleware\ApiProblem\Exception\ApiException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\ErrorMiddlewareInterface;

final class ApiProblem implements ErrorMiddlewareInterface
{

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     */
    public function __invoke($error, ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $data = [];

        $status = $this->getStatusCode($error, $response);
        $message = $this->getMessage($error, $request, $response);

        if ($status == 404 && empty($message)) {
            $detail = sprintf("Path '%s' not found.", $request->getUri()->getPath());
        } else {
            $detail = $message;
        }

        $problem = new Model\ApiProblem($status, $detail);
        $data = $problem->toArray();

        $requestId = $this->getRequestId($request, $response);
        if (! empty($requestId)) {
            $data['code'] = $requestId;
        }

        $response = new JsonResponse($data, $data['status'], $response->getHeaders());
        return $response->withHeader('Content-Type', 'application/problem+json');
    }

    /**
     * Returns an error message from $error
     *
     * @param \Exception $error
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return string
     */
    private function getMessage($error, ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($error instanceof ApiException) {
            $message = $error->getArrayMessage();
            return !empty($message) ? $message : $error->getMessage();
        }

        if ($error instanceof \Exception || (class_exists('Error') && $error instanceof \Error)) {
            return $error->getMessage();
        }

        return 'An error ocurred.';
    }

    /**
     * Returns the status code from the error or response
     *
     * @param unknown $error
     * @param ResponseInterface $response
     * @return int
     */
    private function getStatusCode($error, ResponseInterface $response)
    {
        if ($error instanceof \Exception && ($error->getCode() >= 400 && $error->getCode() <= 599)) {
            return $error->getCode();
        }

        $status = $response->getStatusCode();
        if (! $status || $status < 400 || $status > 599) {
            $status = 500;
        }
        return $status;
    }

    /**
     * Returns the X-Request-Id if present
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return string
     */
    private function getRequestId(RequestInterface $request, ResponseInterface $response)
    {
        if ($request->hasHeader('X-Request-Id')) {
            return $request->getHeader('X-Request-Id')[0];
        }

        if ($response->hasHeader('X-Request-Id')) {
            return $response->getHeader('X-Request-Id')[0];
        }

        return '';
    }
}
