<?php

declare(strict_types=1);

namespace Tests\Tables;

abstract class TablesAbstract
{
    protected string $tableName;
    
    abstract public static function createTableQuery(): string;
    
    abstract public static function createInsertQuery(): string;

    public function getTableName(): string
    {
        return $this->tableName;
    }

    protected static function getQuery(string $queryPath): string
    {
        $queryFile = fopen($queryPath, "r");
        $query = stream_get_contents($queryFile);
        return $query;
    }
}
