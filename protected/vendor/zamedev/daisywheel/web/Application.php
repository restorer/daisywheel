<?php

namespace daisywheel\web;

use daisywheel\core\ClassLoader;
use daisywheel\core\Context;
use daisywheel\core\Config;

class Application
{
    public static function run($appPath) {
        ClassLoader::create('app', $appPath);

        $context = new Context(Config::create("{$appPath}/config", 'web')->defaults(array(
            'appPath' => $appPath,
            'components' => array(
                'request' => array(
                    'class' => 'daisywheel\web\Request',
                ),
                'response' => array(
                    'class' => 'daisywheel\web\Response',
                ),
            ),
        )));

        /*
        $orderId = 42;
        $driverStatus = 'active';

        $sql = $context->db->builder(function($b) use ($orderId, $driverStatus) {
            return $b->select()
                ->distinct()
                ->columns(
                    $b->f('t', '*'),
                    $b->f('user', 'id')->as('userId'),
                    $b->concat(
                        $b->lower($b->f('user', 'firstName')),
                        ' ',
                        $b->upper($b->f('user', 'lastName'))
                    )->as('fullName'),
                    $b->select($b->f('id'))->from('SomeTable')->where($b->e('uid', '=', 42))->as('innerSelect'),
                    $b->e($b->not(44, '>', $b->e('-', 2)))->as('computedValue')
                )
                ->from('UserDriverData', 't')
                ->from('UserDriverDataAddition', 'udda')
                ->leftJoin('OrderQueue', 'orderQueue')
                ->on(
                    $b->e($b->f('orderQueue', 'orderId'), '=', $orderId)
                    ->and($b->f('orderQueue', 'driverUserId'), '=', $b->f('t', 'userId'))
                    ->and($b->f('orderQueue', 'dismissed'), '=', 1)
                )
                ->innerJoin('User', 'user')
                ->on($b->f('user', 'id'), '=', $b->f('t', 'userId'))
                ->where(
                    $b->e($b->f('t', 'status'), '=', $driverStatus)
                    ->and($b->not($b->f('user', 'isDeleted'), '=', 0))
                    ->and(
                        $b->e($b->f('orderQueue', 'id'), '=', null)
                        ->or($b->f('orderQueue', 'uid'), '<>', null)
                    )
                    ->and($b->f('t', 'id'), 'NOT IN', array())
                )
                ->groupBy($b->f('user', 'id'))
                ->groupBy($b->f('user', 'fullName'))
                ->having($b->f('t', 'id'), 'IN', array(41, 42, 43))
                ->orderBy($b->f('fullName'), false)
                ->orderBy($b->f('id'))
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
        $sql = $context->db->builder(function($b) {
            return $b->insert()
                ->into('Address')
                ->columns('id', 'name')
                ->values(1, 'Test 1')
                ->values(2, $b->concat($b->f('id'), ' 2'))
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
        $sql = $context->db->builder(function($b) {
            return $b->delete()
                ->from('Address')
                ->where($b->f('id'), '=', 42)
            ;
        })->build();
        */

        // /*
        $sql = $context->db->builder(function($b) {
            return $b->update()
                ->table('Address')
                ->set('name', 42)
                ->set('name', $b->concat($b->f('id'), ' 2'))
                ->set('name', $b->e($b->f('id'), '+', 1))
                ->set(array(
                    array('name', 42),
                    array('name', 24),
                ))
                ->where($b->f('id'), '=', 42)
            ;
        })->build();
        // */

        header('Content-type: text/plain');
        echo $sql;
    }
}
