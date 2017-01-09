<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ColumnPart;
use daisywheel\db\builder\ForeignReference;

class MySqlDriver extends BaseDriver
{
    public function getColumnTypeMap()
    {
        return [
            ColumnPart::TYPE_PRIMARYKEY => [
                'type' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ],
            ColumnPart::TYPE_BIGPRIMARYKEY => [
                'type' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ],
            ColumnPart::TYPE_TYNYINT => [
                'type' => 'TINYINT',
            ],
            ColumnPart::TYPE_SMALLINT => [
                'type' => 'SMALLINT',
            ],
            ColumnPart::TYPE_INT => [
                'type' => 'INT',
            ],
            ColumnPart::TYPE_BIGINT => [
                'type' => 'BIGINT',
            ],
            ColumnPart::TYPE_DECIMAL => [
                'type' => 'DECIMAL',
                'supportOptions' => [0, 2],
            ],
            ColumnPart::TYPE_FLOAT => [
                'type' => 'FLOAT',
            ],
            ColumnPart::TYPE_DOUBLE => [
                'type' => 'DOUBLE',
            ],
            ColumnPart::TYPE_DATE => [
                'type' => 'DATE',
            ],
            ColumnPart::TYPE_TIME => [
                'type' => 'TIME',
            ],
            ColumnPart::TYPE_DATETIME => [
                'type' => 'DATETIME',
            ],
            ColumnPart::TYPE_CHAR => [
                'type' => 'CHAR',
                'supportOptions' => [1, 1],
            ],
            ColumnPart::TYPE_VARCHAR => [
                'type' => 'VARCHAR',
                'supportOptions' => [1, 1],
            ],
            ColumnPart::TYPE_TEXT => [
                'type' => 'TEXT',
            ],
            ColumnPart::TYPE_MEDIUMTEXT => [
                'type' => 'MEDIUMTEXT',
            ],
            ColumnPart::TYPE_LONGTEXT => [
                'type' => 'LONGTEXT',
            ],
            ColumnPart::TYPE_BLOB => [
                'type' => 'BLOB',
            ],
            ColumnPart::TYPE_MEDIUMBLOB => [
                'type' => 'MEDIUMBLOB',
            ],
            ColumnPart::TYPE_LONGBLOB => [
                'type' => 'LONGBLOB',
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
        if (!array_key_exists(\PDO::ATTR_EMULATE_PREPARES, $driverOptions)) {
            $driverOptions[\PDO::ATTR_EMULATE_PREPARES] = true;
        }

        parent::connect($dsn, $username, $password, $driverOptions, $charset);

        if ($charset != '') {
            $this->dbh->exec('SET NAMES ' . $this->quote($charset));
        }
    }

    public function quoteIdentifier($name, $temporary=false)
    {
        return '`' . str_replace('`', '``', preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name)) . '`';
    }

    public function quoteConstraint($tableName, $constraintName)
    {
        return $this->quoteIdentifier($constraintName);
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

    /*
    public function buildCreateTableColumnType($columnPart)
    {
        if ($columnPart->columnType === ColumnPart::TYPE_PRIMARYKEY) {
            return 'INT NOT NULL AUTO_INCREMENT';
        } elseif ($columnPart->columnType === ColumnPart::TYPE_BIGPRIMARYKEY) {
            return 'BIGINT NOT NULL AUTO_INCREMENT';
        } else {
            return $columnPart->columnType . BuildHelper::buildCreateTableColumnOptions($this, $columnPart);
        }
    }
    */

    public function buildCreateTableStartPart($command)
    {
        return ($command->table->temporary ? 'TEMPORARY ' : '') . 'TABLE ' . $this->quoteTable($command->table->name);
    }

    public function buildCreateTableEndPart($command)
    {
        // TODO: utf8 character set and collation
        return ' ENGINE=InnoDB';
    }

    public function buildDropTableStartPart($command)
    {
        return ($command->table->temporary ? 'TEMPORARY ' : '') . 'TABLE ' . $this->quoteTable($command->table->name);
    }

    public function buildDropIndexEndPart($command)
    {
        return ' ON ' . $this->quoteTable($command->table->name, $command->table->temporary);
    }
}
