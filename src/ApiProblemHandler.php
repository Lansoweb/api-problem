<?php
namespace LosMiddleware\ApiProblem;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ApiProblemHandler
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
    public function __invoke(RequestInterface $request, ResponseInterface $response, $err = null)
    {
        if (! $err) {
            if ($response->getStatusCode() === 200 && $response->getBody()->getSize() === 0) {
                $err = new \Exception(null, 404);
            } else {
                return $response;
            }
        }
        $handler = new ApiProblem();
        $handler->setDisplayTrace($this->displayTrace);
        return $handler($err, $request, $response, null);
    }
}
