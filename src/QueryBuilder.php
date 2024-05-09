<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use Danilocgsilva\EntityClone\ReductionFields;
use Danilocgsilva\EntityClone\Traits\GetFields;
use PDO;

class QueryBuilder
{
    use GetFields;
    
    private array $sourceFields;

    private array $destinyFields;

    private ReductionFields $reductionFields;

    private bool $cloneId;

    private string $commonFieldsCommaSeparated;

    private string $table;

    private string $idValue;

    private PDO $sourcePdo;

    private TimeDebugInterface|null $timeDebug = null;

    private ?string $filterField = null;

    public function __construct()
    {
        $this->reductionFields = new ReductionFields();
    }

    /**
     * Instructs class to considers values in table id.
     *
     * @return self
     */
    public function setOnCloneId(): self
    {
        $this->cloneId = true;
        return $this;
    }

    public function setIdValue(string $idValue): self
    {
        $this->idValue = $idValue;
        return $this;
    }

    /**
     * @param string[] $sourceFields
     * @return self
     */
    public function setSourceFields(array $sourceFields): self
    {
        $this->sourceFields = $sourceFields;
        return $this;
    }

    public function setSourcePdo(PDO $sourcePdo): self
    {
        $this->sourcePdo = $sourcePdo;
        return $this;
    }

    public function setTable(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param string[] $sourceFields
     * @return self
     */
    public function setDestinyFields(array $destinyFields): self
    {
        $this->destinyFields = $destinyFields;
        return $this;
    }

    /**
     * Return reduced fields
     *
     * @return string[]
     */
    public function reduceFields(): array
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

    public function getReductionFields(): ReductionFields
    {
        return $this->reductionFields;
    }

    public function createInsertQuery(): string
    {
        $this->setSourceFields($this->sourceFields)
            ->setDestinyFields($this->destinyFields);

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

    /**
     * Chnages the field criteria other than the table id for fetching data
     *
     * @param string $filterField
     * @return self
     */
    public function setFilterField(string $filterField): self
    {
        $this->filterField = $filterField;
        return $this;
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
