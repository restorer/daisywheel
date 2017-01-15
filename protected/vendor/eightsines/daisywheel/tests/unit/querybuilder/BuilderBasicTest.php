<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;

class BuilderBasicTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryBuilder */
    protected $builder;

    public function __construct()
    {
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::val
     */
    public function testVal()
    {
        $this->assertEquals('NULL', $this->builder->val(null)->buildExpr());
        $this->assertEquals('0', $this->builder->val(false)->buildExpr());
        $this->assertEquals('1', $this->builder->val(true)->buildExpr());
        $this->assertEquals("'Test\\'s'", $this->builder->val("Test's")->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::val
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testValException()
    {
        $this->builder->val([]);
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::param
     */
    public function testParam()
    {
        $this->assertEquals(':param', $this->builder->param(':param')->buildExpr());
        $this->assertEquals(':param_25AZ', $this->builder->param(':param_25AZ')->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::param
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testParamException()
    {
        $this->builder->param('param');
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::col
     */
    public function testCol()
    {
        $this->assertEquals('*', $this->builder->col('*')->buildExpr());
        $this->assertEquals('[name]', $this->builder->col('name')->buildExpr());
        $this->assertEquals('[User].*', $this->builder->col('User', '*')->buildExpr());
        $this->assertEquals('[User].[name]', $this->builder->col('User', 'name')->buildExpr());
        $this->assertEquals('[#User].*', $this->builder->col($this->builder->temp('User'), '*')->buildExpr());
        $this->assertEquals('[#User].[name]', $this->builder->col($this->builder->temp('User'), 'name')->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::as_
     */
    public function testAs()
    {
        $this->assertEquals("'Test' AS [result]", $this->builder->as_($this->builder->val('Test'), 'result')->buildExpr());

        $this->assertEquals(
            '[#User].[name] AS [result]',
            $this->builder->as_($this->builder->col($this->builder->temp('User'), 'name'), 'result')->buildExpr()
        );
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::temp
     */
    public function testTemp()
    {
        $this->assertEquals('[#User]', $this->builder->temp('User')->buildPart());
    }
}
