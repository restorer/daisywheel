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

    public function testSelect()
    {
        $expected = [
            "SELECT * FROM (SELECT TOP '10' * FROM (SELECT DISTINCT TOP '110' [t].*"
            . ", [user].[id] AS [userId]"
            . ", (LOWER([#User].[firstName]) || ' ' || UPPER([User].[lastName])) AS [fullName]"
            . ", (SELECT [id] FROM [SomeTable] WHERE ([uid] = '42')) AS [innerSelect]"
            . " FROM [UserDriverData] AS [t], [#UserDriverDataAddition] AS [udda]"
            . " LEFT JOIN [OrderQueue] AS [orderQueue] ON (([orderQueue].[orderId] = '42')"
            . " AND ([orderQueue].[driverUserId] = [t].[userId])"
            . " AND ([orderQueue].[dismissed] = '1'))"
            . " INNER JOIN [#User] AS [user] ON ([user].[id] = [t].[userId])"
            . " RIGHT JOIN [#User] ON ([id] = [t].[userId])"
            . " WHERE (([t].[status] <> 'active')"
            . " AND ([t].[status] = :status)"
            . " AND (NOT ([user].[isDeleted] = '0'))"
            . " AND (([orderQueue].[id] IS NULL) OR ([orderQueue].[uid] IS NOT NULL))"
            . " AND (1 = 1))"
            . " GROUP BY [user].[id], [user].[fullName]"
            . " HAVING ([t].[id] IN ('41', '42', '43'))"
            . " ORDER BY [fullName] DESC, [id] ASC)"
            . " ORDER BY [fullName] ASC, [id] DESC)"
            . " ORDER BY [fullName] DESC, [id] ASC"
            . " UNION SELECT * FROM [Address]"
            . " UNION ALL SELECT * FROM [#House]"
        ];

        $actual = $this->builder->select(
                $this->builder->col('t', '*'),
                $this->builder->as($this->builder->col('user', 'id'), 'userId'),
                $this->builder->as($this->builder->concat(
                    $this->builder->lower($this->builder->col($this->builder->temp('User'), 'firstName')),
                    $this->builder->val(' '),
                    $this->builder->upper($this->builder->col('User', 'lastName'))
                ), 'fullName'),
                $this->builder->as(
                    $this->builder->select($this->builder->col('id'))
                        ->from('SomeTable')
                        ->where($this->builder->eq($this->builder->col('uid'), $this->builder->val(42))),
                    'innerSelect'
                )
            )
            ->distinct()
            ->from('UserDriverData', 't')
            ->from($this->builder->temp('UserDriverDataAddition'), 'udda')
            ->leftJoin('OrderQueue', 'orderQueue')->on($this->builder->and(
                $this->builder->eq($this->builder->col('orderQueue', 'orderId'), $this->builder->val(42)),
                $this->builder->eq($this->builder->col('orderQueue', 'driverUserId'), $this->builder->col('t', 'userId')),
                $this->builder->eq($this->builder->col('orderQueue', 'dismissed'), $this->builder->val(1))
            ))
            ->innerJoin($this->builder->temp('User'), 'user')->on(
                $this->builder->eq($this->builder->col('user', 'id'), $this->builder->col('t', 'userId'))
            )
            ->rightJoin($this->builder->temp('User'))->on(
                $this->builder->eq($this->builder->col('id'), $this->builder->col('t', 'userId'))
            )
            ->where($this->builder->and(
                $this->builder->neq($this->builder->col('t', 'status'), $this->builder->val('active')),
                $this->builder->eq($this->builder->col('t', 'status'), $this->builder->param(':status')),
                $this->builder->not($this->builder->eq($this->builder->col('user', 'isDeleted'), $this->builder->val(0))),
                $this->builder->or(
                    $this->builder->eq($this->builder->col('orderQueue', 'id'), $this->builder->val(null)),
                    $this->builder->neq($this->builder->col('orderQueue', 'uid'), $this->builder->val(null))
                ),
                $this->builder->notIn($this->builder->col('t', 'id'), [])
            ))
            ->groupBy($this->builder->col('user', 'id'))
            ->groupBy($this->builder->col('user', 'fullName'))
            ->having($this->builder->in($this->builder->col('t', 'id'), [$this->builder->val(41), $this->builder->val(42), $this->builder->val(43)]))
            ->orderBy($this->builder->col('fullName'), false)
            ->orderBy($this->builder->col('id'))
            ->offset(100)
            ->limit(10)
            ->union(
                $this->builder->select($this->builder->col('*'))->from('Address')
            )
            ->unionAll(
                $this->builder->select($this->builder->col('*'))->from($this->builder->temp('House'))
            )
            ->build();

        $this->assertEquals($expected, $actual);
    }

    // TODO "INSERT OR REPLACE", "INSERT OR ROLLBACK", "INSERT OR ABORT", "INSERT OR FAIL", "INSERT OR IGNORE"
    public function x_testInsert()
    {
        $sql = $this->builder->insertInto('Address', ['id', 'name'])
            ->values($this->builder->val(1), $this->builder->val('Test 1'))
            ->values($this->builder->val(1), $this->builder->concat($this->builder->col('id'), $this->builder->val(' 2')))
            ->values([
                [$this->builder->val(3), $this->builder->val('Test 3')],
                [$this->builder->val(4), $this->builder->val('Test 4')],
            ])
            ->select($this->builder->col('id'), $this->builder->col('name'))
            ->from('House')
            ->build();
    }

    public function x_testUpdate()
    {
        $sql = $this->builder->update('Address')
            ->set('name', $this->builder->val(42))
            ->set('name', $this->builder->concat($this->builder->col('id'), $this->builder->val(' 2')))
            ->set('name', $this->builder->add($this->builder->col('id'), $this->builder->val(1)))
            ->set([
                ['name', $this->builder->val(42)],
                ['name', $this->builder->val(24)]
            ])
            ->where($this->builder->eq($this->builder->col('id'), $this->builder->val(42)))
            ->build();
    }

    public function testDelete()
    {
        $this->assertEquals("DELETE FROM [Address] WHERE ([id] = '42')", $this->builder->deleteFrom('Address')
            ->where($this->builder->eq($this->builder->col('id'), $this->builder->val(42)))
            ->build()
        );

        $this->assertEquals("DELETE FROM " . "[#Address] WHERE ([id] = '42')", $this->builder->deleteFrom($this->builder->temp('Address'))
            ->where($this->builder->eq($this->builder->col('id'), $this->builder->val(42)))
            ->build()
        );
    }

    public function x_testCreateTable()
    {
        $sql = $this->builder->create($this->builder->temp('Test'), [
                $this->builder->col('id')->primaryKey(), // bigPrimaryKey()
                $this->builder->col('key')->varChar(255)->notNull(),
                $this->builder->col('name')->varChar(255)->default('TestDefault'),
                $this->builder->col('fooId')->int()
            ])
            ->unique('idxKey', 'key')
            ->unique('idxKeyFooId', ['key', 'fooId'])
            ->index('idxName', 'name')
            ->index('idxKeyName', ['key', 'name'])
            ->foreignKey('fkFooTest', 'fooId', 'Foo', 'id', $this->builder->onDeleteSetNull() | $this->builder->onUpdateCascade())
            ->foreignKey('fkFooTest2', ['key', 'fooId'], 'Foo', ['uid', 'id'])
        ;
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::dropTable
     */
    public function testDropTable()
    {
        $this->assertEquals(["DROP TABLE [User]"], $this->builder->dropTable('User')->build());
        $this->assertEquals(["DROP TEMPORARY TABLE [#User]"], $this->builder->dropTable($this->builder->temp('User'))->build());
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
            "DELETE FROM SQLITE_SEQUENCE WHERE name = 'User'",
        ], $builderExt->truncateTable('User')->build());
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::createIndex
     */
    public function testCreateIndex()
    {
        $this->assertEquals(
            ['CREATE INDEX [User_name] ON [User] ([name])'],
            $this->builder->createIndex('User', 'name', 'name')->build()
        );

        $this->assertEquals(
            ['CREATE INDEX [User_name] ON [User] ([name])'],
            $this->builder->createIndex('User', 'name', ['name'])->build()
        );

        $this->assertEquals(
            ['CREATE INDEX [User_fullName] ON [User] ([firstName], [lastName])'],
            $this->builder->createIndex('User', 'fullName', ['firstName', 'lastName'])->build()
        );
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::createIndex
     * @expectedException daisywheel\querybuilder\BuildException
     */
    public function testCreateIndexException()
    {
        $this->builder->createIndex('User', 'name', []);
    }

    /**
     * @covers daisywheel\querybuilder\QueryBuilder::dropIndex
     */
    public function testDropIndex()
    {
        $this->assertEquals(["DROP INDEX [User_name] ON [User]"], $this->builder->dropIndex('User', 'name')->build());
        $this->assertEquals(["DROP INDEX [User_name] ON [#User]"], $this->builder->dropIndex($this->builder->temp('User'), 'name')->build());
    }
}
