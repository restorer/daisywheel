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
        return [
            ColumnPart::TYPE_PRIMARYKEY => [
                'type' => 'INT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
                'supportNotNull' => false,
                'supportDefault' => false,
            ],
            ColumnPart::TYPE_BIGPRIMARYKEY => [
                'type' => 'BIGINT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
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
                'type' => 'FLOAT(24)',
            ],
            ColumnPart::TYPE_DOUBLE => [
                'type' => 'FLOAT(53)',
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
                'type' => 'NCHAR',
                'supportOptions' => [1, 1],
            ],
            ColumnPart::TYPE_VARCHAR => [
                'type' => 'NVARCHAR',
                'supportOptions' => [1, 1],
            ],
            ColumnPart::TYPE_TEXT => [
                'type' => 'NVARCHAR(MAX)',
            ],
            ColumnPart::TYPE_MEDIUMTEXT => [
                'type' => 'NVARCHAR(MAX)',
            ],
            ColumnPart::TYPE_LONGTEXT => [
                'type' => 'NVARCHAR(MAX)',
            ],
            ColumnPart::TYPE_BLOB => [
                'type' => 'VARBINARY(MAX)',
            ],
            ColumnPart::TYPE_MEDIUMBLOB => [
                'type' => 'VARBINARY(MAX)',
            ],
            ColumnPart::TYPE_LONGBLOB => [
                'type' => 'VARBINARY(MAX)',
            ],
        ];
    }

    public function getReferenceOptionMap()
    {
        return [
            ForeignReference::OPTION_RESTRICT => 'NO ACTION',
            ForeignReference::OPTION_CASCADE => 'CASCADE',
            ForeignReference::OPTION_SET_NULL => 'SET NULL',
        ];
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

    public function quoteIdentifier($name, $temporary=false)
    {
        return '[' . ($temporary ? '#' : '') . preg_replace('/[^A-Za-z0-9_\-."\'` ]/u', '', $name) . ']';
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
            return 'LTRIM(RTRIM(' . BuildHelper::buildPartList($this, $part->arguments) . '))';
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
            $column = 'rownumber_' . ($this->isMock ? 1 : uniqid());

            return "SELECT * FROM ({$start} ROW_NUMBER() OVER (" . trim($order) . ") AS {$column},{$parts}) WHERE {$column} BETWEEN "
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

    public function buildCreateTableStartPart($command)
    {
        return 'TABLE ' . $this->quoteTable($command->table->name, $command->table->temporary);
    }

    public function buildDropIndexEndPart($command)
    {
        return ' ON ' . $this->quoteTable($command->table->name, $command->table->temporary);
    }
}
