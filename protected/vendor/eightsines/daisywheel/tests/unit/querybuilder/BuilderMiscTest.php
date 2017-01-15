<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;

class BuilderMiscTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryBuilder */
    protected $builder;

    public function __construct()
    {
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::__call
     */
    public function testMagicCall()
    {
        $this->assertEquals("'Test' AS [result]", $this->builder->as($this->builder->val('Test'), 'result')->buildExpr());
    }
}
