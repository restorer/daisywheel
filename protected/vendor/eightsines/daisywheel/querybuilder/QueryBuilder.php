<?php

namespace daisywheel\querybuilder;

use daisywheel\querybuilder\ast\Table;
use daisywheel\querybuilder\ast\commands\DropIndexCommand;
use daisywheel\querybuilder\ast\commands\DropTableCommand;
use daisywheel\querybuilder\ast\commands\TruncateTableCommand;
use daisywheel\querybuilder\ast\expr\AliasExpr;
use daisywheel\querybuilder\ast\expr\BasicExpr;
use daisywheel\querybuilder\ast\expr\ColumnExpr;
use daisywheel\querybuilder\ast\expr\FunctionExpr;
use daisywheel\querybuilder\ast\expr\PlaceholderExpr;
use daisywheel\querybuilder\ast\expr\ValueExpr;

class QueryBuilder
{
    /** @var BuildSpec */
    protected $spec;

    /**
     * @param $spec BuildSpec
     */
    public function __construct($spec)
    {
        $this->spec = $spec;
    }

    /**
     * @return SelectCommand
     */
    public function select()
    {
        // return new SelectCommand();
    }

    /**
     * @return InsertCommand
     */
    public function insertInto()
    {
        // return new InsertCommand();
    }

    /**
     * @return UpdateCommand
     */
    public function update()
    {
        // return new UpdateCommand();
    }

    /**
     * @return DeleteCommand
     */
    public function deleteFrom()
    {
        // return new DeleteCommand();
    }

    /**
     * @return CreateTableCommand
     */
    public function createTable()
    {
        // return new CreateTableCommand();
    }

    /**
     * @param $name string|Table
     * @return DropTableCommand
     */
    public function dropTable($nameOrTable)
    {
        return new DropTableCommand($this->spec, Table::create($this->spec, $nameOrTable));
    }

    /**
     * @param $name string|Table
     * @return TruncateTableCommand
     */
    public function truncateTable($nameOrTable)
    {
        return new TruncateTableCommand($this->spec, Table::create($this->spec, $nameOrTable));
    }

    /**
     * @return CreateIndexCommand
     */
    public function createIndex()
    {
        // return new CreateIndexCommand();
    }

    /**
     * @param $name string
     * @return DropIndexCommand
     */
    public function dropIndex($name)
    {
        return new DropIndexCommand($this->spec, $name);
    }

    //
    // Basic
    //

    /**
     * @param $value mixed
     * @return ValueExpr
     */
    public function val($value)
    {
        return new ValueExpr($this->spec, $value);
    }

    /**
     * @param $name string
     * @return PlaceholderExpr
     */
    public function param($name)
    {
        return new PlaceholderExpr($name);
    }

    /**
     * @param $columnOrTable string|Table
     * @param $column string|null
     * @return ColumnExpr
     */
    public function col($nameOrTable, $name = null)
    {
        if ($name === null) {
            return new ColumnExpr($this->spec, $nameOrTable);
        } else {
            return new ColumnExpr($this->spec, $name, Table::create($this->spec, $nameOrTable));
        }
    }

    /**
     * @param $expr Expr
     * @param $alias string
     * @return AliasExpr
     */
    public function as_($expr, $alias)
    {
        return new AliasExpr($this->spec, $expr, $alias);
    }

    /**
     * @param $name string
     * @return Table
     */
    public function temp($name)
    {
        return Table::create($this->spec, $name, true);
    }

    //
    // Expressions
    //

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return BasicExpr
     */
    public function and_($a, $b = null)
    {
        return BasicExpr::createMulti('AND', BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return BasicExpr
     */
    public function or_($a, $b = null)
    {
        return BasicExpr::createMulti('OR', BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @return BasicExpr
     */
    public function eq($a, $b)
    {
        return BasicExpr::createEq('=', [$a, $b], 'IS NULL');
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @return BasicExpr
     */
    public function neq($a, $b)
    {
        return BasicExpr::createEq('<>', [$a, $b], 'IS NOT NULL');
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @return BasicExpr
     */
    public function gt($a, $b)
    {
        return BasicExpr::createBinary('>', [$a, $b]);
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @return BasicExpr
     */
    public function gte($a, $b)
    {
        return BasicExpr::createBinary('>=', [$a, $b]);
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @return BasicExpr
     */
    public function lt($a, $b)
    {
        return BasicExpr::createBinary('<', [$a, $b]);
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @return BasicExpr
     */
    public function lte($a, $b)
    {
        return BasicExpr::createBinary('<=', [$a, $b]);
    }

    /**
     * @param $a Expr
     * @param $b Expr[]|PlaceholderExpr|SelectCommand
     * @return BasicExpr
     */
    public function in($a, $b)
    {
        return BasicExpr::createList('IN', [$a, $b], '1 = 2');
    }

    /**
     * @param $a Expr
     * @param $b Expr[]|PlaceholderExpr|SelectCommand
     * @return BasicExpr
     */
    public function notIn($a, $b)
    {
        return BasicExpr::createList('NOT IN', [$a, $b], '1 = 1');
    }

    /**
     * @param $a Expr
     * @return BasicExpr
     */
    public function isNull($a)
    {
        return BasicExpr::createRightHand('IS NULL', [$a]);
    }

    /**
     * @param $a Expr
     * @return BasicExpr
     */
    public function isNotNull($a)
    {
        return BasicExpr::createRightHand('IS NOT NULL', [$a]);
    }

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return BasicExpr
     */
    public function add($a, $b = null)
    {
        return BasicExpr::createMulti('+', BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return BasicExpr
     */
    public function sub($a, $b = null)
    {
        return BasicExpr::createMulti('-', BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return BasicExpr
     */
    public function mul($a, $b = null)
    {
        return BasicExpr::createMulti('*', BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return BasicExpr
     */
    public function div($a, $b = null)
    {
        return BasicExpr::createMulti('/', BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr
     * @return BasicExpr
     */
    public function neg($a)
    {
        return BasicExpr::createUnary('-', [$a]);
    }

    /**
     * @param $a Expr
     * @return BasicExpr
     */
    public function not($a)
    {
        return BasicExpr::createUnary('NOT', [$a]);
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @return BasicExpr
     */
    public function like($a, $b)
    {
        return BasicExpr::createBinary('LIKE', [$a, $b]);
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @param $c Expr
     * @return BasicExpr
     */
    public function between($a, $b, $c)
    {
        return BasicExpr::createBetween('BETWEEN', [$a, $b, $c]);
    }

    //
    // Functions
    //

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function avg($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_AVG, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function count($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_COUNT, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function max($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_MAX, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function min($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_MIN, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function sum($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_SUM, [$a]);
    }

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return FunctionExpr
     */
    public function coalesce($a, $b = null)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_COALESCE, BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function abs($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_ABS, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function round($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_ROUND, [$a]);
    }

    /**
     * @param $a Expr|Expr[]
     * @param $b Expr|null
     * @return FunctionExpr
     */
    public function concat($a, $b = null)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_CONCAT, BuildHelper::args(func_get_args()));
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function length($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LENGTH, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function lower($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LOWER, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function ltrim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LTRIM, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function rtrim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_RTRIM, [$a]);
    }

    /**
     * @param $a Expr
     * @param $b Expr
     * @param $c Expr|null
     * @return FunctionExpr
     */
    public function substr($a, $b, $c = null)
    {
        if ($c === null) {
            return new FunctionExpr($this->spec, FunctionExpr::TYPE_SUBSTR, [$a, $b]);
        } else {
            return new FunctionExpr($this->spec, FunctionExpr::TYPE_SUBSTR, [$a, $b, $c]);
        }
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function trim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_TRIM, [$a]);
    }

    /**
     * @param $a Expr
     * @return FunctionExpr
     */
    public function upper($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_UPPER, [$a]);
    }

    //
    // Misc
    //

    public function __call($name, $arguments)
    {
        if (method_exists($this, "{$name}_")) {
            return call_user_func_array([$this, "{$name}_"], $arguments);
        }

        throw new BuildException('Calling unknown method ' . get_class($this) . "::{$name}");
    }
}
