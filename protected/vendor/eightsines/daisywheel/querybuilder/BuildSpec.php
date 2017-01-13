<?php

namespace daisywheel\querybuilder;

use daisywheel\querybuilder\ast\Expr;
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
    public function buildSelectCommand($startSql, $partsSql, $orderByList, $limit, $offset);

    // buildInsertCommand
    // buildDeleteCommand
    // buildUpdateCommand
    // buildCreateTableCommand

    /**
     * @param string $tableSql
     * @param boolean $temporary
     * @return string
     */
    public function buildDropTableCommand($tableSql, $temporary);

    /**
     * @param string $tableSql
     * @param string $tableName
     * @return string
     */
    public function buildTruncateTableCommand($tableSql, $tableName);

    /**
     * @param TablePart $table
     * @param string $constraintSql
     * @return string
     */
    public function buildDropIndexCommand($table, $constraintSql);
}
