<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\SelectCommand;

abstract class BaseDriver
{
    protected $connection = null;
    protected $dbh = null;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function connect($dsn, $username, $password, $driverOptions, $charset)
    {
        $this->dbh = new \PDO($dsn, $username, $password, $driverOptions);
    }

    public function quote($value)
    {
        return $this->dbh->quote($value);
    }

    public function quoteTable($name)
    {
        return $this->quoteIdentifier($this->connection->prefix . $name);
    }

    public function queryAll($sql, $params=array())
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);
        $sth->closeCursor();

        return $result;
    }

    public function queryRow($sql, $params=array())
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        $sth->closeCursor();

        return $result;
    }

    public function queryColumn($sql, $params=array())
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        $result = $sth->fetchColumn();
        $sth->closeCursor();

        return $result;
    }

    public function execute($sql, $params=array())
    {
        $sth = $this->dbh->prepare($sql);
        $sth->execute($params);
        return $sth->rowCount();
    }

    public function insert($sql, $params=array())
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

    protected function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    abstract public function quoteIdentifier($name);
    abstract public function applySelectLimit($command, $start, $parts, $order);
}
