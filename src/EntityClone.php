<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use PDO;
use Danilocgsilva\EntitiesDiscover\Entity;
use Danilocgsilva\EntitiesDiscover\ErrorLogInterface;
use Exception;

class EntityClone
{
    private string $table;
    private string $idValue;
    private array $sourceFields;
    private array $destinyFields;
    private bool $cloneId = false;
    private string $commonFieldsCommaSeparated;
    private ReductionFields $reductionFields;
    private ?string $filterField = null;
    private TimeDebugInterface|null $timeDebug = null;
    
    public function __construct(
        private PDO $sourcePdo,
        private PDO $destinyPdo
    ) {
        $this->reductionFields = new ReductionFields();
    }

    public function setTimeDebug(TimeDebugInterface $timeDebug): self
    {
        $this->timeDebug = $timeDebug;
        return $this;
    }

    public function setOnCloneId(): self
    {
        $this->cloneId = true;
        return $this;
    }

    public function setFilterField(string $filterField): self
    {
        $this->filterField = $filterField;
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

        if ($this->timeDebug) {
            $this->timeDebug->message("Time before table insert in destiny. Table: " . $this->table);
        }

        $resultsInsertion = $resResults->execute();

        if ($this->timeDebug) {
            $this->timeDebug->message("Insert finished. Table: " . $this->table);
        }
        
        return [
            'success' => $resultsInsertion,
            'reducedFields' => $this->reductionFields
        ];
    }

    public function entityCloneDeepByFieldName(string $idValue): array
    {
        $this->entityClone($idValue);

        $entity = new Entity(
            new class() implements ErrorLogInterface { 
                function message($message) {} 
            }
        );

        if ($this->timeDebug) {
            $entity->setTimeDebug($this->timeDebug);
        }

        $entity->setPdo($this->sourcePdo);
        $occurrencesFromOtherTables = 
            $entity->discoverEntitiesOccurrencesByIdentity($this->table, $idValue);
        $occurrencesNonZeroCounts = array_filter(
            $occurrencesFromOtherTables, 
            fn ($occurrence) => $occurrence > 0
        );
        $results = [
            'success' => [],
            'fails' => []
        ];
        foreach ($occurrencesNonZeroCounts as $table => $count) {
            $entityCloneTableLoop = new self($this->sourcePdo, $this->destinyPdo);
            $entityCloneTableLoop->setTable($table);
            $entityCloneTableLoop->setOnCloneId();
            $entityCloneTableLoop->setFilterField($this->sourceFields[0]);
            $results['success'][] = $table;

            try {
                $entityCloneTableLoop->entityClone($this->idValue);
            } catch (Exception $e) {
                $results['fails'][] = $table;
            }
        }
        return $results;
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
        $sourceValuesAsString = $this->getSourceValuesAsString($this->idValue);

        return sprintf(
            "INSERT INTO %s (%s) VALUES %s;", 
            $this->table, 
            $this->commonFieldsCommaSeparated, 
            $sourceValuesAsString
        );
    }

    private function getSourceValuesAsString(string $filterValue): string
    {
        $fieldValueFilter = $this->filterField ?: $this->sourceFields[0];
        
        $getSourceDataQuery = sprintf(
            "SELECT %s FROM %s WHERE %s = :filterValue", 
            $this->commonFieldsCommaSeparated,
            $this->table,
            $fieldValueFilter
        );

        $preResults = $this->sourcePdo->prepare($getSourceDataQuery);

        if ($this->timeDebug) {
            $this->timeDebug->message("Will get source data from " . $this->table . '.');
        }

        $preResults->execute([
            ':filterValue' => $filterValue
        ]);

        if ($this->timeDebug) {
            $this->timeDebug->message("Data just fetched from " . $this->table . ".");
        }

        $rowQueryStringData = [];
        while ($rowData = $preResults->fetch(PDO::FETCH_NUM)) {
            $rowDataStrings = $this->convertDataResultToSuitableString($rowData);
            $rowQueryStringData[] = "(" . implode(",", $rowDataStrings) . ")";
        }

        return implode(", ", $rowQueryStringData);
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

    private function getFirstFieldFromTable(string $table): string
    {
        $query = sprintf("DESCRIBE %s;", $table);
        $preResult = $this->sourcePdo->prepare($query);
        $preResult->execute();
        $firstFieldRow = $preResult->fetch(PDO::FETCH_ASSOC);
        return $firstFieldRow["Field"];
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
