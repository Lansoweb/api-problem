<?php
namespace LosMiddleware\ApiProblem;

use LosMiddleware\ApiProblem\Exception\ApiException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Stratigility\Utils;

final class ErrorResponseGenerator
{
    private $displayTrace = false;

    public function __construct($config = [])
    {
        $this->displayTrace = $config['display_trace'] ?? false;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $err
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($error, RequestInterface $request, ResponseInterface $response)
    {
        $response = $response->withStatus(Utils::getStatusCode($error, $response));

        $data = [];

        $status = $this->getStatusCode($error, $response);
        $message = $this->getMessage($error, $request, $response);
        $additional = $this->getAdditional($error);

        if ($status == 404 && empty($message)) {
            $detail = sprintf("Path '%s' not found.", $request->getUri()->getPath());
        } else {
            $detail = $message;
        }

        $problem = new Model\ApiProblem($status, $detail, null, null, $additional);
        $problem->setDetailIncludesStackTrace($this->displayTrace);
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

        if ($error instanceof \Throwable) {
            return $error;
        }

        return 'An error ocurred.';
    }

    private function getAdditional($error) : array
    {
        if (!($error instanceof ApiException)) {
            return [];
        }

        $extra = $error->getExtra();
        return !empty($extra) ? $extra : [];
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
        if ($error instanceof \Throwable && ($error->getCode() >= 400 && $error->getCode() <= 599)) {
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

    /**
     * @param boolean $displayTrace
     */
    public function setDisplayTrace($displayTrace)
    {
        $this->displayTrace = $displayTrace;
        return $this;
    }

}
