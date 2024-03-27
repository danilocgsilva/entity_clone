<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use Danilocgsilva\EntityClone\Traits\GetFields;
use PDO;
use Danilocgsilva\EntityClone\QueryBuilder;

class EntityCloneMultiples
{
    use GetFields;

    private ReductionFields $reductionFields;

    private string $table;

    private array $sourceFields;

    private array $destinyFields;

    private bool $cloneId = false;

    private array $ids;

    private string $commonFieldsCommaSeparated;

    private TimeDebugInterface|null $timeDebug = null;

    private QueryBuilder $queryBuilder;

    public function __construct(
        private PDO $sourcePdo,
        private PDO $destinyPdo
    ) {
        $this->reductionFields = new ReductionFields();
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function entityCloneMultiples(array $ids)
    {
        if (count($ids) === 0) {
            throw new EntityCloneException("You may not use an empty array as ids strings.");
        }
        
        $this->ids = $ids;
        $this->sourceFields = $this->getFields($this->sourcePdo);
        $this->destinyFields = $this->getFields($this->destinyPdo);
        
        $insertQuery = $this->createInsertQueryMultiples();
    
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

    private function createInsertQueryMultiples(): string
    {
        $this->queryBuilder = new QueryBuilder();
        $this->queryBuilder->setDestinyFields($this->destinyFields);
        $this->queryBuilder->setSourceFields($this->sourceFields);
        
        $commonFields = $this->queryBuilder->reduceFields();
        $this->commonFieldsCommaSeparated = implode(", ", $commonFields);
        $sourceValuesAsString = $this->getSourceValuesAsStringMultiple($this->ids);

        return sprintf(
            "INSERT INTO %s (%s) VALUES %s;", 
            $this->table, 
            $this->commonFieldsCommaSeparated, 
            $sourceValuesAsString
        );
    }

    private function getSourceValuesAsStringMultiple(array $ids): string
    {
        $getSourceDataQuery = sprintf(
            "SELECT %s FROM %s WHERE %s IN (%s)", 
            $this->commonFieldsCommaSeparated,
            $this->table,
            $this->sourceFields[0],
            implode(",", $ids)
        );

        $preResults = $this->sourcePdo->prepare($getSourceDataQuery);

        if ($this->timeDebug) {
            $this->timeDebug->message("Will get source data from " . $this->table . '.');
        }

        $preResults->execute();

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