<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\DatabaseService;
use Application\Service\SessionService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        $database = $container->get(DatabaseService::class);
        $session = $container->get(SessionService::class);

        switch ($requestedName) {
            case AuthController::class:
                return new AuthController($database, $session);
            case DashboardController::class:
                return new DashboardController($database, $session);
            case BooksController::class:
                return new BooksController($database, $session);
            case IssuesController::class:
                return new IssuesController($database, $session);
            default:
                throw new \InvalidArgumentException('Unknown controller: ' . $requestedName);
        }
    }
}
