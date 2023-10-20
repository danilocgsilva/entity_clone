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
    private bool $cloneId = false;
    
    public function __construct(
        private PDO $sourcePdo,
        private PDO $destinyPdo
    ) {}

    public function setOnCloneId(): self
    {
        $this->cloneId = true;
        return $this;
    }

    public function setOffCloneId(): self
    {
        $this->cloneId = false;
        return $this;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function entityClone(string $idValue): bool
    {
        $this->idValue = $idValue;
        $this->sourceFields = $this->getFields($this->sourcePdo);
        $this->destinyFields = $this->getFields($this->sourcePdo);
        
        $insertQuery = $this->createInsertQuery();

        $resResults = $this->destinyPdo->prepare($insertQuery);
        $resultsInsertion = $resResults->execute();
        return $resultsInsertion;
    }

    private function getFields(PDO $pdo): array
    {
        $databaseName = $pdo->query('SELECT database()')->fetchColumn();

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

    private function createInsertQuery(): string
    {
        $destinyFieldsCommaSeparated = $this->getCommaSeparatedDestinyFields();
        $sourceValuesAsString = $this->getSourceValuesAsString();

        return sprintf(
            "INSERT INTO %s (%s) VALUES (%s);", 
            $this->table, 
            $destinyFieldsCommaSeparated,
            $sourceValuesAsString
        );
    }

    private function getSourceValuesAsString(): string
    {
        $getSourceDataQuery = sprintf(
            "SELECT %s FROM %s WHERE %s = :id", 
            $this->getCommaSeparatedSourceFields(),
            $this->table,
            $this->sourceFields[0]
        );

        $preResults = $this->sourcePdo->prepare($getSourceDataQuery);
        $preResults->execute([
            ':id' => $this->idValue
        ]);
        $rowData = $preResults->fetch(PDO::FETCH_NUM);

        foreach ($rowData as $key => $value) {
            if ($value === null) {
                $rowData[$key] = "NULL";
            } else
            if (!is_numeric($value)) {
                $rowData[$key] = "'" . $rowData[$key] . "'";
            }
        }

        return implode(",", $rowData);
    }

    private function getCommaSeparatedDestinyFields(): string
    {
        if ($this->cloneId) {
            return implode(",", $this->destinyFields);
        }
        $fieldsWithoutFirstColumn = array_map(fn ($element) => $element, $this->destinyFields);
        array_shift($fieldsWithoutFirstColumn);
        return implode(",", $fieldsWithoutFirstColumn);
    }

    private function getCommaSeparatedSourceFields(): string
    {
        if ($this->cloneId) {
            return implode(",", $this->sourceFields);
        }
        $fieldsWithoutFirstColumn = array_map(fn ($element) => $element, $this->sourceFields);
        array_shift($fieldsWithoutFirstColumn);
        return implode(",", $fieldsWithoutFirstColumn);
    }
}
