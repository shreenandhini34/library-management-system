<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Service\DatabaseService;
use Application\Service\SessionService;
use Laminas\View\Model\ViewModel;
use RuntimeException;

class IssuesController extends AbstractLibraryController
{
    /** @var DatabaseService */
    private $database;

    public function __construct(DatabaseService $database, SessionService $session)
    {
        parent::__construct($session);
        $this->database = $database;
    }

    public function listAction()
    {
        $redirect = $this->requireLogin();
        if ($redirect) {
            return $redirect;
        }

        $pdo = $this->database->getConnection();
        $stmt = $pdo->query(
            'SELECT i.IssueID, i.IssueDate, i.ReturnDate, i.IsReturned, b.Title, u.Username
             FROM Issues i
             INNER JOIN Books b ON b.BookID = i.BookID
             INNER JOIN Users u ON u.UserID = i.UserID
             ORDER BY i.IssueID DESC'
        );

        return new ViewModel([
            'issues' => $stmt->fetchAll(),
            'csrfToken' => $this->session->csrfToken(),
        ]);
    }

    public function addAction()
    {
        $redirect = $this->requireLogin();
        if ($redirect) {
            return $redirect;
        }

        $pdo = $this->database->getConnection();
        $books = $pdo->query(
            'SELECT BookID, Title, Author, AvailableQuantity FROM Books WHERE IsDeleted = 0 AND AvailableQuantity > 0 ORDER BY Title'
        )->fetchAll();

        $message = null;
        $bookId = 0;
        $userId = $this->session->userId();

        if ($this->getRequest()->isPost()) {
            $csrf = $this->requireCsrf();
            if ($csrf) {
                return $csrf;
            }

            $bookId = (int) $this->params()->fromPost('book_id', 0);
            $selectedUserId = (int) $this->params()->fromPost('user_id', 0);
            $userId = $selectedUserId > 0 ? $selectedUserId : (int) $this->session->userId();

            if ($bookId < 1 || $userId < 1) {
                $message = 'Select a book and a valid user.';
            } else {
                try {
                    $pdo->beginTransaction();

                    $bookStmt = $pdo->prepare(
                        'SELECT AvailableQuantity FROM Books WHERE BookID = :id AND IsDeleted = 0 FOR UPDATE'
                    );
                    $bookStmt->execute(['id' => $bookId]);
                    $book = $bookStmt->fetch();

                    $userStmt = $pdo->prepare('SELECT UserID FROM Users WHERE UserID = :id LIMIT 1');
                    $userStmt->execute(['id' => $userId]);
                    $user = $userStmt->fetch();

                    if (!$book || (int) $book['AvailableQuantity'] < 1) {
                        throw new RuntimeException('The selected book is not currently available.');
                    }
                    if (!$user) {
                        throw new RuntimeException('The selected user does not exist.');
                    }

                    $insert = $pdo->prepare(
                        'INSERT INTO Issues (BookID, UserID, IssueDate, IsReturned) VALUES (:book_id, :user_id, NOW(), 0)'
                    );
                    $insert->execute(['book_id' => $bookId, 'user_id' => $userId]);

                    $update = $pdo->prepare(
                        'UPDATE Books SET AvailableQuantity = AvailableQuantity - 1 WHERE BookID = :id'
                    );
                    $update->execute(['id' => $bookId]);

                    $pdo->commit();
                    $this->session->addFlash('success', 'Book issued successfully.');
                    return $this->redirect()->toRoute('issues');
                } catch (RuntimeException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $message = $e->getMessage();
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    $message = 'Unable to issue the book. Please try again.';
                }
            }
        }

        $users = $pdo->query('SELECT UserID, Username FROM Users ORDER BY Username')->fetchAll();

        return new ViewModel([
            'books' => $books,
            'users' => $users,
            'bookId' => $bookId,
            'userId' => $userId,
            'message' => $message,
            'csrf' => $this->session->csrfToken(),
        ]);
    }

    public function returnAction()
    {
        $redirect = $this->requireLogin();
        if ($redirect) {
            return $redirect;
        }

        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('issues');
        }

        $csrf = $this->requireCsrf();
        if ($csrf) {
            return $csrf;
        }

        $issueId = (int) $this->params()->fromRoute('id', 0);
        $pdo = $this->database->getConnection();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                'SELECT IssueID, BookID, IsReturned FROM Issues WHERE IssueID = :id FOR UPDATE'
            );
            $stmt->execute(['id' => $issueId]);
            $issue = $stmt->fetch();

            if (!$issue) {
                throw new RuntimeException('Issue record not found.');
            }
            if ((int) $issue['IsReturned'] === 1) {
                throw new RuntimeException('This book has already been returned.');
            }

            $updateIssue = $pdo->prepare(
                'UPDATE Issues SET IsReturned = 1, ReturnDate = NOW() WHERE IssueID = :id'
            );
            $updateIssue->execute(['id' => $issueId]);

            $updateBook = $pdo->prepare(
                'UPDATE Books SET AvailableQuantity = AvailableQuantity + 1 WHERE BookID = :book_id'
            );
            $updateBook->execute(['book_id' => (int) $issue['BookID']]);

            $pdo->commit();
            $this->session->addFlash('success', 'Book returned successfully.');
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->session->addFlash('error', $e->getMessage());
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $this->session->addFlash('error', 'Unable to return the book. Please try again.');
        }

        return $this->redirect()->toRoute('issues');
    }
}
