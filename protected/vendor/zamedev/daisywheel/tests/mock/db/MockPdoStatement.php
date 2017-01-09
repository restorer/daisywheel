<?php

namespace daisywheel\tests\mock\db;

use daisywheel\core\InvalidArgumentsException;

class MockPdoStatement
{
    public function __construct($sql)
    {
    }

    public function execute($params)
    {
    }

    public function closeCursor()
    {
    }

    public function fetchAll($mode)
    {
        return [];
    }

    public function fetch($mode)
    {
        return [];
    }

    public function fetchColumn()
    {
        return null;
    }

    public function rowCount()
    {
        return 1;
    }
}
