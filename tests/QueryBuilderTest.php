<?php

declare(strict_types=1);

namespace Tests;

use Danilocgsilva\EntityClone\QueryBuilder;
use PHPUnit\Framework\TestCase;
use ArrayObject;

class QueryBuilderTest extends TestCase
{
    private QueryBuilder $queryBuilder;
    
    public function setUp(): void
    {
        $this->queryBuilder = new QueryBuilder();
    }
    
    public function testQueryBuild(): void
    {
        $sourceFields = $this->getSampleSourceFields();
        $destinyFields = $this->getSampleDestinyFields();
        $this->queryBuilder->setSourceFields($sourceFields);
        $this->queryBuilder->setDestinyFields($destinyFields);

        $resultedFields = $this->queryBuilder->reduceFields();

        $fieldsExpectedFromReduction = [
            'flag', 
            'payment', 
            'delivery', 
            'operation', 
            'height', 
            'width'
        ];

        $this->assertCount(6, $resultedFields);

        foreach ($fieldsExpectedFromReduction as $fieldsFromReduction) {
            $this->assertTrue(in_array($fieldsFromReduction, $fieldsExpectedFromReduction));
        }
    }

    public function testQueryBuildConsidersId()
    {
        $sourceFields = $this->getSampleSourceFields();
        $destinyFields = $this->getSampleDestinyFields();
        $this->queryBuilder->setOnCloneId();
        $this->queryBuilder->setSourceFields($sourceFields);
        $this->queryBuilder->setDestinyFields($destinyFields);

        $resultedFields = $this->queryBuilder->reduceFields();

        $fieldsExpectedFromReduction = [
            'tax',
            'flag', 
            'payment', 
            'delivery', 
            'operation', 
            'height', 
            'width'
        ];

        $this->assertCount(7, $resultedFields);

        foreach ($fieldsExpectedFromReduction as $fieldsFromReduction) {
            $this->assertTrue(in_array($fieldsFromReduction, $fieldsExpectedFromReduction));
        }
    }

    /**
     * @return string[]
     */
    private function getSampleSourceFields(): array
    {
        $sourceFields = [
            'tax', 
            'flag', 
            'payment', 
            'delivery', 
            'operation', 
            'weight', 
            'height', 
            'width'
        ];

        $sourceFieldsCopied = new ArrayObject($sourceFields);

        return $sourceFieldsCopied->getArrayCopy();
    }

    /**
     * @return string[]
     */
    private function getSampleDestinyFields(): array
    {
        $destinyFields = [
            'tax', 
            'flag', 
            'payment', 
            'delivery', 
            'operation', 
            'matching', 
            'height', 
            'width'
        ];

        $destinyFieldsCopied = new ArrayObject($destinyFields);

        return $destinyFieldsCopied->getArrayCopy();
    }
}
