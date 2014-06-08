<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ColumnPart;
use daisywheel\db\builder\ForeignReference;
use daisywheel\db\builder\FunctionPart;
use daisywheel\core\InvalidConfigurationException;

class MsSqlDriver extends BaseDriver
{
    protected $sqlServerVersion = 0;

    public function getColumnTypeMap()
    {
        return array(
            ColumnPart::TYPE_PRIMARYKEY => array(
                'type' => 'INT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ),
            ColumnPart::TYPE_BIGPRIMARYKEY => array(
                'type' => 'BIGINT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
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
                'type' => 'FLOAT(24)',
            ),
            ColumnPart::TYPE_DOUBLE => array(
                'type' => 'FLOAT(53)',
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
                'type' => 'NCHAR',
                'supportOptions' => array(1, 1),
            ),
            ColumnPart::TYPE_VARCHAR => array(
                'type' => 'NVARCHAR',
                'supportOptions' => array(1, 1),
            ),
            ColumnPart::TYPE_TEXT => array(
                'type' => 'NVARCHAR(MAX)',
            ),
            ColumnPart::TYPE_MEDIUMTEXT => array(
                'type' => 'NVARCHAR(MAX)',
            ),
            ColumnPart::TYPE_LONGTEXT => array(
                'type' => 'NVARCHAR(MAX)',
            ),
            ColumnPart::TYPE_BLOB => array(
                'type' => 'VARBINARY(MAX)',
            ),
            ColumnPart::TYPE_MEDIUMBLOB => array(
                'type' => 'VARBINARY(MAX)',
            ),
            ColumnPart::TYPE_LONGBLOB => array(
                'type' => 'VARBINARY(MAX)',
            ),
        );
    }

    public function getReferenceOptionMap()
    {
        return array(
            ForeignReference::OPTION_RESTRICT => 'NO ACTION',
            ForeignReference::OPTION_CASCADE => 'CASCADE',
            ForeignReference::OPTION_SET_NULL => 'SET NULL',
        );
    }

    public function connect($dsn, $username, $password, $driverOptions, $charset)
    {
        if (!isset($driverOptions['sqlServerVersion'])) {
            throw new InvalidConfigurationException("'driverOptions' must has field 'sqlServerVersion'");
        }

        $this->sqlServerVersion = $driverOptions['sqlServerVersion'];
        unset($driverOptions['sqlServerVersion']);

        parent::connect($dsn, $username, $password, $driverOptions, $charset);
    }

    public function quoteIdentifier($name)
    {
        return '[' . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name) . ']';
    }

    public function quoteConstraint($tableName, $constraintName)
    {
        return $this->quoteIdentifier($constraintName);
    }

    protected function lastInsertId()
    {
        return $this->queryColumn("SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS BIGINT)");
    }

    public function buildFunctionPart($part)
    {
        if ($part->type === FunctionPart::TYPE_LENGTH) {
            return 'LEN(' . BuildHelper::buildPartList($this, $part->arguments) . ')';
        }

        if ($part->type === FunctionPart::TYPE_SUBSTR) {
            return 'SUBSTRING(' . BuildHelper::buildPartList($this, $part->arguments) . ')';
        }

        if ($part->type === FunctionPart::TYPE_TRIM) {
            return 'LTRIM(RTRIM((' . BuildHelper::buildPartList($this, $part->arguments) . '))';
        }

        return parent::buildFunctionPart($part);
    }

    public function applySelectLimit($command, $start, $parts, $order)
    {
        if ($command->offset === null) {
            return $start
                . ($command->limit !== null ? ' TOP ' . $this->quote($command->limit) : '')
                . $parts
                . $order
            ;
        }

        if ($this->sqlServerVersion >= 2012) {
            return "{$start}{$parts}{$order} OFFSET "
                . $this->quote($command->offset)
                . ' FETCH NEXT '
                . $this->quote($command->limit)
                . ' ROWS ONLY';
        }

        if ($this->sqlServerVersion >= 2005) {
            $column = 'rownumber_' . uniqid();

            return "SELECT * FROM ({$start} ROW_NUMBER() OVER ({$order}) AS {$column}, {$parts}) WHERE {$column} BETWEEN "
                . $this->quote($command->offset + 1)
                . ' AND '
                . $this->quote($command->offset + $command->limit);
        }

        return 'SELECT * FROM (SELECT TOP '
            . $this->quote($command->limit)
            . " * FROM ({$start} TOP "
            . $this->quote($command->offset + $command->limit)
            . "{$parts}{$order})"
            . BuildHelper::buildSelectOrder($this, $command, true)
            . "){$order}"
        ;
    }

    public function getCreateTableStartPart($command)
    {
        return 'TABLE ' . ($command->temporary ? '#' : '') . $this->quoteTable($command->tableName);
    }
}
