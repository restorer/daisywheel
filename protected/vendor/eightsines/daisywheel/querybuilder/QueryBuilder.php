<?php

namespace daisywheel\querybuilder;

use daisywheel\querybuilder\ast\AlterTableSelector;
use daisywheel\querybuilder\ast\commands\CreateTableCommand;
use daisywheel\querybuilder\ast\commands\DeleteCommand;
use daisywheel\querybuilder\ast\commands\DropTableCommand;
use daisywheel\querybuilder\ast\commands\InsertCommand;
use daisywheel\querybuilder\ast\commands\InsertSpecialCommand;
use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\commands\TruncateTableCommand;
use daisywheel\querybuilder\ast\commands\UpdateCommand;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\expr\AliasExpr;
use daisywheel\querybuilder\ast\expr\BasicExpr;
use daisywheel\querybuilder\ast\expr\ColumnExpr;
use daisywheel\querybuilder\ast\expr\FunctionExpr;
use daisywheel\querybuilder\ast\expr\PlaceholderExpr;
use daisywheel\querybuilder\ast\expr\ValueExpr;
use daisywheel\querybuilder\ast\parts\DataTypePart;
use daisywheel\querybuilder\ast\parts\IdentifierPart;
use daisywheel\querybuilder\ast\parts\TablePart;

/**
 * @method AliasExpr as(Expr $expr, string $alias)
 * @method BasicExpr and(Expr|Expr[] ...$a)
 * @method BasicExpr or(Expr|Expr[] ...$a)
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
     *
     * @return SelectCommand
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/select.html}
     * {@internal SQLite: https://www.sqlite.org/lang_select.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-select.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms189499.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_10002.htm#SQLRF01702}
     */
    public function select($columns = null)
    {
        /** @noinspection PhpParamsInspection */
        return new SelectCommand($this->spec, BuildHelper::args(func_get_args()));
    }

    /**
     * @param string|TablePart $table
     * @param string|string[] $columns
     *
     * @return InsertCommand
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/insert.html}
     * {@internal SQLite: https://www.sqlite.org/lang_insert.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-insert.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms174335.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_9014.htm#SQLRF01604}
     */
    public function insertInto($table, $columns)
    {
        /** @noinspection PhpParamsInspection */
        return new InsertCommand($this->spec, TablePart::create($this->spec, $table), BuildHelper::arg($columns));
    }

    /**
     * @param string|TablePart $table
     * @param string|string[] $keys
     * @param string|string[] $columns
     *
     * @return InsertSpecialCommand
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/insert.html}
     * {@internal SQLite: https://www.sqlite.org/lang_insert.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-insert.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/bb510625.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_9016.htm#SQLRF01606}
     */
    public function insertOrIgnore($table, $keys, $columns)
    {
        /** @noinspection PhpParamsInspection */
        return new InsertSpecialCommand(
            $this->spec,
            TablePart::create($this->spec, $table),
            BuildHelper::arg($keys),
            BuildHelper::arg($columns),
            InsertSpecialCommand::TYPE_IGNORE
        );
    }

    /**
     * @param string|TablePart $table
     * @param string|string[] $keys
     * @param string|string[] $columns
     *
     * @return InsertSpecialCommand
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/insert.html}
     * {@internal SQLite: https://www.sqlite.org/lang_insert.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-insert.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/bb510625.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_9016.htm#SQLRF01606}
     */
    public function insertOrReplace($table, $keys, $columns)
    {
        /** @noinspection PhpParamsInspection */
        return new InsertSpecialCommand(
            $this->spec,
            TablePart::create($this->spec, $table),
            BuildHelper::arg($keys),
            BuildHelper::arg($columns),
            InsertSpecialCommand::TYPE_REPLACE
        );
    }

    /**
     * @param string|TablePart $table
     *
     * @return UpdateCommand
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/update.html}
     * {@internal SQLite: https://www.sqlite.org/lang_update.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-update.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms177523.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_10008.htm#SQLRF01708}
     */
    public function update($table)
    {
        return new UpdateCommand($this->spec, TablePart::create($this->spec, $table));
    }

    /**
     * @param string|TablePart $table
     *
     * @return DeleteCommand
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/delete.html}
     * {@internal SQLite: https://www.sqlite.org/lang_delete.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-delete.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms189835.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_8005.htm#SQLRF01505}
     */
    public function deleteFrom($table)
    {
        return new DeleteCommand(TablePart::create($this->spec, $table));
    }

    /**
     * @param string|TablePart $table
     * @param DataTypePart[] $columns
     *
     * @return CreateTableCommand
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/create-table.html}
     * {@internal SQLite: https://www.sqlite.org/lang_createtable.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-createtable.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms174979.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_7002.htm#SQLRF01402}
     * {@internal We don't allow multi-column PKs becase SQLite allows AUTOINCREMENT only as part of PRIMARY KEY column}
     * {@internal We don't allow CHECK because MySQL ignore them}
     */
    public function createTable($table, array $columns = [])
    {
        return new CreateTableCommand($this->spec, TablePart::create($this->spec, $table), $columns);
    }

    /**
     * @param string|TablePart $table
     *
     * @return AlterTableSelector
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/alter-table.html}
     * {@internal SQLite: https://www.sqlite.org/lang_altertable.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-altertable.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-US/library/ms190273.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_3001.htm#SQLRF01001}
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/create-index.html}
     * {@internal SQLite: https://www.sqlite.org/lang_createindex.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-createindex.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms188783.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_5011.htm#SQLRF01209}
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/drop-index.html}
     * {@internal SQLite: https://www.sqlite.org/lang_dropindex.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-dropindex.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms176118.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_8016.htm#SQLRF01510}
     */
    public function alterTable($table)
    {
        return new AlterTableSelector($this->spec, TablePart::create($this->spec, $table));
    }

    /**
     * @param string|TablePart $table
     *
     * @return DropTableCommand
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/drop-table.html}
     * {@internal SQLite: https://www.sqlite.org/lang_droptable.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-droptable.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms173790.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_9003.htm#SQLRF01806}
     */
    public function dropTable($table)
    {
        return new DropTableCommand($this->spec, TablePart::create($this->spec, $table));
    }

    /**
     * @param string|TablePart $table
     *
     * @return TruncateTableCommand
     *
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/truncate-table.html}
     * {@internal SQLite: -}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-truncate.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms177570.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_10007.htm#SQLRF01707}
     */
    public function truncateTable($table)
    {
        return new TruncateTableCommand($this->spec, TablePart::create($this->spec, $table));
    }

    //
    // Basic
    //

    /**
     * @param mixed $value
     *
     * @return ValueExpr
     * @throws BuildException
     */
    public function val($value)
    {
        return new ValueExpr($this->spec, $value);
    }

    /**
     * @param string $name
     *
     * @return PlaceholderExpr
     * @throws BuildException
     */
    public function param($name)
    {
        return new PlaceholderExpr($name);
    }

    /**
     * @param string|TablePart $nameOrAlias
     * @param string|null $name
     *
     * @return ColumnExpr
     * @psalm-suppress InvalidArgument
     */
    public function col($nameOrAlias, $name = null)
    {
        if ($name === null) {
            return new ColumnExpr($this->spec, $nameOrAlias);
        } elseif ($nameOrAlias instanceof TablePart) {
            return new ColumnExpr($this->spec, $name, $nameOrAlias);
        } else {
            return new ColumnExpr($this->spec, $name, new IdentifierPart($this->spec, $nameOrAlias));
        }
    }

    /**
     * @param Expr $expr
     * @param string $alias
     *
     * @return AliasExpr
     */
    public function as_($expr, $alias)
    {
        return new AliasExpr($this->spec, $expr, $alias);
    }

    /**
     * @param string $name
     *
     * @return TablePart
     */
    public function tab($name)
    {
        return new TablePart($this->spec, $name, false);
    }

    /**
     * @param string $name
     *
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
     * @param Expr,...|Expr[] $a
     *
     * @return BasicExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function and_($a)
    {
        /** @noinspection PhpParamsInspection */
        return BasicExpr::createMulti('AND', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr,...|Expr[] $a
     *
     * @return BasicExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function or_($a)
    {
        /** @noinspection PhpParamsInspection */
        return BasicExpr::createMulti('OR', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     * @param Expr $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function eq($a, $b)
    {
        return BasicExpr::createEq('=', [$a, $b], 'IS NULL');
    }

    /**
     * @param Expr $a
     * @param Expr $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function neq($a, $b)
    {
        return BasicExpr::createEq('<>', [$a, $b], 'IS NOT NULL');
    }

    /**
     * @param Expr $a
     * @param Expr $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function gt($a, $b)
    {
        return BasicExpr::createBinary('>', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function gte($a, $b)
    {
        return BasicExpr::createBinary('>=', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function lt($a, $b)
    {
        return BasicExpr::createBinary('<', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function lte($a, $b)
    {
        return BasicExpr::createBinary('<=', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr[]|PlaceholderExpr|SelectCommand $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function in($a, $b)
    {
        return BasicExpr::createList('IN', [$a, $b], '1 = 2');
    }

    /**
     * @param Expr $a
     * @param Expr[]|PlaceholderExpr|SelectCommand $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function notIn($a, $b)
    {
        return BasicExpr::createList('NOT IN', [$a, $b], '1 = 1');
    }

    /**
     * @param Expr $a
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function isNull($a)
    {
        return BasicExpr::createRightHand('IS NULL', [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function isNotNull($a)
    {
        return BasicExpr::createRightHand('IS NOT NULL', [$a]);
    }

    /**
     * @param Expr,...|Expr[] $a
     *
     * @return BasicExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function add($a)
    {
        /** @noinspection PhpParamsInspection */
        return BasicExpr::createMulti('+', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr,...|Expr[] $a
     *
     * @return BasicExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function sub($a)
    {
        /** @noinspection PhpParamsInspection */
        return BasicExpr::createMulti('-', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr,...|Expr[] $a
     *
     * @return BasicExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function mul($a)
    {
        /** @noinspection PhpParamsInspection */
        return BasicExpr::createMulti('*', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr,...|Expr[] $a
     *
     * @return BasicExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function div($a)
    {
        /** @noinspection PhpParamsInspection */
        return BasicExpr::createMulti('/', BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function neg($a)
    {
        return BasicExpr::createUnary('-', [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function not($a)
    {
        return BasicExpr::createUnary('NOT', [$a]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     *
     * @return BasicExpr
     * @throws BuildException
     */
    public function like($a, $b)
    {
        return BasicExpr::createBinary('LIKE', [$a, $b]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @param Expr $c
     *
     * @return BasicExpr
     * @throws BuildException
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
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function avg($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_AVG, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function count($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_COUNT, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function max($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_MAX, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function min($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_MIN, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function sum($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_SUM, [$a]);
    }

    /**
     * @param Expr,...|Expr[] $a
     *
     * @return FunctionExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function coalesce($a)
    {
        /** @noinspection PhpParamsInspection */
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_COALESCE, BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function abs($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_ABS, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function round($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_ROUND, [$a]);
    }

    /**
     * @param Expr,...|Expr[] $a
     *
     * @return FunctionExpr
     * @throws BuildException
     * @psalm-suppress TypeCoercion
     */
    public function concat($a)
    {
        /** @noinspection PhpParamsInspection */
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_CONCAT, BuildHelper::args(func_get_args()));
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function length($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LENGTH, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function lower($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LOWER, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function ltrim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_LTRIM, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function rtrim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_RTRIM, [$a]);
    }

    /**
     * @param Expr $a
     * @param Expr $b
     * @param Expr|null $c
     *
     * @return FunctionExpr
     * @throws BuildException
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
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function trim($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_TRIM, [$a]);
    }

    /**
     * @param Expr $a
     *
     * @return FunctionExpr
     * @throws BuildException
     */
    public function upper($a)
    {
        return new FunctionExpr($this->spec, FunctionExpr::TYPE_UPPER, [$a]);
    }

    //
    // Misc
    //

    /**
     * @param string $name
     * @param mixed $arguments
     *
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
