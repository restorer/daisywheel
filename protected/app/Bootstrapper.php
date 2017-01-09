<?php

namespace app;

use daisywheel\core\BaseBootstrapper;
use daisywheel\web\Response;

class Bootstrapper extends BaseBootstrapper
{
    protected function buildCreateIndexSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->createIndex('idxTest')
                ->on('Test')
                ->columns('key', 'value')
            ;
        })->build();
    }

    protected function buildDropIndexSql()
    {
        return $this->context->db->builder(function($b) {
            return $b->dropIndex('idxText')
                ->on('Test');
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

        // $sql = $this->buildCreateIndexSql();
        // $sql = $this->buildDropIndexSql();
        $sql = $this->buildAlterTableSql();

        // transactions

        $this->context->response->contentType = Response::MIME_TEXT;
        $this->context->response->dump($sql);
    }
}
