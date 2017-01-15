<?php

namespace daisywheel\querybuilder;

use daisywheel\querybuilder\ast\commands\CreateIndexCommand;
use daisywheel\querybuilder\ast\commands\DeleteCommand;
use daisywheel\querybuilder\ast\commands\DropIndexCommand;
use daisywheel\querybuilder\ast\commands\DropTableCommand;
use daisywheel\querybuilder\ast\commands\InsertCommand;
use daisywheel\querybuilder\ast\commands\InsertSpecialCommand;
use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\commands\TruncateTableCommand;
use daisywheel\querybuilder\ast\commands\UpdateCommand;
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
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/select.html}
     * {@internal SQLite: https://www.sqlite.org/lang_select.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-select.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms189499.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_10002.htm#SQLRF01702}
     */
    public function select($columns = null)
    {
        return new SelectCommand($this->spec, BuildHelper::args(func_get_args()));
    }

    /**
     * @param string|TablePart $table
     * @param string|string[] $columns
     * @return InsertCommand
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/insert.html}
     * {@internal SQLite: https://www.sqlite.org/lang_insert.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-insert.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms174335.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_9014.htm#SQLRF01604}
     */
    public function insertInto($table, $columns)
    {
        return new InsertCommand($this->spec, TablePart::create($this->spec, $table), BuildHelper::arg($columns));
    }

    /**
     * @param string|TablePart $table
     * @param string|string[] $keys
     * @param string|string[] $columns
     * @return InsertCommand
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/insert.html}
     * {@internal SQLite: https://www.sqlite.org/lang_insert.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-insert.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/bb510625.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_9016.htm#SQLRF01606}
     */
    public function insertOrIgnore($table, $keys, $columns)
    {
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
     * @return InsertCommand
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/insert.html}
     * {@internal SQLite: https://www.sqlite.org/lang_insert.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-insert.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/bb510625.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_9016.htm#SQLRF01606}
     */
    public function insertOrReplace($table, $keys, $columns)
    {
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
     * @return UpdateCommand
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
     * @return DeleteCommand
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
     * @param ColumnDef[] $columns
     * @return CreateTableCommand
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/create-table.html}
     * {@internal SQLite: https://www.sqlite.org/lang_createtable.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-createtable.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms174979.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_7002.htm#SQLRF01402}
     */
    public function createTable($table, $columns)
    {
        // return new CreateTableCommand();
        // TODO: asSelect
    }

    /**
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/alter-table.html}
     * {@internal SQLite: https://www.sqlite.org/lang_altertable.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-altertable.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-US/library/ms190273.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_3001.htm#SQLRF01001}
     */
    public function alterTable()
    {
        /*
        alterTable('t1')
            ->renameTo('t2') // mySQL, sqlite
            ->add($builder->col('c1')->varChar(255)->notNull()) // mySQL, sqlite
            ->addIndex('i1', ['c1', 'c2']) // ??? just use createIndex
            ->addUniqieIndex('i1', ['c1', 'c2']) // ??? just use createUniqueIndex
            ->addForeignKey('fk1', ['c1', 'c2'], 't2', ['c1', 'c2'])->onDeleteSetNull()->onUpdateCascade()
            ->alter(column)
            ->drop(column)
            ->dropContraint(constraint) // ??? just use dropIndex
            ->renameColumn(column)
            ->renameConstraint(constraint)
        */
    }

    /**
     * @param string|TablePart $table
     * @return DropTableCommand
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
     * @return TruncateTableCommand
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

    /**
     * @param string|TablePart $table
     * @param string $name
     * @param string|string[] $columns
     * @return CreateIndexCommand
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/create-index.html}
     * {@internal SQLite: https://www.sqlite.org/lang_createindex.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-createindex.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms188783.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_5011.htm#SQLRF01209}
     */
    public function createIndex($table, $name, $columns)
    {
        return new CreateIndexCommand($this->spec, TablePart::create($this->spec, $table), $name, BuildHelper::arg($columns), false);
    }

    /**
     * @param string|TablePart $table
     * @param string $name
     * @param string|string[] $columns
     * @return CreateIndexCommand
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/create-index.html}
     * {@internal SQLite: https://www.sqlite.org/lang_createindex.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-createindex.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms188783.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_5011.htm#SQLRF01209}
     */
    public function createUniqueIndex($table, $name, $columns)
    {
        return new CreateIndexCommand($this->spec, TablePart::create($this->spec, $table), $name, BuildHelper::arg($columns), true);
    }

    /**
     * @param string|TablePart $table
     * @param string $name
     * @return DropIndexCommand
     * {@internal MySQL: https://dev.mysql.com/doc/refman/5.7/en/drop-index.html}
     * {@internal SQLite: https://www.sqlite.org/lang_dropindex.html}
     * {@internal PostgreSQL: https://www.postgresql.org/docs/current/static/sql-dropindex.html}
     * {@internal SQL Server: https://msdn.microsoft.com/en-us/library/ms176118.aspx}
     * {@internal Oracle: https://docs.oracle.com/cd/B28359_01/server.111/b28286/statements_8016.htm#SQLRF01510}
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
