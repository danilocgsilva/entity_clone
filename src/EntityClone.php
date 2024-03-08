<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use PDO;
use Danilocgsilva\EntitiesDiscover\Entity;
use Danilocgsilva\EntitiesDiscover\ErrorLogInterface;
use Danilocgsilva\EntityClone\Traits\GetFields;
use Exception;

class EntityClone
{
    use GetFields;
    private string $table;
    private string $idValue;
    private array $sourceFields;
    private array $destinyFields;
    private bool $cloneId = false;
    private string $commonFieldsCommaSeparated;
    private ReductionFields $reductionFields;
    private ?string $filterField = null;
    private TimeDebugInterface|null $timeDebug = null;
    
    /**
     * @param PDO $sourcePdo
     * @param PDO $destinyPdo
     */
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

    /**
     * Clone the current table setted, and also searches fields along other
     * tables having the same primary key name from current table. Them,
     * searches for entries in these other tables that have the same value
     * setted in $idValue. This helps in the data concistency, so bring data
     * from other tables related to the current table. NOTE: the logic here
     * does not make any consideration to Foreign Keys.
     *
     * @param string $idValue
     * @return array
     */
    public function entityCloneDeepByFieldName(string $idValue): array
    {
        $this->entityClone($idValue);

        /** @var \Danilocgsilva\EntitiesDiscover\Entity $entity */
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

    private function getFirstFieldFromTable(string $table): string
    {
        $query = sprintf("DESCRIBE %s;", $table);
        $preResult = $this->sourcePdo->prepare($query);
        $preResult->execute();
        $firstFieldRow = $preResult->fetch(PDO::FETCH_ASSOC);
        return $firstFieldRow["Field"];
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
}
