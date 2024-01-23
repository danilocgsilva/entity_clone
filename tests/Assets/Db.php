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
        $query = "USE {$databaseName};\n";
        $query .= "CREATE TABLE `drivers` (" .
        "    `id` INT NOT NULL AUTO_INCREMENT, " .
        "    `name` VARCHAR(192) NOT NULL, " . 
        "    `age` TINYINT NOT NULL, " .
        "    PRIMARY KEY (`id`)" .
        ") ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_bin;";
        $this->pdo->exec($query);
    }

    public function createDatabase(string $databaseName): void
    {
        $createDatabase = sprintf("CREATE DATABASE %s;", $databaseName);
        $this->pdo->exec($createDatabase);
    }

    public function seed(string $databaseName): void
    {
        $query = "USE {$databaseName};\n";
        $query .= "INSERT INTO `drivers` (id, name, age)" .
            " VALUES " .
            "(6, 'Tobias Silva', 33);";
        $this->pdo->exec($query);
    }

    public function renewDatabase(string $databaseName): void
    {
        $this->dropDatabase($databaseName);
        $this->createDatabase($databaseName);
        $this->migrate($databaseName);
    }

    public function countEntries(string $database, string $table): int
    {
        $this->pdo->exec("USE {$database};");

        $query = "SELECT COUNT(*) as counting FROM {$table};";
        $preResults = $this->pdo->prepare($query);
        $preResults->execute();
        return $preResults->fetch(PDO::FETCH_ASSOC)['counting'];
    }

    public function dropDatabase(string $databaseName): void
    {
        $this->pdo->exec(sprintf("DROP DATABASE `%s`;", $databaseName));
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