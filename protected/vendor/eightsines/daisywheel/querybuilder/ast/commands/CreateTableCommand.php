<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildHelper;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\DataTypePart;
use daisywheel\querybuilder\ast\parts\ForeignKeyConstraintPart;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\ast\parts\UniqueConstraintPart;

class CreateTableCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var DataTypePart[] */
    protected $columns;

    /** @var CreateIndexCommand[] */
    protected $indexList = [];

    /** @var UniqueConstraintPart[] */
    protected $uniqueList = [];

    /** @var ForeignKeyConstraintPart[] */
    protected $foreignKeyList = [];

    /** @var SelectCommand|null */
    protected $select = null;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param DataTypePart[] $columns
     */
    public function __construct($spec, $table, $columns)
    {
        $this->spec = $spec;
        $this->table = $table;
        $this->columns = $columns;
    }

    /**
     * @param string $name
     * @param string|string[] $columns
     * @return self
     * @psalm-suppress TypeCoercion
     */
    public function index($name, $columns)
    {
        $this->indexList[] = new CreateIndexCommand($this->spec, $this->table, $name, BuildHelper::arg($columns), false);
        return $this;
    }

    /**
     * @param string $name
     * @param string|string[] $columns
     * @return self
     */
    public function unique($name, $columns)
    {
        $this->uniqueList[] = new UniqueConstraintPart($this->spec, $this->table, $name, BuildHelper::arg($columns));
        return $this;
    }

    /**
     * @param string $name
     * @param string|string[] $columns
     * @param string|TablePart $refTable
     * @param string|string[] $refColumns
     * @return ForeignKeyConstraintPart
     */
    public function foreignKey($name, $columns, $refTable, $refColumns)
    {
        $result = new ForeignKeyConstraintPart(
            $this,
            $this->spec,
            $this->table,
            $name,
            BuildHelper::arg($columns),
            TablePart::create($this->spec, $refTable),
            BuildHelper::arg($refColumns)
        );

        $this->foreignKeyList[] = $result;
        return $result;
    }

    /**
     * @param SelectCommand $select
     * @return self
     */
    public function asSelect($select)
    {
        $this->select = $select;
        return $this;
    }

    /**
     * @see Command::build()
     * @throws BuildException
     */
    public function build()
    {
        if (empty($this->columns) && $this->select === null) {
            throw new BuildException('Either columns or select required');
        }

        if ($this->select !== null) {
            if (!empty($this->columns) || !empty($this->indexList) || !empty($this->uniqueList) || !empty($this->foreignKeyList)) {
                throw new BuildException('For create-as-select no columns, indexes, unique indexes or foreign keys should be specified');
            }

            return $this->spec->buildCreateTableAsSelectCommand($this->table->buildPart(), $this->table->getTemporary(), $this->select);
        }

        return $this->spec->buildCreateTableCommand(
            $this->table->buildPart(),
            $this->table->getTemporary(),
            join(', ', array_merge(
                array_map(/** @return string */ function ($v) {
                    return $v->buildPart();
                }, $this->columns),
                array_map(/** @return string */ function ($v) {
                    return $v->buildPart();
                }, $this->uniqueList),
                array_map(/** @return string */ function ($v) {
                    return $v->buildPart();
                }, $this->foreignKeyList)
            )),
            $this->indexList
        );
    }

    /**
     * @param string $prependSql
     * @param string $quotedTable
     * @param string $partsSql
     * @param string $afterSql
     * @param CreateIndexCommand[] $indexList
     * @return string[]
     */
    public static function basicBuild($prependSql, $quotedTable, $partsSql, $afterSql, $indexList)
    {
        return array_merge(
            ["CREATE {$prependSql}TABLE {$quotedTable} ($partsSql)$afterSql"],
            array_map(/** @return string */ function ($v) {
                return $v->buildSql();
            }, $indexList)
        );
    }

    /**
     * @param string $prependSql
     * @param string $quotedTable
     * @param SelectCommand $select
     * @return string[]
     */
    public static function basicBuildCreateAsSelect($prependSql, $quotedTable, $select)
    {
        return ["CREATE {$prependSql}TABLE {$quotedTable} AS {$select->buildSql()}"];
    }
}
