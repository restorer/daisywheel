<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\FunctionPart;
use daisywheel\core\InvalidConfigurationException;

class MsSqlDriver extends BaseDriver
{
    protected $sqlServerVersion = 0;

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
        return '[' . preg_replace('/[^A-Za-z_\-."\'` ]/u', '', $name) . ']';
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
            $field = 'rownumber_' . uniqid();

            return "SELECT * FROM ({$start} ROW_NUMBER() OVER ({$order}) AS {$field}, {$parts}) WHERE {$field} BETWEEN "
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
}
