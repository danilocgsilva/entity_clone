<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

class ReductionFields
{
    private array $reducedDestiny = [];
    private array $reducedSource = [];
    
    public function addReducedDestiny(string $reducedDestiny): self
    {
        $this->reducedDestiny[] = $reducedDestiny;
        return $this;
    }

    public function addReducedSource(string $reducedSource): self
    {
        $this->reducedSource[] = $reducedSource;
        return $this;
    }

    public function hasReducedFields(): bool
    {
        return !empty($this->reducedDestiny) && !empty($this->reducedSource);
    }

    public function getReducedDestiny(): array
    {
        return $this->reducedDestiny;
    }

    public function getReducedSource(): array
    {
        return $this->reducedSource;
    }
}
