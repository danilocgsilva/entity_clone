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

    public function setDatabase(string $databaseName): self
    {
        if (!$this->databaseExists($databaseName)) {
            $this->createDatabase($databaseName);
        }
        $query = "USE {$databaseName};\n";
        $this->pdo->exec($query);
        return $this;
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

    public function seed(TablesAbstract $tableClass): self
    {
        $query = $tableClass::createInsertQuery();
        $this->pdo->exec($query);
        return $this;
    }

    public function renewDatabase(string $databaseName): void
    {
        $this->dropDatabaseIfExists($databaseName);
        $this->createDatabase($databaseName);
    }

    public function renewTable(string $databaseName, TablesAbstract $table): self
    {
        $this->dropTable($table);
        $this->migrate($databaseName, $table);
        return $this;
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

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    public function existsRegisterId(int $id, TablesAbstract $table): bool
    {
        $searchQuery = "SELECT COUNT(*) as counting FROM %s WHERE %s = %s;";
        $valuedValues = sprintf(
            $searchQuery,
            $table->getTableName(),
            $table->getTableId(),
            $id
        );

        $preResults = $this->pdo->prepare($valuedValues);
        $preResults->execute();
        $queryResult = $preResults->fetch(PDO::FETCH_ASSOC)['counting'];
        return (bool) $queryResult;
    }
}