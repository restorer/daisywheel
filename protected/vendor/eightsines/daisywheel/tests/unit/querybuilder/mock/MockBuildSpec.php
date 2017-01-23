<?php

namespace daisywheel\tests\unit\querybuilder\mock;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\commands\DropIndexCommand;
use daisywheel\querybuilder\ast\commands\CreateTableCommand;
use daisywheel\querybuilder\ast\commands\DropTableCommand;
use daisywheel\querybuilder\ast\commands\InsertSpecialCommand;
use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\commands\TruncateTableCommand;
use daisywheel\querybuilder\ast\expr\FunctionExpr;
use daisywheel\querybuilder\ast\parts\ForeignKeyConstraintPart;
use daisywheel\querybuilder\ast\parts\DataTypePart;

class MockBuildSpec implements BuildSpec
{
    /** @var array<string, string> */
    protected static $DATA_TYPE_MAP = [
        DataTypePart::TYPE_PRIMARY_KEY => 'INT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
        DataTypePart::TYPE_BIG_PRIMARY_KEY => 'BIGINT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
        DataTypePart::TYPE_TINY_INT => 'TINYINT',
        DataTypePart::TYPE_SMALL_INT => 'SMALLINT',
        DataTypePart::TYPE_INT => 'INT',
        DataTypePart::TYPE_BIG_INT => 'BIGINT',
        DataTypePart::TYPE_DECIMAL => 'DECIMAL',
        DataTypePart::TYPE_FLOAT => 'FLOAT(24)',
        DataTypePart::TYPE_DOUBLE => 'FLOAT(53)',
        DataTypePart::TYPE_DATE => 'DATE',
        DataTypePart::TYPE_TIME => 'TIME',
        DataTypePart::TYPE_DATE_TIME => 'DATETIME',
        DataTypePart::TYPE_CHAR => 'NCHAR',
        DataTypePart::TYPE_VAR_CHAR => 'NVARCHAR',
        DataTypePart::TYPE_TEXT => 'NVARCHAR(MAX)',
        DataTypePart::TYPE_MEDIUM_TEXT => 'MEDIUMTEXT',
        DataTypePart::TYPE_LONG_TEXT => 'LONGTEXT',
        DataTypePart::TYPE_BLOB => 'VARBINARY(MAX)',
        DataTypePart::TYPE_MEDIUM_BLOB => 'MEDIUMBLOB',
        DataTypePart::TYPE_LONG_BLOB => 'LONGBLOB',
    ];

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
     * @see BuildSpec::buildSelectSql()
     */
    public function buildSelectSql($startSql, $partsSql, $orderSql, $limit, $offset)
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

    /**
     * @see BuildSpec::buildInsertIgnoreCommand()
     */
    public function buildInsertIgnoreCommand($quotedTable, $quotedKeys, $quotedColumns, $quotedValues)
    {
        $keysSql = InsertSpecialCommand::buildKeysSql($quotedKeys, $quotedColumns);

        return [
            "WITH qb_1 {$keysSql} AS ("
            . InsertSpecialCommand::buildValuesSql($quotedValues)
            . ") INSERT INTO {$quotedTable} {$keysSql} SELECT "
            . join(', ', array_merge($quotedKeys, $quotedColumns))
            . " FROM qb_1 WHERE NOT EXISTS (SELECT 1 FROM {$quotedTable} WHERE "
            . join(' AND ', array_map(/** @return string */function ($v) {
                return "{$v} = qb_1.{$v}";
            }, $quotedKeys))
            . ')'
        ];
    }

    /**
     * @see BuildSpec::buildInsertReplaceCommand()
     */
    public function buildInsertReplaceCommand($quotedTable, $quotedKeys, $quotedColumns, $quotedValues)
    {
        $keysSql = InsertSpecialCommand::buildKeysSql($quotedKeys, $quotedColumns);

        return [
            "WITH qbv_1 {$keysSql} AS ("
            . InsertSpecialCommand::buildValuesSql($quotedValues)
            . "), qbu_2 AS (UPDATE {$quotedTable} SET "
            . join(', ', array_map(/** @return string */ function ($v) {
                return "{$v} = qbv_1.{$v}";
            }, $quotedColumns))
            . " FROM qbv_1 WHERE "
            . join(' AND ', array_map(/** @return string */ function ($v) use ($quotedTable) {
                return "{$quotedTable}.{$v} = qbv_1.{$v}";
            }, $quotedKeys))
            . " RETURNING {$quotedTable}.*) INSERT INTO {$quotedTable} {$keysSql} SELECT "
            . join(', ', array_merge($quotedKeys, $quotedColumns))
            . " FROM qbv_1 WHERE NOT EXISTS (SELECT 1 FROM qbu_2 WHERE "
            . join(' AND ', array_map(/** @return string */function ($v) {
                return "{$v} = qbv_1.{$v}";
            }, $quotedKeys))
            . ')'
        ];
    }

    /**
     * @see BuildSpec::buildCreateTableCommand()
     */
    public function buildCreateTableCommand($quotedTable, $temporary, $partsSql, $indexList)
    {
        return CreateTableCommand::basicBuild(
            ($temporary ? 'TEMPORARY ' : ''),
            $quotedTable,
            $partsSql,
            " ENGINE=InnoDB, CHARACTER SET = 'utf8', COLLATE = 'utf8_general_ci'",
            $indexList
        );
    }

    /**
     * @see BuildSpec::buildCreateTableAsSelectCommand()
     */
    public function buildCreateTableAsSelectCommand($quotedTable, $temporary, $select)
    {
        return CreateTableCommand::basicBuildCreateAsSelect(($temporary ? 'TEMPORARY ' : ''), $quotedTable, $select);
    }

    /**
     * @see BuildSpec::buildDataTypePart()
     */
    public function buildDataTypePart($quotedName, $type, $options, $isNotNull, $quotedDefaultValue)
    {
        return DataTypePart::basicBuild($quotedName, self::$DATA_TYPE_MAP[$type], $options, $isNotNull, $quotedDefaultValue);
    }

    /**
     * @see BuildSpec::buildCreateForeignKeyPart()
     */
    public function buildCreateForeignKeyPart($startSql, $onDeleteOption, $onUpdateOption)
    {
        return ForeignKeyConstraintPart::basicBuild($startSql, $onDeleteOption, $onUpdateOption);
    }

    /**
     * @see BuildSpec::buildDropTableCommand()
     */
    public function buildDropTableCommand($quotedTable, $temporary)
    {
        return DropTableCommand::basicBuild(($temporary ? 'TEMPORARY ' : ''), $quotedTable);
    }

    /**
     * @see BuildSpec::buildTruncateTableCommand()
     */
    public function buildTruncateTableCommand($quotedTable, $tableName)
    {
        return TruncateTableCommand::basicBuild("${quotedTable} RESTART IDENTITY");
    }

    /**
     * @see BuildSpec::buildDropIndexCommand()
     */
    public function buildDropIndexCommand($table, $constraintSql)
    {
        return DropIndexCommand::basicBuild("$constraintSql ON {$table->buildPart()}");
    }
}
