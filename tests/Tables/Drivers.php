<?php

declare(strict_types=1);

namespace Tests\Tables;

use PDO;

class Drivers extends TablesAbstract
{
    private const TABLE_NAME = "drivers";

    private const CREATE_TABLE_QUERY_FILE = "tests/Assets/queries/create_drivers_table.sql";

    private const INSERT_DEFAULT_DRIVER_QUERY_FILE = "tests/Assets/queries/insert_drivers.sql";

    private const INSERT_ADDITIONAL_DRIVER_QUERY_FILE = "tests/Assets/queries/insert_additional_driver.sql";
    
    public function __construct()
    {
        $this->tableName = self::TABLE_NAME;
    }

    public static function createTableQuery(): string
    {
        return self::getQuery(self::CREATE_TABLE_QUERY_FILE);
    }

    public static function createInsertQuery(): string
    {
        return self::getQuery(self::INSERT_DEFAULT_DRIVER_QUERY_FILE);
    }

    public function seedAdditionalDriver(string $database, PDO $pdo): void
    {
        $pdo->exec("USE {$database};");
        $additionalDriverQuery = self::getQuery(self::INSERT_ADDITIONAL_DRIVER_QUERY_FILE);
        $pdo->exec($additionalDriverQuery);
    }

    public function seedDefaultDriver(string $database, PDO $pdo): void
    {
        $pdo->exec("USE {$database};");
        $defaultUserInsertQuery = self::getQuery(self::INSERT_DEFAULT_DRIVER_QUERY_FILE);
        $pdo->exec($defaultUserInsertQuery);
    }
}

