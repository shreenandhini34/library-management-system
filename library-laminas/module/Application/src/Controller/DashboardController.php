<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\DatabaseService;
use Application\Service\SessionService;
use Laminas\View\Model\ViewModel;

class DashboardController extends AbstractLibraryController
{
    /** @var DatabaseService */
    private $database;

    public function __construct(DatabaseService $database, SessionService $session)
    {
        parent::__construct($session);
        $this->database = $database;
    }

    public function indexAction()
    {
        $redirect = $this->requireLogin();
        if ($redirect) {
            return $redirect;
        }

        $pdo = $this->database->getConnection();
        $bookCount = (int) $pdo->query('SELECT COUNT(*) FROM Books WHERE IsDeleted = 0')->fetchColumn();
        $activeIssues = (int) $pdo->query('SELECT COUNT(*) FROM Issues WHERE IsReturned = 0')->fetchColumn();

        return new ViewModel([
            'username' => $this->session->username(),
            'bookCount' => $bookCount,
            'activeIssues' => $activeIssues,
        ]);
    }
}
