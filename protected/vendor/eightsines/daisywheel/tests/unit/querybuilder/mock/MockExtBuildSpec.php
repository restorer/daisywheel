<?php

namespace daisywheel\tests\unit\querybuilder\mock;

class MockExtBuildSpec extends MockBuildSpec
{
    /**
     * @see MockBuildSpec::buildTruncateTableCommand()
     */
    public function buildTruncateTableCommand($tableSql, $tableName)
    {
        return [
            "DELETE FROM {$tableSql}",
            "DELETE FROM SQLITE_SEQUENCE WHERE name = {$this->quote($tableName)}",
        ];
    }
}
