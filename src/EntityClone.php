<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use PDO;

class EntityClone
{
    private string $table;
    private string $idValue;
    private array $sourceFields;
    private array $destinyFields;
    private bool $cloneId = false;
    private string $commonFieldsCommaSeparated;
    
    public function __construct(
        private PDO $sourcePdo,
        private PDO $destinyPdo,
        private ReductionFields $reductionFields
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

    public function entityClone(string $idValue): array
    {
        $this->idValue = $idValue;
        $this->sourceFields = $this->getFields($this->sourcePdo);
        $this->destinyFields = $this->getFields($this->destinyPdo);
        
        $insertQuery = $this->createInsertQuery();

        $resResults = $this->destinyPdo->prepare($insertQuery);
        $resultsInsertion = $resResults->execute();
        return [
            'success' => $resultsInsertion,
            'reducedFields' => $this->reductionFields
        ];
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
        $commonFields = $this->reduceFields();
        $this->commonFieldsCommaSeparated = implode(", ", $commonFields);
        $sourceValuesAsString = $this->getSourceValuesAsString();

        return sprintf(
            "INSERT INTO %s (%s) VALUES (%s);", 
            $this->table, 
            $this->commonFieldsCommaSeparated, 
            $sourceValuesAsString
        );
    }

    private function getSourceValuesAsString(): string
    {
        $getSourceDataQuery = sprintf(
            "SELECT %s FROM %s WHERE %s = :id", 
            $this->commonFieldsCommaSeparated,
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
}
