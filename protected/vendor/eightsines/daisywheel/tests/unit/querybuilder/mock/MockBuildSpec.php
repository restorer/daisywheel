<?php

namespace daisywheel\tests\unit\querybuilder\mock;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\commands\DropIndexCommand;
use daisywheel\querybuilder\ast\commands\DropTableCommand;
use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\commands\TruncateTableCommand;
use daisywheel\querybuilder\ast\expr\FunctionExpr;

class MockBuildSpec implements BuildSpec
{
    /**
     * @see BuildSpec::quote()
     */
    public function quote($value)
    {
        return "'" . str_replace("'", "\\'", print_r($value, true)) . "'";
    }

    /**
     * @see BuildSpec::quoteIdentifier()
     */
    public function quoteIdentifier($name)
    {
        return '[' . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name) . ']';
    }

    /**
     * @see BuildSpec::quoteTable()
     */
    public function quoteTable($name, $temporary)
    {
        return '[' . ($temporary ? '#' : '') . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name) . ']';
    }

    /**
     * @see BuildSpec::quoteConstraint()
     */
    public function quoteConstraint($tableName, $constraintName)
    {
        return $this->quoteIdentifier($tableName . '_' . $constraintName);
    }

    /**
     * @see BuildSpec::buildFunctionExpr()
     */
    public function buildFunctionExpr($type, $operands)
    {
        switch ($type) {
            case FunctionExpr::TYPE_CONCAT:
                return '(' . join(' || ', array_map(function ($v) {
                    return $v->buildExpr();
                }, $operands)) . ')';

            case FunctionExpr::TYPE_LENGTH:
                return FunctionExpr::basicBuildExpr('LEN', $operands);

            case FunctionExpr::TYPE_SUBSTR:
                return FunctionExpr::basicBuildExpr('SUBSTRING', $operands);

            case FunctionExpr::TYPE_TRIM:
                return 'LTRIM(' . FunctionExpr::basicBuildExpr('RTRIM', $operands) . ')';

            default:
                return FunctionExpr::basicBuildExpr($type, $operands);
        }
    }

    /**
     * @see BuildSpec::buildSelectCommand()
     */
    public function buildSelectCommand($startSql, $partsSql, $orderSql, $limit, $offset)
    {
        if ($offset === null) {
            return $startSql
                . ($limit !== null ? "TOP {$this->quote($limit)} " : '')
                . $partsSql
                . SelectCommand::buildOrderBy($orderSql)
            ;
        }

        return "SELECT * FROM (SELECT TOP {$this->quote($limit)} * FROM ({$startSql}TOP {$this->quote($offset + $limit)} {$partsSql}"
            . SelectCommand::buildOrderBy($orderSql)
            . ')'
            . SelectCommand::buildOrderBy($orderSql, true)
            . ')'
            . SelectCommand::buildOrderBy($orderSql);
    }

    // buildInsertCommand
    // buildDeleteCommand
    // buildUpdateCommand

    /*
    public function buildCreateTableCommand($name, $temporary)
    {
        // TODO: utf8 character set and collation
        return CreateTableCommand::basicBuild(
            $this,
            ($temporary ? 'TEMPORARY ' : '') . "TABLE {$this->quoteIdentifier($name)}",
            ' ENGINE=InnoDB'
        );
    }
    */

    /**
     * @see BuildSpec::buildDropTableCommand()
     */
    public function buildDropTableCommand($tableSql, $temporary)
    {
        return DropTableCommand::basicBuild($tableSql, ($temporary ? 'TEMPORARY ' : ''));
    }

    /**
     * @see BuildSpec::buildTruncateTableCommand()
     */
    public function buildTruncateTableCommand($tableSql, $tableName)
    {
        return TruncateTableCommand::basicBuild("${tableSql} RESTART IDENTITY");
    }

    /**
     * @see BuildSpec::buildDropIndexCommand()
     */
    public function buildDropIndexCommand($table, $constraintSql)
    {
        return DropIndexCommand::basicBuild("$constraintSql ON {$table->buildPart()}");
    }
}
