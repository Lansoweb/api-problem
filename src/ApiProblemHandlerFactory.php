<?php
namespace LosMiddleware\ApiProblem;

use Interop\Container\ContainerInterface;

class ApiProblemHandlerFactory
{

    /**
     * @param ContainerInterface $container
     * @return \LosMiddleware\ApiProblem\ApiProblemHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ApiProblemHandler();
    }
}