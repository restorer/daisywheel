<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ColumnPart;
use daisywheel\db\builder\ForeignReference;

class MySqlDriver extends BaseDriver
{
    public function getColumnTypeMap()
    {
        return array(
            ColumnPart::TYPE_PRIMARYKEY => array(
                'type' => 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ),
            ColumnPart::TYPE_BIGPRIMARYKEY => array(
                'type' => 'BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ),
            ColumnPart::TYPE_TYNYINT => array(
                'type' => 'TINYINT',
            ),
            ColumnPart::TYPE_SMALLINT => array(
                'type' => 'SMALLINT',
            ),
            ColumnPart::TYPE_INT => array(
                'type' => 'INT',
            ),
            ColumnPart::TYPE_BIGINT => array(
                'type' => 'BIGINT',
            ),
            ColumnPart::TYPE_DECIMAL => array(
                'type' => 'DECIMAL',
                'supportOptions' => array(0, 2),
            ),
            ColumnPart::TYPE_FLOAT => array(
                'type' => 'FLOAT',
            ),
            ColumnPart::TYPE_DOUBLE => array(
                'type' => 'DOUBLE',
            ),
            ColumnPart::TYPE_DATE => array(
                'type' => 'DATE',
            ),
            ColumnPart::TYPE_TIME => array(
                'type' => 'TIME',
            ),
            ColumnPart::TYPE_DATETIME => array(
                'type' => 'DATETIME',
            ),
            ColumnPart::TYPE_CHAR => array(
                'type' => 'CHAR',
                'supportOptions' => array(1, 1),
            ),
            ColumnPart::TYPE_VARCHAR => array(
                'type' => 'VARCHAR',
                'supportOptions' => array(1, 1),
            ),
            ColumnPart::TYPE_TEXT => array(
                'type' => 'TEXT',
            ),
            ColumnPart::TYPE_MEDIUMTEXT => array(
                'type' => 'MEDIUMTEXT',
            ),
            ColumnPart::TYPE_LONGTEXT => array(
                'type' => 'LONGTEXT',
            ),
            ColumnPart::TYPE_BLOB => array(
                'type' => 'BLOB',
            ),
            ColumnPart::TYPE_MEDIUMBLOB => array(
                'type' => 'MEDIUMBLOB',
            ),
            ColumnPart::TYPE_LONGBLOB => array(
                'type' => 'LONGBLOB',
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
        if (!array_key_exists(\PDO::ATTR_EMULATE_PREPARES, $driverOptions)) {
            $driverOptions[\PDO::ATTR_EMULATE_PREPARES] = true;
        }

        parent::connect($dsn, $username, $password, $driverOptions, $charset);

        if ($charset != '') {
            $this->dbh->exec('SET NAMES ' . $this->quote($charset));
        }
    }

    public function quoteIdentifier($name)
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

    public function getCreateTableStartPart($command)
    {
        return ($command->temporary ? 'TEMPORARY ' : '') . 'TABLE ' . $this->quoteTable($command->tableName);
    }
}
