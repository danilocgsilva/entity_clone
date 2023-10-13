<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use PDO;
use Exception;

class EntityClone
{
    private string $table;
    private string $idValue;
    private array $sourceFields;
    private array $destinyFields;
    
    public function __construct(
        private PDO $sourcePdo,
        private PDO $destinyPdo
    ) {}

    public function entityClone(
        string $table,
        string $idValue
    ): void
    {
        $this->table = $table;
        $this->idValue = $idValue;
        $this->sourceFields = $this->getFields($this->sourcePdo);
        $this->destinyFields = $this->getFields($this->sourcePdo);
        
        $insertQuery = $this->createInsertQuery();
    }

    private function getFields(PDO $pdo): array
    {
        $databaseName = $pdo->query('SELECT database()')->fetchColumn();

        $baseQuery = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
            ORDER BY ordinal_position;";

        $consultFieldsQuery = sprintf($baseQuery, $databaseName, $this->table);
        $preResults = $pdo->prepare($consultFieldsQuery);
        $preResults->execute();
        $fields = [];
        while ($row = $preResults->fetch(PDO::FETCH_ASSOC)) {
            $fields[] = $row['COLUMN_NAME'];
        }
        return $fields;
    }

    private function createInsertQuery(): string
    {
        $destinyFieldsCommaSeparated = $this->getCommaSeparatedDestinyFields();

        return sprintf(
            "INSERT INTO %s (%s) VALUES (%s);", 
            $this->table, 
            $destinyFieldsCommaSeparated,
            $this->getSourceValuesAsString()
        );
    }

    private function getSourceValuesAsString(): string
    {
        $getSourceDataQuery = sprintf(
            "SELECT %s FROM %s WHERE %s", 
            $this->getCommaSeparatedSourceFields(),
            $this->table,
            $this->getFirstColumnFromSource()
        );

        $preResults = $this->sourcePdo->prepare($getSourceDataQuery);
        $preResults->execute();
        $rowData = $preResults->fetch(PDO::FETCH_ASSOC);

        return '"a", "b", "c"';
    }

    private function getFirstColumnFromSource(): string
    {
        return $this->sourceFields[0];
    }

    private function getCommaSeparatedDestinyFields(): string
    {
        $fieldsWithoutFirstColumn = clone $this->destinyFields;
        array_shift($fieldsWithoutFirstColumn);
        return implode(",", $fieldsWithoutFirstColumn);
    }

    private function getCommaSeparatedSourceFields(): string
    {
        $fieldsWithoutFirstColumn = clone $this->sourceFields;
        array_shift($fieldsWithoutFirstColumn);
        return implode(",", $fieldsWithoutFirstColumn);
    }

    private function getCommaSeparatedFields(string $target): string
    {
        $pdoToClone = null;
        // switch ($target) {
        //     case 'source':
        //         $pdoToClone = $this->sourceFields;
        //     case 'destiny';
        //         $pdoToClone = $this->destinyFields;
        //     default:
        //         throw new Exception("Wrong target given to method.");
        // }
        $fieldsWithoutFirstColumn = clone $pdoToClone;
        array_shift($fieldsWithoutFirstColumn);
        return implode(",", $fieldsWithoutFirstColumn);
    }
}
