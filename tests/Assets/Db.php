<?php

declare(strict_types=1);

namespace Tests\Assets;

use PDO;

class Db
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrate(string $databaseName): void
    {
        $sqlExpression = sprintf("CREATE DATABASE %s;", $databaseName);
        $this->pdo->exec($sqlExpression);
    }

    public function databaseExists(string $databaseName): bool
    {
        $baseQuery = "SELECT schema_name FROM information_schema.schemata WHERE schema_name = '%s';";
        $query = sprintf($baseQuery, $databaseName);
        $preResults = $this->pdo->prepare($query);
        $preResults->execute();
        return (bool) $preResults->fetch(PDO::FETCH_ASSOC);
    }
}