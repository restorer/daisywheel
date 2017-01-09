<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ColumnPart;
use daisywheel\db\builder\ForeignReference;
use daisywheel\db\builder\FunctionPart;

class PgSqlDriver extends BaseDriver
{
    public function getColumnTypeMap()
    {
        return [
            ColumnPart::TYPE_PRIMARYKEY => [
                'type' => 'SERIAL NOT NULL PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ],
            ColumnPart::TYPE_BIGPRIMARYKEY => [
                'type' => 'BIGSERIAL NOT NULL PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ],
            ColumnPart::TYPE_TYNYINT => [
                'type' => 'SMALLINT',
            ],
            ColumnPart::TYPE_SMALLINT => [
                'type' => 'SMALLINT',
            ],
            ColumnPart::TYPE_INT => [
                'type' => 'INTEGER',
            ],
            ColumnPart::TYPE_BIGINT => [
                'type' => 'BIGINT',
            ],
            ColumnPart::TYPE_DECIMAL => [
                'type' => 'DECIMAL',
                'supportOptions' => [0, 2],
            ],
            ColumnPart::TYPE_FLOAT => [
                'type' => 'REAL',
            ],
            ColumnPart::TYPE_DOUBLE => [
                'type' => 'DOUBLE PRECISION',
            ],
            ColumnPart::TYPE_DATE => [
                'type' => 'DATE',
            ],
            ColumnPart::TYPE_TIME => [
                'type' => 'TIME',
            ],
            ColumnPart::TYPE_DATETIME => [
                'type' => 'TIMESTAMP',
            ],
            ColumnPart::TYPE_CHAR => [
                'type' => 'CHARACTER',
                'supportOptions' => [1, 1],
            ],
            ColumnPart::TYPE_VARCHAR => [
                'type' => 'CHARACTER VARYING',
                'supportOptions' => [1, 1],
            ],
            ColumnPart::TYPE_TEXT => [
                'type' => 'TEXT',
            ],
            ColumnPart::TYPE_MEDIUMTEXT => [
                'type' => 'TEXT',
            ],
            ColumnPart::TYPE_LONGTEXT => [
                'type' => 'TEXT',
            ],
            ColumnPart::TYPE_BLOB => [
                'type' => 'BYTEA',
            ],
            ColumnPart::TYPE_MEDIUMBLOB => [
                'type' => 'BYTEA',
            ],
            ColumnPart::TYPE_LONGBLOB => [
                'type' => 'BYTEA',
            ],
        ];
    }

    public function getReferenceOptionMap()
    {
        return [
            ForeignReference::OPTION_RESTRICT => 'RESTRICT',
            ForeignReference::OPTION_CASCADE => 'CASCADE',
            ForeignReference::OPTION_SET_NULL => 'SET NULL',
        ];
    }

    public function connect($dsn, $username, $password, $driverOptions, $charset)
    {
        parent::connect($dsn, $username, $password, $driverOptions, $charset);

        if ($charset != '') {
            $this->dbh->exec('SET NAMES ' . $this->quote($charset));
        }
    }

    public function quoteIdentifier($name, $temporary=false)
    {
        return '"' . str_replace('"', '""', preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name)) . '"';
    }

    public function quoteConstraint($tableName, $constraintName)
    {
        return $this->quoteIdentifier($this->connection->prefix . $tableName . '_' . $constraintName);
    }

    public function buildFunctionPart($part)
    {
        if ($part->type === FunctionPart::TYPE_CONCAT) {
            return '(' . BuildHelper::buildPartList($this, $part->arguments, ' || ') . ')';
        }

        return parent::buildFunctionPart($part);
    }

    public function applySelectLimit($command, $start, $parts, $order)
    {
        $result = "{$start}{$parts}{$order}";

        if ($command->limit !== null) {
            $result .= ' LIMIT ' . $this->quote($command->limit);
        }

        if ($command->offset !== null) {
            $result .= ' OFFSET ' . $this->quote($command->offset);
        }

        return $result;
    }

    public function buildCreateTableStartPart($command)
    {
        return ($command->table->temporary ? 'TEMPORARY ' : '') . 'TABLE ' . $this->quoteTable($command->table->name);
    }

    public function buildDropIndexEndPart($command)
    {
        return '';
    }

    public function buildTruncateTableCommand($command)
    {
        return parent::buildTruncateTableCommand($command) . ' RESTART IDENTITY';
    }
}
