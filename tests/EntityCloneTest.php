<?php

declare(strict_types=1);

namespace Tests\EntityCloneTest;

use PHPUnit\Framework\TestCase;
use Danilocgsilva\EntityClone\EntityClone;

class EntityCloneTest extends TestCase
{
    private EntityClone $entityClone;
    
    public function setUp(): void
    {
        $this->entityClone = new EntityClone();
    }
    
    public function testGetAllConnectionsData(): void
    {
        $this->entityClone->setSourceDns('my.dns.com');
        $this->entityClone->setSourceUser('Jonhy');
        $this->entityClone->setDestinyDns('local.dns.com');
        $this->entityClone->setDestinyUser('Doen');
        $result = $this->entityClone->getAllConnectionsData();
        $this->assertSame('my.dns.com', $result['source-dns']);
        $this->assertSame('Jonhy', $result['source-user']);
        $this->assertSame('local.dns.com', $result['destiny-dns']);
        $this->assertSame('Doen', $result['destiny-user']);
    }
}
