<?php

declare(strict_types=1);

namespace Tests\Tables;

use PDO;

class DriversPayment extends TablesAbstract
{
    private const TABLE_NAME = "payment";

    private const CREATE_TABLE_QUERY_FILE = "tests/Assets/queries/create_payment_table.sql";

    private const INSERT_DEFAULT_DRIVER_PAYMENTS = "tests/Assets/queries/insert_payment.sql";

    private const INSERT_ADDITIONAL_DRIVER_PAYMENTS_QUERY = "tests/Assets/queries/insert_additional_payment.sql";
    
    public function __construct()
    {
        $this->tableName = self::TABLE_NAME;
    }

    public function seedDefaultPayments(string $database, PDO $pdo): void
    {
        $pdo->exec("USE {$database};");
        $defaultPaymentsQuery = self::getQuery(self::INSERT_DEFAULT_DRIVER_PAYMENTS);
        $pdo->exec($defaultPaymentsQuery);
    }

    public function seedAdditionalDriverPayments(string $database, PDO $pdo): void
    {
        $pdo->exec("USE {$database};");
        $additionalDriverPaymentQuery = self::getQuery(self::INSERT_ADDITIONAL_DRIVER_PAYMENTS_QUERY);
        $pdo->exec($additionalDriverPaymentQuery);
    }

    public static function createTableQuery(): string
    {
        return self::getQuery(self::CREATE_TABLE_QUERY_FILE);
    }

    public static function createInsertQuery(): string
    {
        return self::getQuery(self::INSERT_DEFAULT_DRIVER_PAYMENTS);
    }

}
