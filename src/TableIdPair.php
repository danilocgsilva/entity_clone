<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

class TableIdPair
{
    public function __construct(public readonly string $tableName, public readonly string $id) {}
}
