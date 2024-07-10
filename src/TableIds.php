<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

class TableIds
{
    private array $ids; 
    
    public function __construct(public readonly string $tableName) {}

    public function addIds(array $ids)
    {
        $this->ids = $ids;
    }
}
