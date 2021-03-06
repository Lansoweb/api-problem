<?php
namespace LosMiddleware\ApiProblem;

use Interop\Container\ContainerInterface;

class ErrorResponseGeneratorFactory
{

    /**
     * @param ContainerInterface $container
     * @return \LosMiddleware\ApiProblem\ApiProblemHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $config = $config['los_api_problem'] ?? [];

        return new ErrorResponseGenerator($config);
    }
}