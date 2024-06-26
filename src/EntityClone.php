<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use PDO;
use Danilocgsilva\EntitiesDiscover\Entity;
use Danilocgsilva\EntitiesDiscover\ErrorLogInterface;
use Danilocgsilva\EntityClone\Traits\GetFields;
use Danilocgsilva\EntityClone\QueryBuilder;
use Danilocgsilva\EntitiesDiscover\TimeDebugInterface as EDTimeDebugInterface;
use Exception;

class EntityClone
{
    use GetFields;

    private string|null $table = null;

    private string $idValue;

    private array $sourceFields;

    private array $destinyFields;

    // private bool $cloneIdSetted = false;

    private bool|null $cloneId = null;

    private string $commonFieldsCommaSeparated;

    private ?string $filterField = null;

    private TimeDebugInterface|EDTimeDebugInterface|null $timeDebug = null;

    private QueryBuilder $queryBuilder;

    private bool $ignoreInsertError = false;

    private array $skipTablesNames = [];
    
    /**
     * @param PDO $sourcePdo
     * @param PDO $destinyPdo
     */
    public function __construct(
        private PDO $sourcePdo,
        private PDO $destinyPdo
    ) {
        $this->queryBuilder = new QueryBuilder();
    }

    public function setSkipTables(array $skipTableNames): self
    {
        $this->skipTablesNames = $skipTableNames;
        return $this;
    }

    public function setIgnoreInsertError(): self
    {
        $this->ignoreInsertError = true;
        return $this;
    }

    public function setTimeDebug(TimeDebugInterface|EDTimeDebugInterface $timeDebug): self
    {
        $this->timeDebug = $timeDebug;
        return $this;
    }

    /**
     * Instructs to application know if is required to clone the entity id.
     *
     * @param boolean $doCloneId
     * @return self
     */
    public function setCloneId(bool $doCloneId): self
    {
        $this->queryBuilder->setCloneId($doCloneId);
        $this->cloneId = $doCloneId;
        return $this;
    }

    /**
     * Chnages the search to an altenative field other than the table id.
     *
     * @param string $filterField
     * @return self
     */
    public function setFilterField(string $filterField): self
    {
        $this->filterField = $filterField;
        return $this;
    }

    // public function setOffCloneId(): self
    // {
    //     $this->cloneId = false;
    //     return $this;
    // }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Clone data from $sourcePdo to $sourcePdo based on the if from entity
     *
     * @param string $idValue
     * @return array
     */
    public function entityClone(string $idValue): array
    {
        $this->idValue = $idValue;
        $this->sourceFields = $this->getFields($this->sourcePdo);
        $this->destinyFields = $this->getFields($this->destinyPdo);

        if (count($this->sourceFields) === 0) {
            if ($this->timeDebug) {
                $this->timeDebug->message("Warning! Table " . $this->table . " does not exists in source.");
            }
            return [
                'success' => false,
                'reducedFields' => []
            ];
        }

        if (count($this->destinyFields) === 0) {
            if ($this->timeDebug) {
                $this->timeDebug->message("Warning! Table " . $this->table . " does not exists in destiny.");
            }
            return [
                'success' => false,
                'reducedFields' => []
            ];
        }
        
        $this->queryBuilder->setIgnoreInsertErrors($this->ignoreInsertError);
        $this->queryBuilder->setSourceFields($this->sourceFields);
        $this->queryBuilder->setDestinyFields($this->destinyFields);
        $this->queryBuilder->setIdValue($this->idValue);
        $this->queryBuilder->setTable($this->table);
        $this->queryBuilder->setSourcePdo($this->sourcePdo);
        if ($this->filterField) {
            $this->queryBuilder->setFilterField($this->filterField);
        }

        $insertQuery = $this->queryBuilder->createInsertQuery();

        $resResults = $this->destinyPdo->prepare($insertQuery);

        if ($this->timeDebug) {
            $this->timeDebug->message("Time before table insert in destiny. Table: " . $this->table);
        }

        try {
            $resultsInsertion = $resResults->execute();
        } catch (Exception $e) {
            $resultsInsertion = false;
            if ($this->timeDebug) {
                $this->timeDebug->message("Error of copying: " . $e->getMessage() . ", exception class: " . get_class($e));
            }
            return [
                'success' => false,
                'reducedFields' => $this->queryBuilder->getReductionFields()
            ];
        }

        if ($this->timeDebug) {
            $this->timeDebug->message("Insert finished. Table: " . $this->table);
        }
        
        return [
            'success' => $resultsInsertion,
            'reducedFields' => $this->queryBuilder->getReductionFields()
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
        if ($this->table === null) {
            throw new Exception("You have not setted the table to clone. Use setTable method.");
        }
        if (!$this->cloneId) {
            throw new Exception("It is required to tell to the object if the id clone will be done. Use setCloneId(true|false) method before making the clone.");
        }

        $this->entityClone($idValue);

        /** @var \Danilocgsilva\EntitiesDiscover\Entity $entity */
        $entity = new Entity(
            new class() implements ErrorLogInterface { 
                function message($message) {} 
            }
        );

        $entity->setSkipTables($this->skipTablesNames);

        if ($this->timeDebug) {
            $entity->setTimeDebug($this->timeDebug);
        }

        $entity->setPdo($this->sourcePdo);
        $occurrencesFromOtherTables = 
            $entity->discoverEntitiesOccurrencesByIdentitySync($this->table, $idValue);

        $occurrencesNonZeroCounts = array_filter(
            $occurrencesFromOtherTables->getSuccesses(), 
            fn ($occurrence) => $occurrence > 0
        );

        $results = [
            'success' => [],
            'fails' => []
        ];

        foreach ($occurrencesNonZeroCounts as $table => $count) {
            $entityCloneTableLoop = new self($this->sourcePdo, $this->destinyPdo);
            $entityCloneTableLoop->setTable($table);
            $entityCloneTableLoop->setCloneId($this->cloneId);
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

    private function getFirstFieldFromTable(string $table): string
    {
        $query = sprintf("DESCRIBE %s;", $table);
        $preResult = $this->sourcePdo->prepare($query);
        $preResult->execute();
        $firstFieldRow = $preResult->fetch(PDO::FETCH_ASSOC);
        return $firstFieldRow["Field"];
    }
}
