<?php

namespace daisywheel\querybuilder;

interface BuildSpec
{
    /**
     * @param $value any
     * @return string
     */
    public function quote($value);

    /**
     * @param $name string
     * @return string
     */
    public function quoteIdentifier($name);

    /**
     * @param $name string
     * @param $temporary boolean
     * @return string
     */
    public function quoteTable($name, $temporary);

    /**
     * @param $tableName string
     * @param $constraintName string
     * @return string
     */
    public function quoteConstraint($tableName, $constraintName);

    /**
     * @param $type string
     * @param $operands daisywheel\querybuilder\ast\Expr[]
     * @return string
     */
    public function buildFunctionExpr($type, $operands);

    // buildSelectCommand
    // buildInsertCommand
    // buildDeleteCommand
    // buildUpdateCommand
    // buildCreateTableCommand

    /**
     * @param $name string
     * @param $temporary boolean
     * @return string
     */
    public function buildDropTableCommand($name, $temporary);

    /**
     * @param $name string
     * @param $temporary boolean
     * @return string
     */
    public function buildTruncateTableCommand($name, $temporary);

    // buildCreateIndexCommand

    /**
     * @param $constraintSql string
     * @param $tableName string
     * @param $tableTemporary boolean
     * @return string
     */
    public function buildDropIndexCommand($constraintSql, $tableName, $tableTemporary);
}
