<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;

class BuilderMiscTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryBuilder */
    protected $builder;

    /**
     * @see \PHPUnit_Framework_TestCase::__construct()
     * @inheritdoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::__call
     */
    public function testMagicCall()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        /** @noinspection ClassConstantCanBeUsedInspection */
        $this->assertEquals(
            "'Test' AS [result]",
            $this->builder->as($this->builder->val('Test'), 'result')->buildExpr()
        );
    }
}
