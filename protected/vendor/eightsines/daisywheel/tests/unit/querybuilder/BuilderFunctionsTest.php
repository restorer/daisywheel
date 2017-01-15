<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;

class BuilderFunctionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryBuilder */
    protected $builder;

    public function __construct()
    {
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::avg
     */
    public function testAvg()
    {
        $this->assertEquals('AVG([likes])', $this->builder->avg($this->builder->col('likes'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::count
     */
    public function testCount()
    {
        $this->assertEquals('AVG(*)', $this->builder->avg($this->builder->col('*'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::max
     */
    public function testMax()
    {
        $this->assertEquals('MAX([likes])', $this->builder->max($this->builder->col('likes'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::min
     */
    public function testMin()
    {
        $this->assertEquals('MIN([likes])', $this->builder->min($this->builder->col('likes'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::sum
     */
    public function testSum()
    {
        $this->assertEquals('SUM([likes])', $this->builder->sum($this->builder->col('likes'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::coalesce
     */
    public function testCoalesce()
    {
        $this->assertEquals('COALESCE([likes])', $this->builder->coalesce($this->builder->col('likes'))->buildExpr());

        $this->assertEquals('COALESCE([likes], 1)', $this->builder->coalesce(
            $this->builder->col('likes'),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('COALESCE([likes], NULL, 1)', $this->builder->coalesce(
            $this->builder->col('likes'),
            $this->builder->val(null),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('COALESCE([likes], NULL, 1)', $this->builder->coalesce([
            $this->builder->col('likes'),
            $this->builder->val(null),
            $this->builder->val(true)
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::abs
     */
    public function testAbs()
    {
        $this->assertEquals('ABS([likes])', $this->builder->abs($this->builder->col('likes'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::round
     */
    public function testRound()
    {
        $this->assertEquals('ROUND([likes])', $this->builder->round($this->builder->col('likes'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::concat
     */
    public function testConcat()
    {
        $this->assertEquals("('This')", $this->builder->concat($this->builder->val('This'))->buildExpr());

        $this->assertEquals("('This' || 'is')", $this->builder->concat(
            $this->builder->val('This'),
            $this->builder->val('is')
        )->buildExpr());

        $this->assertEquals("('This' || 'is' || 'the test')", $this->builder->concat(
            $this->builder->val('This'),
            $this->builder->val('is'),
            $this->builder->val('the test')
        )->buildExpr());

        $this->assertEquals("('This' || 'is' || 'the test')", $this->builder->concat([
            $this->builder->val('This'),
            $this->builder->val('is'),
            $this->builder->val('the test'),
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::length
     */
    public function testLength()
    {
        $this->assertEquals('LEN([name])', $this->builder->length($this->builder->col('name'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::lower
     */
    public function testLower()
    {
        $this->assertEquals('LOWER([name])', $this->builder->lower($this->builder->col('name'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::ltrim
     */
    public function testLtrim()
    {
        $this->assertEquals('LTRIM([name])', $this->builder->ltrim($this->builder->col('name'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::rtrim
     */
    public function testRtrim()
    {
        $this->assertEquals('RTRIM([name])', $this->builder->rtrim($this->builder->col('name'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::substr
     */
    public function testSubstr()
    {
        $this->assertEquals("SUBSTRING([name], '2')", $this->builder->substr(
            $this->builder->col('name'),
            $this->builder->val(2)
        )->buildExpr());

        $this->assertEquals("SUBSTRING([name], '2', '3')", $this->builder->substr(
            $this->builder->col('name'),
            $this->builder->val(2),
            $this->builder->val(3)
        )->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::trim
     */
    public function testTrim()
    {
        $this->assertEquals('LTRIM(RTRIM([name]))', $this->builder->trim($this->builder->col('name'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::upper
     */
    public function testUpper()
    {
        $this->assertEquals('UPPER([name])', $this->builder->upper($this->builder->col('name'))->buildExpr());
    }
}
