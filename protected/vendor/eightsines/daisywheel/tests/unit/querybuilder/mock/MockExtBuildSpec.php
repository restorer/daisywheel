<?php

namespace daisywheel\tests\unit\querybuilder\mock;

class MockExtBuildSpec extends MockBuildSpec
{
    /**
     * @see MockBuildSpec::buildCreateTableAsSelectCommand()
     * @inheritdoc
     */
    public function buildCreateTableAsSelectCommand($quotedTable, $temporary, $select)
    {
        return [$select->buildSql(" INTO {$quotedTable}")];
    }

    /**
     * @see MockBuildSpec::buildTruncateTableCommand()
     * @inheritdoc
     */
    public function buildTruncateTableCommand($tableSql, $tableName)
    {
        return [
            "DELETE FROM {$tableSql}",
            "DELETE FROM SQLITE_SEQUENCE WHERE name = {$this->quote($this->applyTablePrefix($tableName))}",
        ];
    }

    /**
     * @see MockBuildSpec::buildAlterTableRenameToCommand()
     * @inheritdoc
     */
    public function buildAlterTableRenameToCommand($table, $newName)
    {
        return ["EXEC sp_rename {$this->quote($this->applyTablePrefix($table->getName()))}, {$this->quote($this->applyTablePrefix($newName))}"];
    }
}
