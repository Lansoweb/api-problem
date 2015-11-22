<?php
namespace LosMiddleware\ApiProblem;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\ErrorMiddlewareInterface;

final class ApiProblem implements ErrorMiddlewareInterface
{

    /**
     * Runs the middleware
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     */
    public function __invoke($error, RequestInterface $request, ResponseInterface $response, $next = null)
    {
        $data = [];

        $status = $this->getStatusCode($error, $response);

        if ($status == 404) {
            $detail = sprintf("Path '%s' not found.", $request->getUri()->getPath());
        } else {
            $detail = 'erro';
        }

        $problem = new Model\ApiProblem($data['status'], $detail);
        $data = json_encode($problem->toArray());

        $requestId = $this->getRequestId($request, $response);
        if (! empty($requestId)) {
            $data['code'] = $requestId;
        }

        $response = new JsonResponse($data, $data['status'], $response->getHeaders());
        return $response->withHeader('Content-Type', 'application/problem+json');
    }

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
