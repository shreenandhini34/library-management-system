<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\SessionService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Http\Response;

abstract class AbstractLibraryController extends AbstractActionController
{
    /** @var SessionService */
    protected $session;

    public function __construct(SessionService $session)
    {
        $this->session = $session;
    }

    protected function requireLogin(): ?Response
    {
        if ($this->session->isAuthenticated()) {
            return null;
        }

        return $this->redirect()->toRoute('login');
    }

    protected function requireCsrf(): ?Response
    {
        if ($this->session->validateCsrf($this->params()->fromPost('_csrf'))) {
            return null;
        }

        $this->session->addFlash('error', 'Your form session expired. Please try again.');
        return $this->redirect()->toRoute('dashboard');
    }
}
