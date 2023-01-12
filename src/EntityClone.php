<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

use AddressData;

class EntityClone {
    
    private AddressData $source;
    private AddressData $destiny;
    
    public function setSource(AddressData $source): self
    {
        $this->source = $source;
        return $this;
    }

    public function setDestiny(AddressData $destiny): self
    {
        $this->destiny = $destiny;
        return $this;
    }

    public function discoverEntity(string $entity): void
    {
        
    }
}
