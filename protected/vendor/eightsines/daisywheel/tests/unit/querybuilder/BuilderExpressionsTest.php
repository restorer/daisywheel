<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;

class BuilderExpressionsTest extends \PHPUnit_Framework_TestCase
{
    protected $builder;

    public function __construct()
    {
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::and_
     */
    public function testAnd()
    {
        $this->assertEquals('(1 AND 0)', $this->builder->and_(
            $this->builder->val(true),
            $this->builder->val(false)
        )->buildExpr());

        $this->assertEquals('(1 AND 0 AND 1)', $this->builder->and_(
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('(1 AND 0 AND 1)', $this->builder->and_([
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true),
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::and_
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testAndException()
    {
        $this->builder->and_($this->builder->val(true));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::or_
     */
    public function testOr()
    {
        $this->assertEquals('(1 OR 0)', $this->builder->or_(
            $this->builder->val(true),
            $this->builder->val(false)
        )->buildExpr());

        $this->assertEquals('(1 OR 0 OR 1)', $this->builder->or_(
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('(1 OR 0 OR 1)', $this->builder->or_([
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true),
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::or_
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testOrException()
    {
        $this->builder->or_($this->builder->val(true));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::eq
     */
    public function testEq()
    {
        $this->assertEquals('(1 = 0)', $this->builder->eq($this->builder->val(true), $this->builder->val(false))->buildExpr());
        $this->assertEquals('(1 IS NULL)', $this->builder->eq($this->builder->val(true), $this->builder->val(null))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::neq
     */
    public function testNeq()
    {
        $this->assertEquals('(1 <> 0)', $this->builder->neq($this->builder->val(true), $this->builder->val(false))->buildExpr());
        $this->assertEquals('(1 IS NOT NULL)', $this->builder->neq($this->builder->val(true), $this->builder->val(null))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::gt
     */
    public function testGt()
    {
        $this->assertEquals('(1 > 0)', $this->builder->gt($this->builder->val(true), $this->builder->val(false))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::gte
     */
    public function testGte()
    {
        $this->assertEquals('(1 >= 0)', $this->builder->gte($this->builder->val(true), $this->builder->val(false))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::lt
     */
    public function testLt()
    {
        $this->assertEquals('(1 < 0)', $this->builder->lt($this->builder->val(true), $this->builder->val(false))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::lte
     */
    public function testLte()
    {
        $this->assertEquals('(1 <= 0)', $this->builder->lte($this->builder->val(true), $this->builder->val(false))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::in
     */
    public function testIn()
    {
        $this->assertEquals(
            '(1 IN (0))',
            $this->builder->in($this->builder->val(true), [
                $this->builder->val(false),
            ])->buildExpr()
        );

        $this->assertEquals(
            '(1 IN (0, 1))',
            $this->builder->in($this->builder->val(true), [
                $this->builder->val(false),
                $this->builder->val(true),
            ])->buildExpr()
        );

        $this->assertEquals(
            '(1 = 2)',
            $this->builder->in($this->builder->val(true), [])->buildExpr()
        );

        $this->assertEquals(
            '([name] IN :name)',
            $this->builder->in($this->builder->col('name'), $this->builder->param(':name'))->buildExpr()
        );

        // TODO: test SelectCommand
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::in
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testInException()
    {
        $this->builder->in($this->builder->val(true), $this->builder->val(false));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::notIn
     */
    public function testNotIn()
    {
        $this->assertEquals(
            '(1 NOT IN (0))',
            $this->builder->notIn($this->builder->val(true), [
                $this->builder->val(false),
            ])->buildExpr()
        );

        $this->assertEquals(
            '(1 NOT IN (0, 1))',
            $this->builder->notIn($this->builder->val(true), [
                $this->builder->val(false),
                $this->builder->val(true),
            ])->buildExpr()
        );

        $this->assertEquals(
            '(1 = 1)',
            $this->builder->notIn($this->builder->val(true), [])->buildExpr()
        );

        $this->assertEquals(
            '([name] NOT IN :name)',
            $this->builder->notIn($this->builder->col('name'), $this->builder->param(':name'))->buildExpr()
        );

        // TODO: test SelectCommand
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::notIn
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testNotInException()
    {
        $this->builder->notIn($this->builder->val(true), $this->builder->val(false));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::isNull
     */
    public function testIsNull()
    {
        $this->assertEquals('(1 IS NULL)', $this->builder->isNull($this->builder->val(true))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::isNotNull
     */
    public function testIsNotNull()
    {
        $this->assertEquals('(1 IS NOT NULL)', $this->builder->isNotNull($this->builder->val(true))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::add
     */
    public function testAdd()
    {
        $this->assertEquals('(1 + 0)', $this->builder->add(
            $this->builder->val(true),
            $this->builder->val(false)
        )->buildExpr());

        $this->assertEquals('(1 + 0 + 1)', $this->builder->add(
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('(1 + 0 + 1)', $this->builder->add([
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true),
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::add
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testAddException()
    {
        $this->builder->add($this->builder->val(true));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::sub
     */
    public function testSub()
    {
        $this->assertEquals('(1 - 0)', $this->builder->sub(
            $this->builder->val(true),
            $this->builder->val(false)
        )->buildExpr());

        $this->assertEquals('(1 - 0 - 1)', $this->builder->sub(
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('(1 - 0 - 1)', $this->builder->sub([
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true),
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::sub
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testSubException()
    {
        $this->builder->sub($this->builder->val(true));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::mul
     */
    public function testMul()
    {
        $this->assertEquals('(1 * 0)', $this->builder->mul(
            $this->builder->val(true),
            $this->builder->val(false)
        )->buildExpr());

        $this->assertEquals('(1 * 0 * 1)', $this->builder->mul(
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('(1 * 0 * 1)', $this->builder->mul([
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true),
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::mul
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testMulException()
    {
        $this->builder->mul($this->builder->val(true));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::div
     */
    public function testDiv()
    {
        $this->assertEquals('(1 / 0)', $this->builder->div(
            $this->builder->val(true),
            $this->builder->val(false)
        )->buildExpr());

        $this->assertEquals('(1 / 0 / 1)', $this->builder->div(
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true)
        )->buildExpr());

        $this->assertEquals('(1 / 0 / 1)', $this->builder->div([
            $this->builder->val(true),
            $this->builder->val(false),
            $this->builder->val(true),
        ])->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::div
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testDivException()
    {
        $this->builder->div($this->builder->val(true));
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::div
     */
    public function testNeg()
    {
        $this->assertEquals('(- 1)', $this->builder->neg($this->builder->val(true))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::div
     */
    public function testNot()
    {
        $this->assertEquals('(NOT 1)', $this->builder->not($this->builder->val(true))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::like
     */
    public function testLike()
    {
        $this->assertEquals("([name] LIKE 'John%')", $this->builder->like($this->builder->col('name'), $this->builder->val('John%'))->buildExpr());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::between
     */
    public function testBetween()
    {
        $this->assertEquals("([created_at] BETWEEN '2016-01-01' AND '2017-01-01')", $this->builder->between(
            $this->builder->col('created_at'),
            $this->builder->val('2016-01-01'),
            $this->builder->val('2017-01-01')
        )->buildExpr());
    }
}
