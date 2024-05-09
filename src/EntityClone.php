<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use PDO;
use Danilocgsilva\EntitiesDiscover\Entity;
use Danilocgsilva\EntitiesDiscover\ErrorLogInterface;
use Danilocgsilva\EntityClone\Traits\GetFields;
use Danilocgsilva\EntityClone\QueryBuilder;
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

    private ?string $filterField = null;

    private TimeDebugInterface|null $timeDebug = null;

    private QueryBuilder $queryBuilder;
    
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

    public function setTimeDebug(TimeDebugInterface $timeDebug): self
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

        $resultsInsertion = $resResults->execute();

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

    private function getFirstFieldFromTable(string $table): string
    {
        $query = sprintf("DESCRIBE %s;", $table);
        $preResult = $this->sourcePdo->prepare($query);
        $preResult->execute();
        $firstFieldRow = $preResult->fetch(PDO::FETCH_ASSOC);
        return $firstFieldRow["Field"];
    }
}
