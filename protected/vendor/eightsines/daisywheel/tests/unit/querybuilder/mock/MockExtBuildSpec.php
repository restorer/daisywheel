<?php

namespace daisywheel\tests\unit\querybuilder\mock;

class MockExtBuildSpec extends MockBuildSpec
{
    /**
     * @see MockBuildSpec::buildCreateTableAsSelectCommand()
     */
    public function buildCreateTableAsSelectCommand($quotedTable, $temporary, $select)
    {
        return [$select->buildSql(" INTO {$quotedTable}")];
    }

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
