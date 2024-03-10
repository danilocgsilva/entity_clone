<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use Danilocgsilva\EntityClone\ReductionFields;

class QueryBuilder
{
    private array $sourceFields;

    private array $destinyFields;

    private ReductionFields $reductionFields;

    private bool $cloneId = false;

    public function __construct()
    {
        $this->reductionFields = new ReductionFields();
    }

    public function setOnCloneId(): self
    {
        $this->cloneId = true;
        return $this;
    }

    public function setSourceFields(array $sourceFields): self
    {
        $this->sourceFields = $sourceFields;
        return $this;
    }

    public function setDestinyFields(array $destinyFields): self
    {
        $this->destinyFields = $destinyFields;
        return $this;
    }

    
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
}
