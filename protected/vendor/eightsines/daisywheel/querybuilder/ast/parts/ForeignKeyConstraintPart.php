<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\commands\CreateTableCommand;
use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\Part;
use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildSpec;

/**
 * @method ForeignKeyConstraintPart foreignKey(string $name, string|string[] $columns, string|TablePart $refTable, string|string[] $refColumns)
 * @method CreateTableCommand asSelect(SelectCommand $select)
 * @method string[] build()
 */
class ForeignKeyConstraintPart implements Part
{
    const OPTION_RESTRICT = 'RESTRICT';
    const OPTION_CASCADE = 'CASCADE';
    const OPTION_SET_NULL = 'SET NULL';

    /** @var mixed */
    protected $owner;

    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string */
    protected $name;

    /** @var string[] */
    protected $columns;

    /** @var TablePart */
    protected $refTable;

    /** @var string[] */
    protected $refColumns;

    /** @var string */
    protected $onDeleteOption = self::OPTION_RESTRICT;

    /** @var string */
    protected $onUpdateOption = self::OPTION_RESTRICT;

    /**
     * @param mixed $owner
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $name
     * @param string[] $columns
     * @param TablePart $refTable
     * @param string[] $refColumns
     *
     * @throws BuildException
     */
    public function __construct($owner, $spec, $table, $name, $columns, $refTable, $refColumns)
    {
        if (empty($columns)) {
            throw new BuildException('At least one column required');
        }

        if (empty($refColumns)) {
            throw new BuildException('At least one reference column required');
        }

        $this->owner = $owner;
        $this->spec = $spec;
        $this->table = $table;
        $this->name = $name;
        $this->columns = $columns;
        $this->refTable = $refTable;
        $this->refColumns = $refColumns;
    }

    /**
     * @return self
     */
    public function onDeleteRestrict()
    {
        $this->onDeleteOption = self::OPTION_RESTRICT;
        return $this;
    }

    /**
     * @return self
     */
    public function onDeleteCascade()
    {
        $this->onDeleteOption = self::OPTION_CASCADE;
        return $this;
    }

    /**
     * @return self
     */
    public function onDeleteSetNull()
    {
        $this->onDeleteOption = self::OPTION_SET_NULL;
        return $this;
    }

    /**
     * @return self
     */
    public function onUpdateRestrict()
    {
        $this->onUpdateOption = self::OPTION_RESTRICT;
        return $this;
    }

    /**
     * @return self
     */
    public function onUpdateCascade()
    {
        $this->onUpdateOption = self::OPTION_CASCADE;
        return $this;
    }

    /**
     * @return self
     */
    public function onUpdateSetNull()
    {
        $this->onUpdateOption = self::OPTION_SET_NULL;
        return $this;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return $this->spec->buildCreateForeignKeyPart(
            "CONSTRAINT {$this->spec->quoteConstraint($this->table->getName(), $this->name)} FOREIGN KEY ("
            . implode(
                ', ',
                array_map(
                    /** @return string */
                    function ($v) {
                        return $this->spec->quoteIdentifier($v);
                    },
                    $this->columns
                )
            )
            . ") REFERENCES {$this->refTable->buildPart()} ("
            . implode(
                ', ',
                array_map(
                    /** @return string */
                    function ($v) {
                        return $this->spec->quoteIdentifier($v);
                    },
                    $this->refColumns
                )
            )
            . ')',
            $this->onDeleteOption,
            $this->onUpdateOption
        );
    }

    /**
     * @param string $name
     * @param mixed $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->owner, $name], $arguments);
    }

    /**
     * @param string $startSql
     * @param string $onDeleteOption
     * @param string $onUpdateOption
     *
     * @return string
     */
    public static function basicBuild($startSql, $onDeleteOption, $onUpdateOption)
    {
        return "{$startSql} ON DELETE {$onDeleteOption} ON UPDATE {$onUpdateOption}";
    }
}
