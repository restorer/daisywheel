<?php

namespace daisywheel\tests\unit\querybuilder\mock;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\commands\DropIndexCommand;
use daisywheel\querybuilder\ast\commands\DropTableCommand;
use daisywheel\querybuilder\ast\commands\TruncateTableCommand;
use daisywheel\querybuilder\ast\expr\FunctionExpr;

class MockBuildSpec implements BuildSpec
{
    /**
     * @implements BuildSpec
     */
    public function quote($value)
    {
        return "'" . str_replace("'", "\\'", print_r($value, true)) . "'";
    }

    /**
     * @implements BuildSpec
     */
    public function quoteIdentifier($name)
    {
        return '[' . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name) . ']';
    }

    /**
     * @implements BuildSpec
     */
    public function quoteTable($name, $temporary)
    {
        return '[' . ($temporary ? '#' : '') . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name) . ']';
    }

    /**
     * @implements BuildSpec
     */
    public function quoteConstraint($tableName, $constraintName)
    {
        return '['
            . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $tableName)
            . '_'
            . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $constraintName)
            . ']';
    }

    /**
     * @implements BuildSpec
     */
    public function buildFunctionExpr($type, $operands)
    {
        switch ($type) {
            case FunctionExpr::TYPE_CONCAT:
                return '(' . join(' || ', array_map(function ($v) {
                    return $v->build();
                }, $operands)) . ')';

            case FunctionExpr::TYPE_LENGTH:
                return FunctionExpr::basicBuild('LEN', $operands);

            case FunctionExpr::TYPE_SUBSTR:
                return FunctionExpr::basicBuild('SUBSTRING', $operands);

            case FunctionExpr::TYPE_TRIM:
                return 'LTRIM(' . FunctionExpr::basicBuild('RTRIM', $operands) . ')';

            default:
                return FunctionExpr::basicBuild($type, $operands);
        }
    }

    // buildSelectCommand
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
     * @implements BuildSpec
     */
    public function buildDropTableCommand($name, $temporary)
    {
        return DropTableCommand::basicBuild(($temporary ? 'TEMPORARY ' : '') . "TABLE {$this->quoteIdentifier($name)}");
    }

    /**
     * @implements BuildSpec
     */
    public function buildTruncateTableCommand($name, $temporary)
    {
        return TruncateTableCommand::basicBuild($this->quoteTable($name, $temporary), ' RESTART IDENTITY');
    }

    // buildCreateIndexCommand

    /**
     * @implements BuildSpec
     */
    public function buildDropIndexCommand($constraintSql, $tableName, $tableTemporary)
    {
        return DropIndexCommand::basicBuild($constraintSql, " ON {$this->quoteTable($tableName, $tableTemporary)}");
    }
}
