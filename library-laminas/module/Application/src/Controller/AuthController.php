<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\DatabaseService;
use Application\Service\SessionService;
use Laminas\View\Model\ViewModel;

class AuthController extends AbstractLibraryController
{
    /** @var DatabaseService */
    private $database;

    public function __construct(DatabaseService $database, SessionService $session)
    {
        parent::__construct($session);
        $this->database = $database;
    }

    public function loginAction()
    {
        if ($this->session->isAuthenticated()) {
            return $this->redirect()->toRoute('dashboard');
        }

        $message = null;

        if ($this->getRequest()->isPost()) {
            if (!$this->session->validateCsrf($this->params()->fromPost('_csrf'))) {
                $message = 'Your form session expired. Please refresh the page and try again.';
            }

            $username = trim((string) $this->params()->fromPost('username', ''));
            $password = (string) $this->params()->fromPost('password', '');

            if ($message === null && ($username === '' || $password === '')) {
                $message = 'Username and password are required.';
            } elseif ($message === null) {
                $stmt = $this->database->getConnection()->prepare(
                    'SELECT UserID, Username, Password FROM Users WHERE Username = :username LIMIT 1'
                );
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();

                $valid = false;
                if ($user) {
                    $stored = (string) $user['Password'];
                    $valid = password_verify($password, $stored);

                    // Keep compatibility with an existing database that stored plain text passwords.
                    if (!$valid && hash_equals($stored, $password)) {
                        $valid = true;
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $update = $this->database->getConnection()->prepare(
                            'UPDATE Users SET Password = :password WHERE UserID = :id'
                        );
                        $update->execute(['password' => $newHash, 'id' => (int) $user['UserID']]);
                    }
                }

                if ($valid) {
                    $this->session->login((string) $user['Username'], (int) $user['UserID']);
                    return $this->redirect()->toRoute('dashboard');
                }

                $message = 'Invalid username or password.';
            }
        }

        $view = new ViewModel([
            'message' => $message,
            'csrf' => $this->session->csrfToken(),
        ]);
        $view->setTerminal(false);
        return $view;
    }

    public function logoutAction()
    {
        $this->session->logout();
        return $this->redirect()->toRoute('login');
    }
}
