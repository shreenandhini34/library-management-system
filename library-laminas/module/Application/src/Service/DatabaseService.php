<?php

declare(strict_types=1);

namespace Application\Service;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseService
{
    /** @var PDO|null */
    private $pdo;

    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getConnection(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $host = isset($this->config['host']) ? $this->config['host'] : '127.0.0.1';
        $port = isset($this->config['port']) ? $this->config['port'] : 3306;
        $database = isset($this->config['database']) ? $this->config['database'] : 'LibrarySystem';
        $username = isset($this->config['username']) ? $this->config['username'] : 'root';
        $password = isset($this->config['password']) ? $this->config['password'] : '';

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);

        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException(
                'Unable to connect to the LibrarySystem MySQL database. Check config/autoload/local.php and make sure MySQL is running.',
                0,
                $e
            );
        }

        return $this->pdo;
    }
}
