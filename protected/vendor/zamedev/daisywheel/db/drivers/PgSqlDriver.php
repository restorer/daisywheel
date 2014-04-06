<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\FunctionPart;

class PgSqlDriver extends BaseDriver
{
    public function connect($dsn, $username, $password, $driverOptions, $charset)
    {
        parent::connect($dsn, $username, $password, $driverOptions, $charset);

        if ($charset != '') {
            $this->dbh->exec('SET NAMES ' . $this->quote($charset));
        }
    }

    public function quoteIdentifier($name)
    {
        return '"' . str_replace('"', '""', preg_replace('/[^A-Za-z_\-."\'` ]/u', '', $name)) . '"';
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
}
