<?php

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use PDO;

class BookController extends AbstractActionController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function indexAction()
    {
        $statement = $this->pdo->query(
            'SELECT id, Title, Author, Category, Stock
             FROM books
             ORDER BY Title'
        );

        $books = $statement->fetchAll(PDO::FETCH_ASSOC);

        return new ViewModel([
            'books' => $books,
        ]);
    }

    public function stockAction()
    {
        $statement = $this->pdo->query(
            'SELECT id, Title, Author, Category, Stock
             FROM books
             ORDER BY Title'
        );

        $books = $statement->fetchAll(PDO::FETCH_ASSOC);

        return new ViewModel([
            'books' => $books,
        ]);
    }

    public function addAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $title = trim((string) $request->getPost('Title'));
            $author = trim((string) $request->getPost('Author'));
            $category = trim((string) $request->getPost('Category'));
            $stock = (int) $request->getPost('Stock');

            if ($title === '' || $author === '' || $category === '') {
                return new ViewModel([
                    'error' => 'Title, Author and Category are required.',
                    'title' => $title,
                    'author' => $author,
                    'category' => $category,
                    'stock' => $stock,
                ]);
            }

            if ($stock < 0) {
                return new ViewModel([
                    'error' => 'Stock cannot be negative.',
                    'title' => $title,
                    'author' => $author,
                    'category' => $category,
                    'stock' => $stock,
                ]);
            }

            $statement = $this->pdo->prepare(
                'INSERT INTO books
                    (Title, Author, Category, Stock)
                 VALUES
                    (:title, :author, :category, :stock)'
            );

            $statement->execute([
                ':title' => $title,
                ':author' => $author,
                ':category' => $category,
                ':stock' => $stock,
            ]);

            return $this->redirect()->toRoute('books');
        }

        return new ViewModel([
            'title' => '',
            'author' => '',
            'category' => '',
            'stock' => 10,
        ]);
    }

    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id');

        if ($id <= 0) {
            return $this->redirect()->toRoute('books');
        }

        $statement = $this->pdo->prepare(
            'SELECT id, Title, Author, Category, Stock
             FROM books
             WHERE id = :id'
        );

        $statement->execute([
            ':id' => $id,
        ]);

        $book = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$book) {
            return $this->redirect()->toRoute('books');
        }

        $request = $this->getRequest();

        if ($request->isPost()) {

            $title = trim((string) $request->getPost('Title'));
            $author = trim((string) $request->getPost('Author'));
            $category = trim((string) $request->getPost('Category'));
            $stock = (int) $request->getPost('Stock');

            if ($title === '' || $author === '' || $category === '') {
                return new ViewModel([
                    'error' => 'Title, Author and Category are required.',
                    'id' => $id,
                    'title' => $title,
                    'author' => $author,
                    'category' => $category,
                    'stock' => $stock,
                ]);
            }

            if ($stock < 0) {
                return new ViewModel([
                    'error' => 'Stock cannot be negative.',
                    'id' => $id,
                    'title' => $title,
                    'author' => $author,
                    'category' => $category,
                    'stock' => $stock,
                ]);
            }

            $update = $this->pdo->prepare(
                'UPDATE books
                 SET Title = :title,
                     Author = :author,
                     Category = :category,
                     Stock = :stock
                 WHERE id = :id'
            );

            $update->execute([
                ':title' => $title,
                ':author' => $author,
                ':category' => $category,
                ':stock' => $stock,
                ':id' => $id,
            ]);

            return $this->redirect()->toRoute('books');
        }

        return new ViewModel([
            'id' => $book['id'],
            'title' => $book['Title'],
            'author' => $book['Author'],
            'category' => $book['Category'],
            'stock' => $book['Stock'],
        ]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id');

        if ($id <= 0) {
            return $this->redirect()->toRoute('books');
        }

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('books');
        }

        $statement = $this->pdo->prepare(
            'DELETE FROM books
             WHERE id = :id'
        );

        $statement->execute([
            ':id' => $id,
        ]);

        return $this->redirect()->toRoute('books');
    }

    public function buyAction()
    {
        $id = (int) $this->params()->fromRoute('id');

        if ($id <= 0) {
            return $this->redirect()->toRoute('books');
        }

        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->redirect()->toRoute('books');
        }

        $statement = $this->pdo->prepare(
            'UPDATE books
             SET Stock = Stock - 1
             WHERE id = :id
             AND Stock > 0'
        );

        $statement->execute([
            ':id' => $id,
        ]);

        return $this->redirect()->toRoute('books');
    }
}