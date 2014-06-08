<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ColumnPart;
use daisywheel\db\builder\ForeignReference;
use daisywheel\db\builder\FunctionPart;

class PgSqlDriver extends BaseDriver
{
    public function getColumnTypeMap()
    {
        return array(
            ColumnPart::TYPE_PRIMARYKEY => array(
                'type' => 'SERIAL NOT NULL PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ),
            ColumnPart::TYPE_BIGPRIMARYKEY => array(
                'type' => 'BIGSERIAL NOT NULL PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ),
            ColumnPart::TYPE_TYNYINT => array(
                'type' => 'SMALLINT',
            ),
            ColumnPart::TYPE_SMALLINT => array(
                'type' => 'SMALLINT',
            ),
            ColumnPart::TYPE_INT => array(
                'type' => 'INTEGER',
            ),
            ColumnPart::TYPE_BIGINT => array(
                'type' => 'BIGINT',
            ),
            ColumnPart::TYPE_DECIMAL => array(
                'type' => 'DECIMAL',
                'supportOptions' => array(0, 2),
            ),
            ColumnPart::TYPE_FLOAT => array(
                'type' => 'REAL',
            ),
            ColumnPart::TYPE_DOUBLE => array(
                'type' => 'DOUBLE PRECISION',
            ),
            ColumnPart::TYPE_DATE => array(
                'type' => 'DATE',
            ),
            ColumnPart::TYPE_TIME => array(
                'type' => 'TIME',
            ),
            ColumnPart::TYPE_DATETIME => array(
                'type' => 'TIMESTAMP',
            ),
            ColumnPart::TYPE_CHAR => array(
                'type' => 'CHARACTER',
                'supportOptions' => array(1, 1),
            ),
            ColumnPart::TYPE_VARCHAR => array(
                'type' => 'CHARACTER VARYING',
                'supportOptions' => array(1, 1),
            ),
            ColumnPart::TYPE_TEXT => array(
                'type' => 'TEXT',
            ),
            ColumnPart::TYPE_MEDIUMTEXT => array(
                'type' => 'TEXT',
            ),
            ColumnPart::TYPE_LONGTEXT => array(
                'type' => 'TEXT',
            ),
            ColumnPart::TYPE_BLOB => array(
                'type' => 'BYTEA',
            ),
            ColumnPart::TYPE_MEDIUMBLOB => array(
                'type' => 'BYTEA',
            ),
            ColumnPart::TYPE_LONGBLOB => array(
                'type' => 'BYTEA',
            ),
        );
    }

    public function getReferenceOptionMap()
    {
        return array(
            ForeignReference::OPTION_RESTRICT => 'RESTRICT',
            ForeignReference::OPTION_CASCADE => 'CASCADE',
            ForeignReference::OPTION_SET_NULL => 'SET NULL',
        );
    }

    public function connect($dsn, $username, $password, $driverOptions, $charset)
    {
        parent::connect($dsn, $username, $password, $driverOptions, $charset);

        if ($charset != '') {
            $this->dbh->exec('SET NAMES ' . $this->quote($charset));
        }
    }

    public function quoteIdentifier($name)
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

    public function getCreateTableStartPart($command)
    {
        return ($command->temporary ? 'TEMPORARY ' : '') . 'TABLE ' . $this->quoteTable($command->tableName);
    }
}
