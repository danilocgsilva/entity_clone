<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use Danilocgsilva\EntityClone\ReductionFields;
use Danilocgsilva\EntityClone\Traits\GetFields;
use PDO;
use Exception;

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

    private bool $ignoreInsertErrors = false;

    public function __construct()
    {
        $this->reductionFields = new ReductionFields();
    }

    public function setIgnoreInsertErrors(bool $ignore): self
    {
        $this->ignoreInsertErrors = $ignore;
        return $this;
    }

    /**
     * Instructs class to considers values in table id.
     *
     * @return self
     */
    public function setCloneId(bool $doCloneId): self
    {
        $this->cloneId = $doCloneId;
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

        $headerQuery = "INSERT";
        if ($this->ignoreInsertErrors) {
            $headerQuery .= " IGNORE";
        }
        $headerQuery .= " INTO %s (%s) VALUES %s;";

        return sprintf(
            $headerQuery, 
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

    public function getIds(string $filterValue)
    {
        $fieldValueFilter = $this->sourceFields[0];

        $getSourceDataQuery = sprintf(
            "SELECT %s FROM %s WHERE %s = :filterValue",
            $fieldValueFilter,
            $this->table,
            $fieldValueFilter
        );
    }

    /**
     * Returns the table ids based on value from other table field
     * 
     * @param string $table
     * @param string $idValue
     * @param string $fieldName
     * @return array
     */
    public function getCopyingIds(string $table, string $idValue, string $fieldName): array
    {
        $tableIdFieldName = $this->getFieldsFromTable($table)[0];
        var_dump($table, $idValue, $fieldName);
        $queryBase = sprintf("SELECT %s FROM %s WHERE %s = :idValue", $tableIdFieldName, $table, $fieldName);
        $preResults = $this->sourcePdo->prepare($queryBase);
        $preResults->execute([":idValue" => $idValue]);
        $preResults->setFetchMode(PDO::FETCH_NUM);
        $ids = [];
        while ($row = $preResults->fetch()) {
            $ids[] = $row[0];
        }
        return $ids;
    }

    /**
     * Get fields from a table. NOTE: The PDO must have declared table on
     *   its connection string.
     *
     * @param string $tableName
     * @return string[]
     */
    private function getFieldsFromTable(string $tableName): array
    {
        $databaseName = $this->sourcePdo->query('SELECT database()')->fetchColumn();

        if ($databaseName === null) {
            throw new Exception("The PDO object must have a database definition on its statement.");
        }

        $baseQuery = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = :tableschema AND TABLE_NAME = :tablename
            ORDER BY ordinal_position;";

        $preResults = $this->sourcePdo->prepare($baseQuery);
        $preResults->execute([
            ':tableschema' => $databaseName,
            ':tablename' => $tableName,
        ]);
        $fields = [];
        while ($row = $preResults->fetch(PDO::FETCH_ASSOC)) {
            $fields[] = $row['COLUMN_NAME'];
        }
        return $fields;
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
