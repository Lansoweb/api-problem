<?php
namespace LosMiddleware\ApiProblem;

use Interop\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Stratigility\Middleware\ErrorHandler;

class ErrorHandlerFactory
{

    /**
     * @param ContainerInterface $container
     * @return \LosMiddleware\ApiProblem\ApiProblemHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = $config['los_api_problem'] ?? [];

        return new ErrorHandler(new Response(), new ErrorResponseGenerator($config));
    }
}