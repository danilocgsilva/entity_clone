<?php

declare(strict_types=1);

namespace Tests\Assets;

use PDO;

class Db
{
    public function migrate(PDO $pdo, string $databaseName): void
    {
        $sqlExpression = sprintf("CREATE DATABASE %s;", $databaseName);
        $pdo->exec($sqlExpression);
    }
}