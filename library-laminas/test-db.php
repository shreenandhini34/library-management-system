<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/autoload/local.php';

$pdo = new PDO(
    $config['db']['dsn'],
    $config['db']['username'],
    $config['db']['password']
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$result = $pdo->query('SELECT * FROM books');

echo '<h1>Database Connection Successful</h1>';

echo '<pre>';

foreach ($result as $row) {
    print_r($row);
}

echo '</pre>';