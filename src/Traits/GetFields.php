<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone\Traits;

use Exception;
use PDO;

trait GetFields
{
    /**
     * Get fields from a table. NOTE: The PDO must have declared table on
     *   its connection string.
     *
     * @param PDO $pdo
     * @return string[]
     */
    private function getFields(PDO $pdo): array
    {
        $databaseName = $pdo->query('SELECT database()')->fetchColumn();

        if ($databaseName === null) {
            throw new Exception("The PDO object must have a database definition on its statement.");
        }

        $baseQuery = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :tableschema AND TABLE_NAME = :tablename
            ORDER BY ordinal_position;";

        $preResults = $pdo->prepare($baseQuery);
        $preResults->execute([
            ':tableschema' => $databaseName,
            ':tablename' => $this->table,
        ]);
        $fields = [];
        while ($row = $preResults->fetch(PDO::FETCH_ASSOC)) {
            $fields[] = $row['COLUMN_NAME'];
        }
        return $fields;
    }

    private function convertDataResultToSuitableString($rowData)
    {
        foreach ($rowData as $key => $value) {
            if ($value === null) {
                $rowData[$key] = "NULL";
            } else
            if (!is_numeric($value)) {
                $rowData[$key] = "'" . $rowData[$key] . "'";
            }
        }

        return $rowData;
    }
}
