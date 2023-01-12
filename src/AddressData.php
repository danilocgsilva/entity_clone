<?php

declare(strict_types=1);

namespace Danilocgsilva\EntityClone;

class AddressData
{
    private string $dns;
    private string $password;
    private string $user;
    
    public function setDns(string $dns): self
    {
        $this->dns = $dns;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getDns(): string
    {
        return $this->dns;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUser(): string
    {
        return $this->user;
    }
}
