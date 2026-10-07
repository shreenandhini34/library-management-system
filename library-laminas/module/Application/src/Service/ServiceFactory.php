<?php

declare(strict_types=1);

namespace Application\Service;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $config = $container->get('config');
        $dbConfig = isset($config['db']) ? $config['db'] : [];

        if ($requestedName === DatabaseService::class) {
            return new DatabaseService($dbConfig);
        }

        if ($requestedName === SessionService::class) {
            return new SessionService();
        }

        throw new \InvalidArgumentException('Unknown service: ' . $requestedName);
    }
}
