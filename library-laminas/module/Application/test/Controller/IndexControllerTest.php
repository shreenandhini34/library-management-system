<?php

declare(strict_types=1);

namespace ApplicationTest\Controller;

use Laminas\Stdlib\ArrayUtils;
use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp(): void
    {
        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            []
        ));

        parent::setUp();
    }

    public function testLoginPageCanBeAccessed(): void
    {
        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('Application\\Controller\\AuthController');
        $this->assertActionName('login');
    }

    public function testProtectedDashboardRedirectsToLogin(): void
    {
        $this->dispatch('/dashboard', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectTo('/');
    }
}
