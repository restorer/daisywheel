<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ColumnPart;
use daisywheel\db\builder\ForeignReference;
use daisywheel\db\builder\FunctionPart;

// TODO:
// Drop table - restrict if constraint exists [ pragma table_info('tablename') , pragma foreign_key_list('tablename') ]

class SqliteDriver extends BaseDriver
{
    public function getColumnTypeMap()
    {
        return array(
            ColumnPart::TYPE_PRIMARYKEY => array(
                'type' => 'INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT',
                'supportNotNull' => false,
                'supportDefault' => false,
            ),
            ColumnPart::TYPE_BIGPRIMARYKEY => array(
                'type' => 'INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT',
                'supportNotNull' => false,
                'supportDefault' => false,
            ),
            ColumnPart::TYPE_TYNYINT => array(
                'type' => 'INTEGER',
            ),
            ColumnPart::TYPE_SMALLINT => array(
                'type' => 'INTEGER',
            ),
            ColumnPart::TYPE_INT => array(
                'type' => 'INTEGER',
            ),
            ColumnPart::TYPE_BIGINT => array(
                'type' => 'INTEGER',
            ),
            ColumnPart::TYPE_DECIMAL => array(
                'type' => 'NUMERIC',
                'supportOptions' => array(0, 2),
            ),
            ColumnPart::TYPE_FLOAT => array(
                'type' => 'REAL',
            ),
            ColumnPart::TYPE_DOUBLE => array(
                'type' => 'REAL',
            ),
            ColumnPart::TYPE_DATE => array(
                'type' => 'NUMERIC', // according to http://www.sqlite.org/datatype3.html
            ),
            ColumnPart::TYPE_TIME => array(
                'type' => 'NUMERIC', // according to http://www.sqlite.org/datatype3.html
            ),
            ColumnPart::TYPE_DATETIME => array(
                'type' => 'NUMERIC', // according to http://www.sqlite.org/datatype3.html
            ),
            ColumnPart::TYPE_CHAR => array(
                'type' => 'TEXT',
                'supportOptions' => array(1, 1),
            ),
            ColumnPart::TYPE_VARCHAR => array(
                'type' => 'TEXT',
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
                'type' => 'BLOB',
            ),
            ColumnPart::TYPE_MEDIUMBLOB => array(
                'type' => 'BLOB',
            ),
            ColumnPart::TYPE_LONGBLOB => array(
                'type' => 'BLOB',
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
        $this->dbh->exec('PRAGMA foreign_keys=ON');
        // PRAGMA encoding="UTF-8"
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

        if ($command->offset !== null) {
            $result .= ' LIMIT ' . $this->quote($command->offset) . ', ' . $this->quote($command->limit);
        } elseif ($command->limit !== null) {
            $result .= ' LIMIT ' . $this->quote($command->limit);
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
        return array(
            'DELETE FROM ' . $this->quoteTable($command->table->name),
            'DELETE FROM SQLITE_SEQUENCE WHERE name=' . $this->quote($command->table->name),
        );
    }
}
