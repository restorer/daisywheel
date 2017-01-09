<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\SelectCommand;

abstract class BaseDriver
{
    protected $connection = null;
    protected $dbh = null;
    protected $isMock = false;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function connect($dsn, $username, $password, $driverOptions, $charset)
    {
        if (preg_match('/^[a-zA-Z]+:mock=([a-zA-Z0-9_\\\\]+)/', $dsn, $mt)) {
            $className = $mt[1];
            $this->isMock = true;
        } else {
            $className = '\\PDO';
        }

        $this->dbh = new $className($dsn, $username, $password, $driverOptions);
    }

    public function quote($value)
    {
        // TODO: quote(print_r($value, true)) - ?
        return $this->dbh->quote($value);
    }

    public function quoteTable($name, $temporary=false)
    {
        return $this->quoteIdentifier($this->connection->prefix . $name, $temporary);
    }

    public function queryAll($sql, $params=[])
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $sth->closeCursor();

        return $result;
    }

    public function queryRow($sql, $params=[])
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        $sth->closeCursor();

        return $result;
    }

    public function queryColumn($sql, $params=[])
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        $result = $sth->fetchColumn();
        $sth->closeCursor();

        return $result;
    }

    public function execute($sql, $params=[])
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        return $sth->rowCount();
    }

    public function insert($sql, $params=[])
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        return $this->lastInsertId();
    }

    public function build($command)
    {
        return BuildHelper::build($this, $command);
    }

    public function buildFunctionPart($part)
    {
        return "{$part->type}(" . BuildHelper::buildPartList($this, $part->arguments) . ')';
    }

    public function buildCreateTableEndPart($command)
    {
        return '';
    }

    public function buildDropTableStartPart($command)
    {
        return 'TABLE ' . $this->quoteTable($command->table->name, $command->table->temporary);
    }

    public function buildTruncateTableCommand($command)
    {
        return 'TRUNCATE TABLE ' . $this->quoteTable($command->table->name, $command->table->temporary);
    }

    protected function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    abstract public function getColumnTypeMap();
    abstract public function getReferenceOptionMap();
    abstract public function quoteIdentifier($name, $temporary);
    abstract public function quoteConstraint($tableName, $constraintName);
    abstract public function applySelectLimit($command, $start, $parts, $order);
    abstract public function buildCreateTableStartPart($command);
    abstract public function buildDropIndexEndPart($command);
}
