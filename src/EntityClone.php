<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

class EntityClone {
    
    private string $sourceDns;
    private string $sourceUser;
    private string $sourcePassword;
    private string $destinyDns;
    private string $destinyUser;
    private string $destinyPassword;
    
    public function setSourceDns(string $sourceDns): self
    {
        $this->sourceDns = $sourceDns;
        return $this;
    }

    public function setSourceUser(string $soureUser): self
    {
        $this->sourceUser = $soureUser;
        return $this;
    }

    public function setSourcePassword(string $sourcePassword): self
    {
        $this->sourcePassword = $sourcePassword;
        return $this;
    }

    public function setDestinyDns(string $destinyDns): self
    {
        $this->destinyDns = $destinyDns;
        return $this;
    }

    public function setDestinyUser(string $destinyUser): self
    {
        $this->destinyUser = $destinyUser;
        return $this;
    }

    public function setDestinyPassword(string $destinyPassword): self
    {
        $this->destinyPassword = $destinyPassword;
        return $this;
    }
}
