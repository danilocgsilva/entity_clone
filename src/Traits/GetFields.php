<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone\Traits;

use Exception;
use PDO;

trait GetFields
{
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

    /**
     * Gets the common fields between source and destiny table.
     *
     * @return array
     */
    private function reduceFields(): array
    {
        $reducedDestinyFields = [];
        foreach ($this->destinyFields as $destinyField) {
            if (in_array($destinyField, $this->sourceFields)) {
                $reducedDestinyFields[] = $destinyField;
            } else {
                $this->reductionFields->addReducedDestiny($destinyField);
            }
        }

        $reducedSourceFields = [];
        foreach ($this->sourceFields as $sourceField) {
            if (in_array($sourceField, $this->destinyFields)) {
                $reducedSourceFields[] = $sourceField;
            } else {
                $this->reductionFields->addReducedSource($sourceField);
            }
        }

        $reducedFields = array_intersect($reducedDestinyFields, $reducedSourceFields);

        if ($this->cloneId) {
            return $reducedFields;
        }

        array_shift($reducedFields);

        return $reducedFields;
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
