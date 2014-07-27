<?php

namespace app;

use daisywheel\core\BaseBootstrapper;
use daisywheel\web\Response;

class Bootstrapper extends BaseBootstrapper
{
    protected function buildSelectSql()
    {
        $orderId = 42;
        $driverStatus = 'active';

        return $this->context->db->builder(function($b) use ($orderId, $driverStatus) {
            return $b->select()
                ->distinct()
                ->columns(
                    $b->c('t', '*'),
                    $b->c('user', 'id')->as('userId'),
                    $b->concat(
                        $b->lower($b->c($b->temp('User'), 'firstName')),
                        ' ',
                        $b->upper($b->c('user', 'lastName'))
                    )->as('fullName'),
                    $b->select()->columns($b->c('id'))->from('SomeTable')->where($b->eq('uid', 42))->as('innerSelect'),
                    $b->not($b->gt(44, $b->neg(2)))->as('computedValue')
                )
                ->from('UserDriverData', 't')
                ->from($b->temp('UserDriverDataAddition'), 'udda')
                ->leftJoin('OrderQueue', 'orderQueue')
                ->on(
                    $b->eq($b->c('orderQueue', 'orderId'), $orderId)
                    ->and($b->eq($b->c('orderQueue', 'driverUserId'), $b->c('t', 'userId')))
                    ->and($b->eq($b->c('orderQueue', 'dismissed'), 1))
                )
                ->innerJoin($b->temp('User'), 'user')
                ->on($b->eq($b->c('user', 'id'), $b->c('t', 'userId')))
                ->where(
                    $b->eq($b->c('t', 'status'), $driverStatus)
                    ->and($b->not($b->eq($b->c('user', 'isDeleted'), 0)))
                    ->and(
                        $b->eq($b->c('orderQueue', 'id'), null)
                        ->or($b->neq($b->c('orderQueue', 'uid'), null))
                    )
                    ->and($b->notIn($b->c('t', 'id'), array()))
                )
                ->groupBy($b->c('user', 'id'))
                ->groupBy($b->c('user', 'fullName'))
                ->having($b->in($b->c('t', 'id'), array(41, 42, 43)))
                ->orderBy($b->c('fullName'), false)
                ->orderBy($b->c('id'))
                ->offset(100)
                ->limit(10)
                ->union(
                    $b->select()->from('Address')
                )
                ->unionAll(
                    $b->select()->from($b->temp('House'))
                )
            ;
        })->build();
    }

    protected function buildInsertSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->insert()
                ->into('Address')
                ->columns('id', 'name')
                ->values(1, 'Test 1')
                ->values(2, $b->concat($b->c('id'), ' 2'))
                ->values(array(
                    array(3, 'Test 3'),
                    array(4, 'Test 4'),
                ))
                ->select()
                ->columns('id', 'name')
                ->from('House')
            ;
        })->build();
    }

    protected function buildDeleteSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->delete()
                ->from('Address')
                ->where($b->eq($b->c('id'), 42))
            ;
        })->build();
    }

    protected function buildUpdateSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->update()
                ->table('Address')
                ->set('name', 42)
                ->set('name', $b->concat($b->c('id'), ' 2'))
                ->set('name', $b->add($b->c('id'), 1))
                ->set(array(
                    array('name', 42),
                    array('name', 24),
                ))
                ->where($b->eq($b->c('id'), 42))
            ;
        })->build();
    }

    protected function buildCreateTableSql()
    {
        // http://dev.mysql.com/doc/refman/5.1/en/create-table.html
        // http://sqlite.org/lang_createtable.html
        // http://www.postgresql.org/docs/8.1/static/sql-createtable.html
        // http://technet.microsoft.com/en-us/library/ms174979.aspx

        return $this->context->db->builder(function($b) {
            return $b->createTable($b->temp('Test'))
                ->columns(
                    $b->c('id')->primaryKey(), // bigPrimaryKey()
                    $b->c('key')->varChar(255)->notNull(),
                    $b->c('name')->varChar(255)->default('TestDefault'),
                    $b->c('fooId')->int()
                )
                ->unique('idxKey', 'key')
                ->unique('idxKeyFooId', 'key', 'fooId')
                ->unique('idxKeyFooId2', array('key', 'fooId'))
                ->index('idxName', 'name')
                ->index('idxKeyName', array('key', 'name'))
                ->index('idxKeyName2', 'key', 'name')
                ->foreignKey('fkFooTest', 'fooId')->references('Foo', 'id')->onDeleteSetNull()->onUpdateCascade()
                ->foreignKey('fkFooTest2', array('key', 'fooId'))->references('Foo', array('uid', 'id'))
                ->foreignKey('fkFooTest3', 'key', 'fooId')->references('Foo', 'uid', 'id')
            ;
        })->build();
    }

    protected function buildCreateIndexSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->createIndex('idxTest')
                ->on('Test')
                ->columns('key', 'value')
            ;
        })->build();
    }

    protected function buildDropTableSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->dropTable($b->temp('Test'));
        })->build();
    }

    protected function buildDropIndexSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->dropIndex('idxText')
                ->on('Test');
        })->build();
    }

    protected function buildTruncateTableSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->truncateTable('Test');
        })->build();
    }

    protected function buildAlterTableSql()
    {
        // http://www.w3schools.com/sql/sql_default.asp

        // alterTable('xxx')->renameTo('yyy')
        // alterTable('xxx')->add()->column($b->c('key')->varChar(255)->notNull())
        // add()->index() - instead of createIndex?
        // add()->unique()
        // add()->foreignKey()
        // alter()->column('key')->default('gg')
        // alter()->column('key')->dropDefault()
        // modify()->column($b->c('key')->varChar(128)->notNull())
        // change()->column('key')->to($b->c('key2')->varChar(255)->notNull())
        // drop()->column('key')
        // drop()->primaryKey()
        // drop()->index() - instead of dropIndex?
        // drop()->unique()
        // drop()->foreignKey()
        // rename()->column()
        // rename constraint?

        return null;
    }

    public function run()
    {
        $sql = null;

        // $sql = $this->buildSelectSql();
        // $sql = $this->buildInsertSql();
        // $sql = $this->buildDeleteSql();
        // $sql = $this->buildUpdateSql();
        // $sql = $this->buildCreateTableSql();
        // $sql = $this->buildCreateIndexSql();
        // $sql = $this->buildDropTableSql();
        // $sql = $this->buildDropIndexSql();
        // $sql = $this->buildTruncateTableSql();
        $sql = $this->buildAlterTableSql();

        // transactions

        $this->context->response->contentType = Response::MIME_TEXT;
        $this->context->response->dump($sql);
    }
}
