<?php

declare(strict_types=1);

namespace Tests\Assets;

use PDO;
use Tests\Tables\TablesAbstract;

class Db
{
    private PDO $pdo;
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function migrate(string $databaseName, TablesAbstract $table): void
    {
        $query = "USE {$databaseName};\n";
        $query .= $table::createTableQuery();
        $this->pdo->exec($query);
    }

    public function createDatabase(string $databaseName): void
    {
        $createDatabaseQuery = sprintf("CREATE DATABASE %s;", $databaseName);
        $this->pdo->exec($createDatabaseQuery);
    }

    public function seed(string $databaseName, TablesAbstract $tableClass): void
    {
        $query = "USE {$databaseName};\n";
        $query .= $tableClass::createInsertQuery();
        $this->pdo->exec($query);
    }

    public function renewDatabase(string $databaseName): void
    {
        $this->dropDatabaseIfExists($databaseName);
        $this->createDatabase($databaseName);
    }

    public function renewTable(string $databaseName, TablesAbstract $table): void
    {
        $this->pdo->exec("USE {$databaseName};");
        $this->dropTable($table);
        $this->migrate($databaseName, $table);
    }

    public function dropTable(TablesAbstract $table): void
    {
        $query = "DROP TABLE IF EXISTS " . $table->getTableName() . ";";
        $this->pdo->exec($query);
    }

    public function countEntries(string $database, TablesAbstract $table): int
    {
        $this->pdo->exec("USE {$database};");

        $query = "SELECT COUNT(*) as counting FROM {$table->getTableName()};";
        $preResults = $this->pdo->prepare($query);
        $preResults->execute();
        return $preResults->fetch(PDO::FETCH_ASSOC)['counting'];
    }

    public function dropDatabaseIfExists(string $databaseName): void
    {
        $this->pdo->exec(sprintf("DROP DATABASE IF EXISTS `%s`;", $databaseName));
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