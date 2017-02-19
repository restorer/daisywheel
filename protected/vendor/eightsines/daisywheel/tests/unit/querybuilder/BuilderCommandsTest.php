<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;
use daisywheel\tests\unit\querybuilder\mock\MockExtBuildSpec;

class BuilderCommandsTest extends \PHPUnit_Framework_TestCase
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
     * @covers \daisywheel\querybuilder\QueryBuilder::select
     */
    public function testSelect()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        $expected = [
            "SELECT * FROM (SELECT TOP '10' * FROM (SELECT DISTINCT TOP '110' [t].*"
            . ", [user].[id] AS [userId]"
            . ", (LOWER([#qb_User].[firstName]) || ' ' || UPPER([qb_User].[lastName])) AS [fullName]"
            . ", (SELECT [id] FROM [qb_SomeTable] WHERE ([uid] = '42')) AS [innerSelect]"
            . " FROM [qb_UserDriverData] AS [t], [#qb_UserDriverDataAddition] AS [udda]"
            . " LEFT JOIN [qb_OrderQueue] AS [orderQueue] ON (([orderQueue].[orderId] = '42')"
            . " AND ([orderQueue].[driverUserId] = [t].[userId])"
            . " AND ([orderQueue].[dismissed] = '1'))"
            . " INNER JOIN [#qb_User] AS [user] ON ([user].[id] = [t].[userId])"
            . " RIGHT JOIN [#qb_User] ON ([id] = [t].[userId])"
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
            . " UNION SELECT * FROM [qb_Address]"
            . " UNION ALL SELECT * FROM [#qb_House]"
        ];

        $actual = $this->builder->select(
            $this->builder->col('t', '*'),
            $this->builder->as($this->builder->col('user', 'id'), 'userId'),
            $this->builder->as(
                $this->builder->concat(
                    $this->builder->lower($this->builder->col($this->builder->temp('User'), 'firstName')),
                    $this->builder->val(' '),
                    $this->builder->upper($this->builder->col($this->builder->tab('User'), 'lastName'))
                ),
                'fullName'
            ),
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
            ->leftJoin('OrderQueue', 'orderQueue')->on(
                $this->builder->and(
                    $this->builder->eq($this->builder->col('orderQueue', 'orderId'), $this->builder->val(42)),
                    $this->builder->eq(
                        $this->builder->col('orderQueue', 'driverUserId'),
                        $this->builder->col('t', 'userId')
                    ),
                    $this->builder->eq($this->builder->col('orderQueue', 'dismissed'), $this->builder->val(1))
                )
            )
            ->innerJoin($this->builder->temp('User'), 'user')->on(
                $this->builder->eq($this->builder->col('user', 'id'), $this->builder->col('t', 'userId'))
            )
            ->rightJoin($this->builder->temp('User'))->on(
                $this->builder->eq($this->builder->col('id'), $this->builder->col('t', 'userId'))
            )
            ->where(
                $this->builder->and(
                    $this->builder->neq($this->builder->col('t', 'status'), $this->builder->val('active')),
                    $this->builder->eq($this->builder->col('t', 'status'), $this->builder->param(':status')),
                    $this->builder->not(
                        $this->builder->eq($this->builder->col('user', 'isDeleted'), $this->builder->val(0))
                    ),
                    $this->builder->or(
                        $this->builder->eq($this->builder->col('orderQueue', 'id'), $this->builder->val(null)),
                        $this->builder->neq($this->builder->col('orderQueue', 'uid'), $this->builder->val(null))
                    ),
                    $this->builder->notIn($this->builder->col('t', 'id'), [])
                )
            )
            ->groupBy($this->builder->col('user', 'id'))
            ->groupBy($this->builder->col('user', 'fullName'))
            ->having(
                $this->builder->in(
                    $this->builder->col('t', 'id'),
                    [
                        $this->builder->val(41),
                        $this->builder->val(42),
                        $this->builder->val(43)
                    ]
                )
            )
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

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::insertInto
     */
    public function testInsert()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        $expected = [
            "INSERT INTO [qb_Address] ([id], [name]) VALUES"
            . " ('1', 'Test 1')"
            . ", ('1', ('Test' || ' 2'))"
            . ", ('3', 'Test 3')"
            . ", ('4', 'Test 4')"
            . " SELECT [id], [name] FROM [qb_House]"
        ];

        /** @noinspection ClassConstantCanBeUsedInspection */
        $actual = $this->builder->insertInto('Address', ['id', 'name'])
            ->values($this->builder->val(1), $this->builder->val('Test 1'))
            ->values(
                [
                    $this->builder->val(1),
                    $this->builder->concat($this->builder->val('Test'), $this->builder->val(' 2'))
                ]
            )
            ->values(
                [
                    [$this->builder->val(3), $this->builder->val('Test 3')],
                    [$this->builder->val(4), $this->builder->val('Test 4')],
                ]
            )
            ->select(
                $this->builder->select($this->builder->col('id'), $this->builder->col('name'))
                    ->from('House')
            )
            ->build();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::insertOrIgnore
     */
    public function testInsertOrIgnore()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        $expected = [
            "WITH qb_1 ([id], [key], [name], [subname]) AS (VALUES"
            . " ('1', 'a', 'n1', 's1')"
            . ", ('2', 'b', 'n2', 's2')"
            . ", ('3', 'c', 'n3', 's3')"
            . ", ('4', 'd', 'n4', 's4')"
            . ") INSERT INTO [qb_Address] ([id], [key], [name], [subname])"
            . " SELECT [id], [key], [name], [subname] FROM qb_1"
            . " WHERE NOT EXISTS (SELECT 1 FROM [qb_Address] WHERE [id] = qb_1.[id] AND [key] = qb_1.[key])"
        ];

        $actual = $this->builder->insertOrIgnore('Address', ['id', 'key'], ['name', 'subname'])
            ->values(
                $this->builder->val('1'),
                $this->builder->val('a'),
                $this->builder->val('n1'),
                $this->builder->val('s1')
            )
            ->values(
                [
                    $this->builder->val('2'),
                    $this->builder->val('b'),
                    $this->builder->val('n2'),
                    $this->builder->val('s2')
                ]
            )
            ->values(
                [
                    [
                        $this->builder->val('3'),
                        $this->builder->val('c'),
                        $this->builder->val('n3'),
                        $this->builder->val('s3')
                    ],
                    [
                        $this->builder->val('4'),
                        $this->builder->val('d'),
                        $this->builder->val('n4'),
                        $this->builder->val('s4')
                    ],
                ]
            )
            ->build();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::insertOrReplace
     */
    public function testInsertOrReplace()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        $expected = [
            "WITH qbv_1 ([id], [key], [name], [subname]) AS (VALUES"
            . " ('1', 'a', 'n1', 's1')"
            . ", ('2', 'b', 'n2', 's2')"
            . ", ('3', 'c', 'n3', 's3')"
            . ", ('4', 'd', 'n4', 's4')"
            . "), qbu_2 AS (UPDATE [qb_Address] SET"
            . " [name] = qbv_1.[name]"
            . ", [subname] = qbv_1.[subname]"
            . " FROM qbv_1 WHERE"
            . " [qb_Address].[id] = qbv_1.[id]"
            . " AND [qb_Address].[key] = qbv_1.[key]"
            . " RETURNING [qb_Address].*"
            . ") INSERT INTO [qb_Address] ([id], [key], [name], [subname])"
            . " SELECT [id], [key], [name], [subname] FROM qbv_1"
            . " WHERE NOT EXISTS (SELECT 1 FROM qbu_2 WHERE [id] = qbv_1.[id] AND [key] = qbv_1.[key])"
        ];

        $actual = $this->builder->insertOrReplace('Address', ['id', 'key'], ['name', 'subname'])
            ->values(
                $this->builder->val('1'),
                $this->builder->val('a'),
                $this->builder->val('n1'),
                $this->builder->val('s1')
            )
            ->values(
                [
                    $this->builder->val('2'),
                    $this->builder->val('b'),
                    $this->builder->val('n2'),
                    $this->builder->val('s2')
                ]
            )
            ->values(
                [
                    [
                        $this->builder->val('3'),
                        $this->builder->val('c'),
                        $this->builder->val('n3'),
                        $this->builder->val('s3')
                    ],
                    [
                        $this->builder->val('4'),
                        $this->builder->val('d'),
                        $this->builder->val('n4'),
                        $this->builder->val('s4')
                    ],
                ]
            )
            ->build();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::update
     */
    public function testUpdate()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        $expected = [
            "UPDATE [qb_Address] SET"
            . " [name] = '42'"
            . ", [name] = ([id] || ' 2')"
            . ", [name] = ([id] + '1')"
            . ", [name] = '42'"
            . ", [name] = '24'"
            . " WHERE ([id] = '42')"
        ];

        $actual = $this->builder->update('Address')
            ->set('name', $this->builder->val(42))
            ->set('name', $this->builder->concat($this->builder->col('id'), $this->builder->val(' 2')))
            ->set('name', $this->builder->add($this->builder->col('id'), $this->builder->val(1)))
            ->set(
                [
                    ['name', $this->builder->val(42)],
                    ['name', $this->builder->val(24)]
                ]
            )
            ->where($this->builder->eq($this->builder->col('id'), $this->builder->val(42)))
            ->build();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::deleteFrom
     */
    public function testDelete()
    {
        $this->assertEquals(
            ["DELETE FROM [qb_Address] WHERE ([id] = '42')"],
            $this->builder->deleteFrom('Address')
                ->where($this->builder->eq($this->builder->col('id'), $this->builder->val(42)))
                ->build()
        );

        $this->assertEquals(
            ['DELETE FROM ' . "[#qb_Address] WHERE ([id] = '42')"],
            $this->builder->deleteFrom($this->builder->temp('Address'))
                ->where($this->builder->eq($this->builder->col('id'), $this->builder->val(42)))
                ->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::createTable
     */
    public function testCreateTable()
    {
        /** @noinspection UnNecessaryDoubleQuotesInspection */
        $expected = [
            "CREATE TEMPORARY TABLE " . "[#qb_Test] ("
            . "[id] INT NOT NULL IDENTITY(1, 1) PRIMARY KEY"
            . ", [id2] BIGINT NOT NULL IDENTITY(1, 1) PRIMARY KEY"
            . ", [key] NVARCHAR(255) NOT NULL"
            . ", [name] NVARCHAR(255) DEFAULT 'TestDefault'"
            . ", [fooId] INT"
            . ", CONSTRAINT [qb_Test_idxKey] UNIQUE ([key])"
            . ", CONSTRAINT [qb_Test_idxKeyFooId] UNIQUE ([key], [fooId])"
            . ", CONSTRAINT [qb_Test_fkFooTest] FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id]) ON DELETE CASCADE ON UPDATE RESTRICT"
            . ", CONSTRAINT [qb_Test_fkFooTest] FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id]) ON DELETE RESTRICT ON UPDATE CASCADE"
            . ", CONSTRAINT [qb_Test_fkFooTest] FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id]) ON DELETE RESTRICT ON UPDATE SET NULL"
            . ", CONSTRAINT [qb_Test_fkFooTest] FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id]) ON DELETE CASCADE ON UPDATE RESTRICT"
            . ", CONSTRAINT [qb_Test_fkFooTest] FOREIGN KEY ([fooId]) REFERENCES [qb_Foo] ([id]) ON DELETE SET NULL ON UPDATE CASCADE"
            . ", CONSTRAINT [qb_Test_fkFooTest2] FOREIGN KEY ([key], [fooId]) REFERENCES [qb_Foo] ([uid], [id])"
            . " ON DELETE RESTRICT ON UPDATE RESTRICT"
            . ") ENGINE=InnoDB, CHARACTER SET = 'utf8', COLLATE = 'utf8_general_ci'",
            "CREATE INDEX [qb_Test_idxName] ON " . "[#qb_Test] ([name])",
            "CREATE INDEX [qb_Test_idxKeyName] ON " . "[#qb_Test] ([key], [name])",
        ];

        /** @noinspection ClassConstantCanBeUsedInspection */
        $actual = $this->builder->createTable(
            $this->builder->temp('Test'),
            [
                $this->builder->col('id')->primaryKey(),
                $this->builder->col('id2')->bigPrimaryKey(),
                $this->builder->col('key')->varChar(255)->notNull(),
                $this->builder->col('name')->varChar(255)->default('TestDefault'),
                $this->builder->col('fooId')->int()
            ]
        )
            ->index('idxName', 'name')
            ->index('idxKeyName', ['key', 'name'])
            ->unique('idxKey', 'key')
            ->unique('idxKeyFooId', ['key', 'fooId'])
            ->foreignKey('fkFooTest', 'fooId', 'Foo', 'id')->onDeleteCascade()
            ->foreignKey('fkFooTest', 'fooId', 'Foo', 'id')->onUpdateCascade()
            ->foreignKey('fkFooTest', 'fooId', 'Foo', 'id')->onDeleteRestrict()->onUpdateSetNull()
            ->foreignKey('fkFooTest', 'fooId', 'Foo', 'id')->onDeleteCascade()->onUpdateRestrict()
            ->foreignKey('fkFooTest', 'fooId', 'Foo', 'id')->onDeleteSetNull()->onUpdateCascade()
            ->foreignKey('fkFooTest2', ['key', 'fooId'], 'Foo', ['uid', 'id'])
            ->build();

        $this->assertEquals($expected, $actual);

        /** @noinspection ClassConstantCanBeUsedInspection */
        $this->assertEquals(
            ['CREATE TABLE [qb_Test] AS SELECT * FROM [qb_Foo]'],
            $this->builder->createTable('Test')->asSelect(
                $this->builder->select($this->builder->col('*'))->from('Foo')
            )->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::createTable
     */
    public function testCreateTableExt()
    {
        $builderExt = new QueryBuilder(new MockExtBuildSpec());

        /** @noinspection ClassConstantCanBeUsedInspection */
        $this->assertEquals(
            ['SELECT * INTO [qb_Test] FROM [qb_Foo]'],
            $builderExt->createTable('Test')->asSelect(
                $builderExt->select($builderExt->col('*'))->from('Foo')
            )->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::createTable
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testCreateTableException1()
    {
        $this->builder->createTable('User')->build();
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::createTable
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testCreateTableException2()
    {
        $this->builder->createTable(
            'User',
            [
                $this->builder->col('id')->primaryKey(),
            ]
        )->asSelect(
            $this->builder->select($this->builder->col('*'))->from('Foo')
        )->build();
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::createTable
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testCreateTableException3()
    {
        $this->builder->createTable('User')
            ->index('idxKey', 'key')
            ->asSelect(
                $this->builder->select($this->builder->col('*'))->from('Foo')
            )->build();
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::createTable
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testCreateTableException4()
    {
        $this->builder->createTable('User')
            ->index('idxKey', 'key')
            ->asSelect(
                $this->builder->select($this->builder->col('*'))->from('Foo')
            )->build();
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::createTable
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testCreateTableException5()
    {
        $this->builder->createTable('User')
            ->foreignKey('fkFooTest', 'fooId', 'Foo', 'id')->onDeleteCascade()
            ->asSelect(
                $this->builder->select($this->builder->col('*'))->from('Foo')
            )->build();
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::dropTable
     */
    public function testDropTable()
    {
        $this->assertEquals(['DROP TABLE [qb_User]'], $this->builder->dropTable('User')->build());

        $this->assertEquals(
            ['DROP TEMPORARY TABLE [#qb_User]'],
            $this->builder->dropTable($this->builder->temp('User'))->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::truncateTable
     */
    public function testTruncateTable()
    {
        $this->assertEquals(
            ['TRUNCATE TABLE [qb_User] RESTART IDENTITY'],
            $this->builder->truncateTable('User')->build()
        );

        $this->assertEquals(
            ['TRUNCATE TABLE [#qb_User] RESTART IDENTITY'],
            $this->builder->truncateTable($this->builder->temp('User'))->build()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\QueryBuilder::truncateTable
     */
    public function testTruncateTableExt()
    {
        $builderExt = new QueryBuilder(new MockExtBuildSpec());

        $this->assertEquals(
            [
                'DELETE FROM [qb_User]',
                "DELETE FROM SQLITE_SEQUENCE WHERE name = 'qb_User'",
            ],
            $builderExt->truncateTable('User')->build()
        );
    }
}
