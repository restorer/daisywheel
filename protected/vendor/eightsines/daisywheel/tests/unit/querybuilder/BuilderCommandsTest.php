<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;
use daisywheel\tests\unit\querybuilder\mock\MockExtBuildSpec;

class BuilderCommandsTest extends \PHPUnit_Framework_TestCase
{
    protected $builder;

    public function __construct()
    {
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::dropTable
     */
    public function testDropTable()
    {
        $this->assertEquals(["DROP TABLE [User]"], $this->builder->dropTable('User')->build());
        $this->assertEquals(["DROP TEMPORARY TABLE [User]"], $this->builder->dropTable($this->builder->temp('User'))->build());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::truncateTable
     */
    public function testTruncateTable()
    {
        $this->assertEquals(["TRUNCATE TABLE [User] RESTART IDENTITY"], $this->builder->truncateTable('User')->build());
        $this->assertEquals(["TRUNCATE TABLE [#User] RESTART IDENTITY"], $this->builder->truncateTable($this->builder->temp('User'))->build());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::truncateTable
     */
    public function testTruncateTableExt()
    {
        $builderExt = new QueryBuilder(new MockExtBuildSpec());

        $this->assertEquals([
            "DELETE FROM [User]",
            "DELETE FROM SQLITE_SEQUENCE WHERE name='User'",
        ], $builderExt->truncateTable('User')->build());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::dropIndex
     */
    public function testDropIndex()
    {
        $this->assertEquals(["DROP INDEX [User_name] ON [User]"], $this->builder->dropIndex('name')->on('User')->build());
        $this->assertEquals(["DROP INDEX [User_name] ON [#User]"], $this->builder->dropIndex('name')->on($this->builder->temp('User'))->build());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::dropIndex
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testDropIndexException()
    {
        $this->builder->dropIndex('name')->build();
    }
}
