<?php

namespace daisywheel\querybuilder;

use daisywheel\querybuilder\ast\commands\CreateIndexCommand;
use daisywheel\querybuilder\ast\commands\DeleteCommand;
use daisywheel\querybuilder\ast\commands\DropIndexCommand;
use daisywheel\querybuilder\ast\commands\DropTableCommand;
use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\commands\TruncateTableCommand;
use daisywheel\querybuilder\ast\expr\AliasExpr;
use daisywheel\querybuilder\ast\expr\BasicExpr;
use daisywheel\querybuilder\ast\expr\ColumnExpr;
use daisywheel\querybuilder\ast\expr\FunctionExpr;
use daisywheel\querybuilder\ast\expr\PlaceholderExpr;
use daisywheel\querybuilder\ast\expr\ValueExpr;
use daisywheel\querybuilder\ast\parts\TablePart;

/**
 * @method AliasExpr as(Expr $expr, string $alias)
 * @method BasicExpr and(Expr|Expr[] $a, Expr|null $b)
 * @method BasicExpr or(Expr|Expr[] $a, Expr|null $b)
 */
class QueryBuilder
{
    /** @var BuildSpec */
    protected $spec;

    /**
     * @param BuildSpec $spec
     */
    public function __construct($spec)
    {
        $this->spec = $spec;
    }

    /**
     * @param Expr[] $columns
     * @return SelectCommand
     */
    public function select($columns = null)
    {
        return new SelectCommand($this->spec, BuildHelper::args(func_get_args()));
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
     * @param string|TablePart $table
     * @return DeleteCommand
     */
    public function deleteFrom($table)
    {
        return new DeleteCommand(TablePart::create($this->spec, $table));
    }

    /**
     * @return CreateTableCommand
     */
    public function createTable()
    {
        // return new CreateTableCommand();
    }

    /**
     * @param string|TablePart $table
     * @return DropTableCommand
     */
    public function dropTable($table)
    {
        return new DropTableCommand($this->spec, TablePart::create($this->spec, $table));
    }

    /**
     * @param string|TablePart $table
     * @return TruncateTableCommand
     */
    public function truncateTable($table)
    {
        return new TruncateTableCommand($this->spec, TablePart::create($this->spec, $table));
    }

    /**
     * @param string|TablePart $table
     * @param string $name
     * @param string|string[] $columns
     * @return CreateIndexCommand
     */
    public function createIndex($table, $name, $columns)
    {
        return new CreateIndexCommand($this->spec, TablePart::create($this->spec, $table), $name, $columns);
    }

    /**
     * @param string|TablePart $table
     * @param string $name
     * @return DropIndexCommand
     */
    public function dropIndex($table, $name)
    {
        return new DropIndexCommand($this->spec, TablePart::create($this->spec, $table), $name);
    }

    //
    // Basic
    //

    /**
     * @param mixed $value
     * @return ValueExpr
     */
    public function val($value)
    {
        return new ValueExpr($this->spec, $value);
    }

    /**
     * @param string $name
     * @return PlaceholderExpr
     */
    public function param($name)
    {
        return new PlaceholderExpr($name);
    }

    /**
     * @param string|TablePart $nameOrTable
     * @param string|null $name
     * @return ColumnExpr
     */
    public function col($nameOrTable, $name = null)
    {
        if ($name === null) {
            return new ColumnExpr($this->spec, $nameOrTable);
        } else {
            return new ColumnExpr($this->spec, $name, TablePart::create($this->spec, $nameOrTable));
        }
    }

    /**
     * @param Expr $expr
     * @param string $alias
     * @return AliasExpr
     */
    public function as_($expr, $alias)
    {
        return new AliasExpr($this->spec, $expr, $alias);
    }

    /**
     * @param string $name
     * @return TablePart
     */
    public function temp($name)
    {
        return new TablePart($this->spec, $name, true);
    }

    //
    // Expressions
    //

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return BasicExpr
     */
    public function and_($a, $b = null)
    {
        return BasicExpr::createMulti('AND', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return BasicExpr
     */
    public function or_($a, $b = null)
    {
        return BasicExpr::createMulti('OR', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @return BasicExpr
     */
    public function eq($a, $b)
    {
        return BasicExpr::createEq('=', [$a, $b], 'IS NULL');
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @return BasicExpr
     */
    public function neq($a, $b)
    {
        return BasicExpr::createEq('<>', [$a, $b], 'IS NOT NULL');
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @return BasicExpr
     */
    public function gt($a, $b)
    {
        return BasicExpr::createBinary('>', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @return BasicExpr
     */
    public function gte($a, $b)
    {
        return BasicExpr::createBinary('>=', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @return BasicExpr
     */
    public function lt($a, $b)
    {
        return BasicExpr::createBinary('<', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @return BasicExpr
     */
    public function lte($a, $b)
    {
        return BasicExpr::createBinary('<=', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr[]|PlaceholderExpr|SelectCommand $b
     * @return BasicExpr
     */
    public function in($a, $b)
    {
        return BasicExpr::createList('IN', [$a, $b], '1 = 2');
    }

    /**
     * @param Expr $a
     * @param Expr[]|PlaceholderExpr|SelectCommand $b
     * @return BasicExpr
     */
    public function notIn($a, $b)
    {
        return BasicExpr::createList('NOT IN', [$a, $b], '1 = 1');
    }

    /**
     * @param Expr $a
     * @return BasicExpr
     */
    public function isNull($a)
    {
        return BasicExpr::createRightHand('IS NULL', [$a]);
    }

    /**
     * @param Expr $a
     * @return BasicExpr
     */
    public function isNotNull($a)
    {
        return BasicExpr::createRightHand('IS NOT NULL', [$a]);
    }

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return BasicExpr
     */
    public function add($a, $b = null)
    {
        return BasicExpr::createMulti('+', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return BasicExpr
     */
    public function sub($a, $b = null)
    {
        return BasicExpr::createMulti('-', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return BasicExpr
     */
    public function mul($a, $b = null)
    {
        return BasicExpr::createMulti('*', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return BasicExpr
     */
    public function div($a, $b = null)
    {
        return BasicExpr::createMulti('/', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     * @return BasicExpr
     */
    public function neg($a)
    {
        return BasicExpr::createUnary('-', [$a]);
    }

    /**
     * @param Expr $a
     * @return BasicExpr
     */
    public function not($a)
    {
        return BasicExpr::createUnary('NOT', [$a]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @return BasicExpr
     */
    public function like($a, $b)
    {
        return BasicExpr::createBinary('LIKE', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @param Expr $c
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
     * @param Expr $a
     * @return FunctionExpr
     */
    public function avg($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_AVG, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function count($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_COUNT, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function max($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_MAX, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function min($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_MIN, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function sum($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_SUM, [$a]);
    }

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return FunctionExpr
     */
    public function coalesce($a, $b = null)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_COALESCE, BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function abs($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_ABS, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function round($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_ROUND, [$a]);
    }

    /**
     * @param Expr|Expr[] $a
     * @param Expr|null $b
     * @return FunctionExpr
     */
    public function concat($a, $b = null)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_CONCAT, BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function length($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LENGTH, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function lower($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LOWER, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function ltrim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LTRIM, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function rtrim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_RTRIM, [$a]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @param Expr|null $c
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
     * @param Expr $a
     * @return FunctionExpr
     */
    public function trim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_TRIM, [$a]);
    }

    /**
     * @param Expr $a
     * @return FunctionExpr
     */
    public function upper($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_UPPER, [$a]);
    }

    //
    // Misc
    //

    /**
     * @internal
     * @param string $name
     * @param mixed $arguments
     * @throws BuildException
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, "{$name}_")) {
            return call_user_func_array([$this, "{$name}_"], $arguments);
        }

        throw new BuildException('Calling unknown method ' . get_class($this) . "::{$name}");
    }
}
