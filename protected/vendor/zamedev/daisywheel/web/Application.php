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
        $sql = $context->db->builder(function($b) {
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
        $sql = $context->db->builder(function($b) {
            return $b->delete()
                ->from('Address')
                ->where($b->c('id'), '=', 42)
            ;
        })->build();
        */

        /*
        $sql = $context->db->builder(function($b) {
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

        $sql = $context->db->builder(function($b) {
            return $b->create()
                ->temporaryTable('Test')
                ->columns(
                    $b->c('id')->primaryKey(), // bigPrimaryKey()
                    $b->c('key')->varChar(255)->notNull(),
                    $b->c('name')->varChar(255)->default('TestDefault'),
                    $b->c('fooId')->int()
                )
                ->unique('idxKey', 'key')
                ->index('idxKeyName', 'key', 'name')
                ->foreignKey('fkFooTest', 'fooId', $b->ref('Foo', 'id')->onDeleteSetNull()->onUpdateCascade())
            ;
        })->build();

        /*
CREATE TABLE TestTable (
    `testField` INT,
    CONSTRAINT `idxTestField` UNIQUE (`testField`)
);
        */

        /*
CREATE TABLE `product_order` (
    `no` int(11) NOT NULL AUTO_INCREMENT,
    `product_category` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `customer_id` int(11) NOT NULL,
    PRIMARY KEY (`no`),
    KEY `product_category` (`product_category`,`product_id`),
    KEY `customer_id` (`customer_id`),
    CONSTRAINT `product_order_ibfk_1` FOREIGN KEY (`product_category`, `product_id`) REFERENCES `product` (`category`, `id`) ON UPDATE CASCADE,
    CONSTRAINT `product_order_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        */

        // http://www.w3schools.com/sql/sql_default.asp

        // sqlite:
        //
        // CONSTRAINT name
        // PRIMARY KEY [AUTOINCREMENT]
        // NOT NULL
        // UNIQUE
        // CHECK (expr)
        // DEFAULT ...
        // REFERENCES tablename (columnname,...) ON DELETE ... ON UPDATE ...
        // ... - SET NULL / SET DEFAULT / CASCADE / RESTRICT / NO ACTION
        //
        // unique можно задать при создании. но просто индексы надо создавать отдельно
        //
        // TEXT
        // INTEGER
        // REAL
        // NUMERIC
        // NONE (BLOB)

        // mysql:
        //
        // NOT NULL
        // DEFAULT ...
        // AUTO_INCREMENT
        // PRIMARY KEY
        // RESTRICT | CASCADE | SET NULL | NO ACTION - NO ACTION == RESTRICT
        //
        // TINYINT :: INTEGER:1
        // SMALLINT :: INTEGER:2
        // MEDIUMINT :: INTEGER:3
        // INT / INTEGER :: INTEGER:4 :: INT UNSIGNED ZEROFILL
        // BIGINT :: INTEGER:8
        // SERIAL is an alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE
        // DECIMAL(p,s) / NUMERIC(p,s) :: NUMERIC
        // FLOAT :: REAL:4
        // DOUBLE :: REAL:8
        // DATE
        // TIME
        // DATETIME
        // CHAR(M) CHARACTER SET utf8 COLLATE utf8_general_ci
        // VARCHAR(M) CHARACTER SET utf8 COLLATE utf8_general_ci - The length can be specified as a value from 0 to 255 before MySQL 5.0.3, and 0 to 65,535 in 5.0.3 and later versions
        // BINARY(M)
        // VARBINARY(M) - VARBINARY is bound to 255 bytes on MySQL 5.0.2 and below, to 65kB on 5.0.3 and above
        // TINYBLOB - 2^8
        // BLOB - 2^16
        // MEDIUMBLOB - 2^24
        // LONGBLOB - 2^32
        // TINYTEXT
        // TEXT
        // MEDIUMTEXT
        // LONGTEXT

        // pgsql:
        //
        // smallint :: INTEGER:2
        // integer :: INTEGER:4
        // bigint :: INTEGER:8
        // decimal(p,s) / numeric(p,s) :: NUMERIC
        // real :: REAL:4
        // double precision :: REAL:8
        // serial - autoincrementing integer 4 bytes
        // bigserial - large autoincrementing integer 8 bytes
        // bytea - binary data ("byte array")
        // character varying(n) / varchar(n)
        // character(n) / char(n)
        // date - calendar date (year, month, day)
        // time
        // timestamp
        // text
        //
        // id serial PRIMARY KEY - auto increment
        // NO ACTION | RESTRICT | CASCADE | SET NULL | SET DEFAULT (NO ACTION - The SQL Server Database Engine raises an error and the delete action on the row in the parent table is rolled back)

        // mssql:
        //
        // tinyint
        // smallint
        // int
        // bigint
        // decimal(p,s) / numeric(p,s)
        // float(24)
        // float(53)
        // date
        // time
        // datetime
        // nchar
        // nvarchar - nvarchar(1 ... 4000) OR nvarchar(max)
        // ntext - 2^30-1
        // varbinary - varbinary(1 ... 8000) OR varbinary(max) == 2^31-1
        //
        // ID int IDENTITY(1,1) PRIMARY KEY - auto increment
        // NO ACTION | CASCADE | SET NULL | SET DEFAULT

        // php:
        //
        // boolean
        // integer
        // float
        // string

        // create index
        // drop table
        // drop index
        // alter table
        // truncate
        // transactions

        header('Content-type: text/plain');
        echo $sql;
    }
}
