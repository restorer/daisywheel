<?php

namespace daisywheel\tests\mock\db;

use daisywheel\core\InvalidArgumentsException;

class MockPdo
{
    protected static $variantMap = [
        'mysql' => 'mysql',
        'sqlite' => 'sqlite',
        'pgsql' => 'pgsql',
        'sqlsrv' => 'mssql',
        'dblib' => 'mssql',
    ];

    protected $variant;

    public function __construct($dsn, $username, $password, $driverOptions)
    {
        if (!preg_match('/^([a-zA-Z]+):/', $dsn, $mt)) {
            throw new InvalidArgumentsException();
        }

        if (!isset(self::$variantMap[$mt[1]])) {
            throw new InvalidArgumentsException();
        }

        $this->variant = self::$variantMap[$mt[1]];
    }

    public function exec($sql)
    {
    }

    public function quote($value)
    {
        return "'" . str_replace("'", "\\'", print_r($value, true)) . "'";
    }

    public function prepare($sql)
    {
        return new MockPdoStatement($sql);
    }

    public function lastInsertId()
    {
        return 1;
    }
}
