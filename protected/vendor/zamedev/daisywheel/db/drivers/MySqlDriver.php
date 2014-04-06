<?php

namespace daisywheel\db\drivers;

class MySqlDriver extends BaseDriver
{
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
        return '`' . str_replace('`', '``', preg_replace('/[^A-Za-z_\-."\'` ]/u', '', $name)) . '`';
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
}
