<?php

namespace daisywheel\querybuilder;

use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\parts\OrderByPart;
use daisywheel\querybuilder\ast\parts\TablePart;

interface BuildSpec
{
    /**
     * @param mixed $value
     * @return string
     */
    public function quote($value);

    /**
     * @param string $name
     * @return string
     */
    public function quoteIdentifier($name);

    /**
     * @param string $name
     * @param boolean $temporary
     * @return string
     */
    public function quoteTable($name, $temporary);

    /**
     * @param string $tableName
     * @param string $constraintName
     * @return string
     */
    public function quoteConstraint($tableName, $constraintName);

    /**
     * @param string $type
     * @param Expr[] $operands
     * @return string
     */
    public function buildFunctionExpr($type, $operands);

    /**
     * @param string $startSql
     * @param string $partsSql
     * @param OrderByPart[] $orderByList
     * @param int|null $limit
     * @param int|null $offset
     * @return string
     */
    public function buildSelectSql($startSql, $partsSql, $orderByList, $limit, $offset);

    /**
     * @param string $quotedTable
     * @param string[] $quotedKeys
     * @param string[] $quotedColumns
     * @param array<array<string>> $quotedValues
     * @return string
     */
    public function buildInsertIgnoreCommand($quotedTable, $quotedKeys, $quotedColumns, $quotedValues);

    /**
     * @param string $quotedTable
     * @param string[] $quotedKeys
     * @param string[] $quotedColumns
     * @param array<array<string>> $quotedValues
     * @return string
     */
    public function buildInsertReplaceCommand($quotedTable, $quotedKeys, $quotedColumns, $quotedValues);

    /**
     * @param string $quotedTable
     * @param boolean $temporary
     * @param string $partsSql
     * @param CreateIndexCommand[] $indexList
     * @return string[]
     */
    public function buildCreateTableCommand($quotedTable, $temporary, $partsSql, $indexList);

    /**
     * @param string $quotedTable
     * @param boolean $temporary
     * @param SelectCommand $select
     * @return string[]
     */
    public function buildCreateTableAsSelectCommand($quotedTable, $temporary, $select);

    /**
     * @param string $quotedName
     * @param string $type
     * @param int[] $options
     * @param boolean $isNotNull
     * @param string|null $quotedDefaultValue
     * @return string
     */
    public function buildDataTypePart($quotedName, $type, $options, $isNotNull, $quotedDefaultValue);

    /**
     * @param string $startSql
     * @param string $onDeleteOption
     * @param string $onUpdateOption
     * @return string
     */
    public function buildCreateForeignKeyPart($startSql, $onDeleteOption, $onUpdateOption);

    /**
     * @param string $quotedTable
     * @param boolean $temporary
     * @return string[]
     */
    public function buildDropTableCommand($quotedTable, $temporary);

    /**
     * @param string $quotedTable
     * @param string $tableName
     * @return string[]
     */
    public function buildTruncateTableCommand($quotedTable, $tableName);

    /**
     * @param TablePart $table
     * @param string $constraintSql
     * @return string[]
     */
    public function buildDropIndexCommand($table, $constraintSql);
}
