<?php

namespace app;

use daisywheel\core\BaseBootstrapper;
use daisywheel\web\Response;

class Bootstrapper extends BaseBootstrapper
{
    public function run()
    {
        $sql = null;

        /*
        $orderId = 42;
        $driverStatus = 'active';

        $sql = $this->context->db->builder(function($b) use ($orderId, $driverStatus) {
            return $b->select()
                ->distinct()
                ->columns(
                    $b->c('t', '*'),
                    $b->c('user', 'id')->as('userId'),
                    $b->concat(
                        $b->lower($b->c('user', 'firstName')),
                        ' ',
                        $b->upper($b->c('user', 'lastName'))
                    )->as('fullName'),
                    $b->select($b->c('id'))->from('SomeTable')->where($b->e('uid', '=', 42))->as('innerSelect'),
                    $b->e($b->not(44, '>', $b->e('-', 2)))->as('computedValue')
                )
                ->from('UserDriverData', 't')
                ->from('UserDriverDataAddition', 'udda')
                ->leftJoin('OrderQueue', 'orderQueue')
                ->on(
                    $b->e($b->c('orderQueue', 'orderId'), '=', $orderId)
                    ->and($b->c('orderQueue', 'driverUserId'), '=', $b->c('t', 'userId'))
                    ->and($b->c('orderQueue', 'dismissed'), '=', 1)
                )
                ->innerJoin('User', 'user')
                ->on($b->c('user', 'id'), '=', $b->c('t', 'userId'))
                ->where(
                    $b->e($b->c('t', 'status'), '=', $driverStatus)
                    ->and($b->not($b->c('user', 'isDeleted'), '=', 0))
                    ->and(
                        $b->e($b->c('orderQueue', 'id'), '=', null)
                        ->or($b->c('orderQueue', 'uid'), '<>', null)
                    )
                    ->and($b->c('t', 'id'), 'NOT IN', array())
                )
                ->groupBy($b->c('user', 'id'))
                ->groupBy($b->c('user', 'fullName'))
                ->having($b->c('t', 'id'), 'IN', array(41, 42, 43))
                ->orderBy($b->c('fullName'), false)
                ->orderBy($b->c('id'))
                ->offset(100)
                ->limit(10)
                ->union(
                    $b->select()->from('Address')
                )
                ->unionAll(
                    $b->select()->from('House')
                )
            ;
        })->build();
        */

        /*
        $sql = $this->context->db->builder(function($b) {
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
        */

        /*
        $sql = $this->context->db->builder(function($b) {
            return $b->delete()
                ->from('Address')
                ->where($b->c('id'), '=', 42)
            ;
        })->build();
        */

        /*
        $sql = $this->context->db->builder(function($b) {
            return $b->update()
                ->table('Address')
                ->set('name', 42)
                ->set('name', $b->concat($b->c('id'), ' 2'))
                ->set('name', $b->e($b->c('id'), '+', 1))
                ->set(array(
                    array('name', 42),
                    array('name', 24),
                ))
                ->where($b->c('id'), '=', 42)
            ;
        })->build();
        */

        // http://dev.mysql.com/doc/refman/5.1/en/create-table.html
        // http://sqlite.org/lang_createtable.html
        // http://www.postgresql.org/docs/8.1/static/sql-createtable.html
        // http://technet.microsoft.com/en-us/library/ms174979.aspx

        /*
        $sql = $this->context->db->builder(function($b) {
            return $b->createTemporaryTable('Test')
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
        */

        /*
        $sql = $this->context->db->builder(function($b) {
            return $b->createIndex('idxTest')
                ->on('Test')
                ->columns('key', 'value')
            ;
        })->build();
        */

        // drop table
        // drop index
        // truncate
        // alter table ( http://www.w3schools.com/sql/sql_default.asp )
        // transactions

        $this->context->response->contentType = Response::MIME_TEXT;
        $this->context->response->dump($sql);
    }
}
