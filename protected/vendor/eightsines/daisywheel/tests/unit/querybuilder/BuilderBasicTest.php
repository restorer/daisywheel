<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;

class BuilderBasicTest extends \PHPUnit_Framework_TestCase
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
     * @covers \daisywheel\querybuilder\QueryBuilder::val
     */
    public function testVal()
    {
        $this->assertEquals('NULL', $this->builder->val(null)->buildExpr());
        $this->assertEquals('0', $this->builder->val(false)->buildExpr());
        $this->assertEquals('1', $this->builder->val(true)->buildExpr());
        $this->assertEquals("'Test\\'s'", $this->builder->val("Test's")->buildExpr());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::val
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testValException()
    {
        $this->builder->val([]);
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::param
     */
    public function testParam()
    {
        $this->assertEquals(':param', $this->builder->param(':param')->buildExpr());
        $this->assertEquals(':param_25AZ', $this->builder->param(':param_25AZ')->buildExpr());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::param
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testParamException()
    {
        $this->builder->param('param');
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::col
     */
    public function testCol()
    {
        $this->assertEquals('*', $this->builder->col('*')->buildExpr());
        $this->assertEquals('[name]', $this->builder->col('name')->buildExpr());
        $this->assertEquals('[t].*', $this->builder->col('t', '*')->buildExpr());
        $this->assertEquals('[t].[name]', $this->builder->col('t', 'name')->buildExpr());
        $this->assertEquals('[qb_User].*', $this->builder->col($this->builder->tab('User'), '*')->buildExpr());
        $this->assertEquals('[qb_User].[name]', $this->builder->col($this->builder->tab('User'), 'name')->buildExpr());
        $this->assertEquals('[#qb_User].*', $this->builder->col($this->builder->temp('User'), '*')->buildExpr());

        $this->assertEquals(
            '[#qb_User].[name]',
            $this->builder->col($this->builder->temp('User'), 'name')->buildExpr()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::as_
     */
    public function testAs()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        /** @noinspection ClassConstantCanBeUsedInspection */
        $this->assertEquals(
            "'Test' AS [result]",
            $this->builder->as_($this->builder->val('Test'), 'result')->buildExpr()
        );

        $this->assertEquals(
            '[#qb_User].[name] AS [result]',
            $this->builder->as_($this->builder->col($this->builder->temp('User'), 'name'), 'result')->buildExpr()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::tab
     */
    public function testTab()
    {
        $this->assertEquals('[qb_User]', $this->builder->tab('User')->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::temp
     */
    public function testTemp()
    {
        $this->assertEquals('[#qb_User]', $this->builder->temp('User')->buildPart());
    }
}
