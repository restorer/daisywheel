<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;
use daisywheel\tests\unit\querybuilder\mock\MockExtBuildSpec;

class BuilderAlterCommandsTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryBuilder */
    protected $builder;

    /**
     * @see \PHPUnit_Framework_TestCase::__construct()
     * @inheritdoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::renameTo
     */
    public function testRenameTo()
    {
        $this->assertEquals(
            ['ALTER TABLE [qb_User] RENAME TO [qb_NewUser]'],
            $this->builder->alterTable('User')->renameTo('NewUser')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::renameTo
     */
    public function testRenameToExt()
    {
        $builderExt = new QueryBuilder(new MockExtBuildSpec());

        $this->assertEquals(
            ["EXEC sp_rename 'qb_User', 'qb_NewUser'"],
            $builderExt->alterTable('User')->renameTo('NewUser')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::addColumn
     */
    public function testAddColumn()
    {
        $this->assertEquals(
            ['ALTER TABLE [qb_User] ADD [name] NVARCHAR(255)'],
            $this->builder->alterTable('User')->addColumn($this->builder->col('name')->varChar(255))->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::addIndex
     */
    public function testAddIndex()
    {
        $this->assertEquals(
            ['CREATE INDEX [qb_User_name] ON [qb_User] ([name])'],
            $this->builder->alterTable('User')->addIndex('name', 'name')->build()
        );

        $this->assertEquals(
            ['CREATE INDEX [qb_User_name] ON ' . '[#qb_User] ([name])'],
            $this->builder->alterTable($this->builder->temp('User'))->addIndex('name', 'name')->build()
        );

        $this->assertEquals(
            ['CREATE INDEX [qb_User_name] ON [qb_User] ([name])'],
            $this->builder->alterTable('User')->addIndex('name', ['name'])->build()
        );

        $this->assertEquals(
            ['CREATE INDEX [qb_User_fullName] ON [qb_User] ([firstName], [lastName])'],
            $this->builder->alterTable('User')->addIndex('fullName', ['firstName', 'lastName'])->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::addIndex
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testAddIndexException()
    {
        $this->builder->alterTable('User')->addIndex('name', []);
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::addUniqueIndex
     */
    public function testAddUniqueIndex()
    {
        $this->assertEquals(
            ['CREATE UNIQUE INDEX [qb_User_name] ON [qb_User] ([name])'],
            $this->builder->alterTable('User')->addUniqueIndex('name', 'name')->build()
        );

        $this->assertEquals(
            ['CREATE UNIQUE INDEX [qb_User_name] ON [qb_User] ([name])'],
            $this->builder->alterTable('User')->addUniqueIndex('name', ['name'])->build()
        );

        $this->assertEquals(
            ['CREATE UNIQUE INDEX [qb_User_fullName] ON [qb_User] ([firstName], [lastName])'],
            $this->builder->alterTable('User')->addUniqueIndex('fullName', ['firstName', 'lastName'])->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::addUniqueIndex
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testAddUniqueIndexException()
    {
        $this->builder->alterTable('User')->addUniqueIndex('name', []);
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::addForeignKey
     */
    public function testAddForeignKey()
    {
        $this->assertEquals(
            [
                'ALTER TABLE [qb_User] ADD CONSTRAINT [qb_User_fkFooTest]'
                . ' FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id])'
                . ' ON DELETE CASCADE ON UPDATE RESTRICT'
            ],
            $this->builder->alterTable('User')
                ->addForeignKey('fkFooTest', 'fooId', 'Foo', 'id')
                ->onDeleteCascade()
                ->build()
        );

        $this->assertEquals(
            [
                'ALTER TABLE [qb_User] ADD CONSTRAINT [qb_User_fkFooTest]'
                . ' FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id])'
                . ' ON DELETE RESTRICT ON UPDATE CASCADE'
            ],
            $this->builder->alterTable('User')
                ->addForeignKey('fkFooTest', 'fooId', 'Foo', 'id')
                ->onUpdateCascade()
                ->build()
        );

        $this->assertEquals(
            [
                'ALTER TABLE [qb_User] ADD CONSTRAINT [qb_User_fkFooTest]'
                . ' FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id])'
                . ' ON DELETE RESTRICT ON UPDATE SET NULL'
            ],
            $this->builder->alterTable('User')
                ->addForeignKey('fkFooTest', 'fooId', 'Foo', 'id')
                ->onDeleteRestrict()
                ->onUpdateSetNull()
                ->build()
        );

        $this->assertEquals(
            [
                'ALTER TABLE [qb_User] ADD CONSTRAINT [qb_User_fkFooTest]'
                . ' FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id])'
                . ' ON DELETE CASCADE ON UPDATE RESTRICT'
            ],
            $this->builder->alterTable('User')
                ->addForeignKey('fkFooTest', 'fooId', 'Foo', 'id')
                ->onDeleteCascade()
                ->onUpdateRestrict()
                ->build()
        );

        $this->assertEquals(
            [
                'ALTER TABLE [qb_User] ADD CONSTRAINT [qb_User_fkFooTest]'
                . ' FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id])'
                . ' ON DELETE SET NULL ON UPDATE CASCADE'
            ],
            $this->builder->alterTable('User')
                ->addForeignKey('fkFooTest', 'fooId', 'Foo', 'id')
                ->onDeleteSetNull()
                ->onUpdateCascade()
                ->build()
        );

        $this->assertEquals(
            [
                'ALTER TABLE [qb_User] ADD CONSTRAINT [qb_User_fkFooTest2]'
                . ' FOREIGN KEY ([key], [fooId]) REFERENCES [qb_Foo] ([uid], [id])'
                . ' ON DELETE RESTRICT ON UPDATE RESTRICT'
            ],
            $this->builder->alterTable('User')
                ->addForeignKey('fkFooTest2', ['key', 'fooId'], 'Foo', ['uid', 'id'])
                ->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::alterColumn
     */
    public function x_testAlterColumn()
    {
        $this->assertEquals(
            [''],
            $this->builder->alterTable('User')
                ->alterColumn($this->builder->col('name')->varChar(128)->notNull())
                ->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::dropColumn
     */
    public function x_testDropColumn()
    {
        $this->assertEquals(
            [''],
            $this->builder->alterTable('User')->dropColumn('name')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::dropIndex
     */
    public function testDropIndex()
    {
        $this->assertEquals(
            ['DROP INDEX [qb_User_name] ON [qb_User]'],
            $this->builder->alterTable('User')->dropIndex('name')->build()
        );

        $this->assertEquals(
            ['DROP INDEX [qb_User_name] ON [#qb_User]'],
            $this->builder->alterTable($this->builder->temp('User'))->dropIndex('name')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::dropUniqueIndex
     */
    public function testDropUniqueIndex()
    {
        $this->assertEquals(
            ['DROP INDEX [qb_User_name] ON [qb_User]'],
            $this->builder->alterTable('User')->dropUniqueIndex('name')->build()
        );

        $this->assertEquals(
            ['DROP INDEX [qb_User_name] ON [#qb_User]'],
            $this->builder->alterTable($this->builder->temp('User'))->dropUniqueIndex('name')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::dropForeignKey
     */
    public function x_testDropForeignKey()
    {
        $this->assertEquals(
            [''],
            $this->builder->alterTable('User')->dropForeignKey('fkFooTest')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::renameColumn
     */
    public function x_testRenameColumn()
    {
        $this->assertEquals(
            [''],
            $this->builder->alterTable('User')->renameColumn('name', 'fullName')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::renameIndex
     */
    public function x_testRenameIndex()
    {
        $this->assertEquals(
            [''],
            $this->builder->alterTable('User')->renameIndex('name', 'name2')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::renameUniqueIndex
     */
    public function x_testRenameUniqueIndex()
    {
        $this->assertEquals(
            [''],
            $this->builder->alterTable('User')->renameUniqueIndex('name', 'name2')->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::alterTable
     * @covers \daisywheel\querybuilder\ast\AlterTableSelector::renameForeignKeyIndex
     */
    public function x_testRenameForeignKeyIndex()
    {
        $this->assertEquals(
            [''],
            $this->builder->alterTable('User')->renameForeignKeyIndex('fkFooTest', 'fkFooTest2')->build()
        );
    }
}
