<?php

declare(strict_types=1);

namespace Application;

use Application\Controller\AuthController;
use Application\Controller\BookController;
use Application\Controller\DashboardController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use PDO;

return [

    'router' => [
        'routes' => [

            /*
             * Home
             */
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action' => 'index',
                    ],
                ],
            ],

            /*
             * Login
             */
            'login' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/login',
                    'defaults' => [
                        'controller' => AuthController::class,
                        'action' => 'login',
                    ],
                ],
            ],

            /*
             * Logout
             */
            'logout' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/logout',
                    'defaults' => [
                        'controller' => AuthController::class,
                        'action' => 'logout',
                    ],
                ],
            ],

            /*
             * Dashboard
             */
            'dashboard' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/dashboard',
                    'defaults' => [
                        'controller' => DashboardController::class,
                        'action' => 'index',
                    ],
                ],
            ],

            /*
             * Books
             */
            'books' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/books',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'index',
                    ],
                ],
            ],

            /*
             * Stock
             */
            'books-stock' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/books/stock',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'stock',
                    ],
                ],
            ],

            /*
             * Add Book
             */
            'books-add' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/books/add',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'add',
                    ],
                ],
            ],

            /*
             * Edit Book
             */
            'books-edit' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/books/edit[/:id]',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'edit',
                    ],
                ],
            ],

            /*
             * Delete Book
             */
            'books-delete' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/books/delete[/:id]',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'delete',
                    ],
                ],
            ],

            /*
             * Buy Book
             */
            'books-buy' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/books/buy[/:id]',
                    'defaults' => [
                        'controller' => BookController::class,
                        'action' => 'buy',
                    ],
                ],
            ],

        ],
    ],

    /*
     * Controllers
     */
    'controllers' => [
        'factories' => [

            Controller\IndexController::class => InvokableFactory::class,

            AuthController::class => function ($container) {

                $config = $container->get('config');

                $db = $config['db'];

                $pdo = new PDO(
                    $db['dsn'],
                    $db['username'],
                    $db['password']
                );

                $pdo->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                return new AuthController($pdo);
            },

            DashboardController::class => InvokableFactory::class,

            BookController::class => function ($container) {

                $config = $container->get('config');

                $db = $config['db'];

                $pdo = new PDO(
                    $db['dsn'],
                    $db['username'],
                    $db['password']
                );

                $pdo->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                return new BookController($pdo);
            },

        ],
    ],

    /*
     * View Manager
     */
    'view_manager' => [

        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',

        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',

        'template_map' => [

            'layout/layout' =>
                __DIR__ . '/../view/layout/layout.phtml',

            'application/index/index' =>
                __DIR__ . '/../view/application/index/index.phtml',

            'application/auth/login' =>
                __DIR__ . '/../view/application/auth/login.phtml',

            'application/dashboard/index' =>
                __DIR__ . '/../view/application/dashboard/index.phtml',

            'application/book/index' =>
                __DIR__ . '/../view/application/book/index.phtml',

            'application/book/stock' =>
                __DIR__ . '/../view/application/book/stock.phtml',

            'application/book/add' =>
                __DIR__ . '/../view/application/book/add.phtml',

            'application/book/edit' =>
                __DIR__ . '/../view/application/book/edit.phtml',

            'error/404' =>
                __DIR__ . '/../view/error/404.phtml',

            'error/index' =>
                __DIR__ . '/../view/error/index.phtml',

        ],

        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];