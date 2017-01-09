<?php

namespace daisywheel\tests\unit\querybuilder\mock;

class MockExtBuildSpec extends MockBuildSpec
{
    /**
     * @override
     */
    public function buildTruncateTableCommand($name, $temporary)
    {
        return [
            "DELETE FROM {$this->quoteTable($name, $temporary)}",
            "DELETE FROM SQLITE_SEQUENCE WHERE name={$this->quote($name)}",
        ];
    }
}
