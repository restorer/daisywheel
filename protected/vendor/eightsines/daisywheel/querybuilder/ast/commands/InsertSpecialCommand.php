<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildHelper;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;

class InsertSpecialCommand implements Command
{
    const TYPE_IGNORE = "IGNORE";
    const TYPE_REPLACE = "REPLACE";

    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string[] */
    protected $keys;

    /** @var string[] */
    protected $columns;

    /** @var array<array<Expr>> */
    protected $values = [];

    /** @var string */
    protected $type;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string[] $keys
     * @param string[] $columns
     * @param string $type
     */
    public function __construct($spec, $table, $keys, $columns, $type)
    {
        if (empty($keys)) {
            throw new BuildException('At least one key required');
        }

        if (empty($columns)) {
            throw new BuildException('At least one column required');
        }

        $this->spec = $spec;
        $this->table = $table;
        $this->keys = $keys;
        $this->columns = $columns;
        $this->type = $type;
    }

    /**
     * @param Expr[]|array<array<Expr>> $valuesOrList
     * @return self
     */
    public function values($valuesOrList)
    {
        $valuesOrList = BuildHelper::args(func_get_args());

        if (empty($valuesOrList)) {
            throw new BuildException('At least one value required');
        }

        if (!is_array($valuesOrList[0])) {
            $valuesOrList = [$valuesOrList];
        }

        foreach ($valuesOrList as $item) {
            if (count($item) !== (count($this->keys) + count($this->columns))) {
                throw new BuildException(
                    'Values count ('
                    . count($item)
                    . ') must be the same as keys count plus columns count ('
                    . (count($this->keys) + count($this->columns))
                    . ')'
                );
            }

            $this->values[] = $item;
        }

        return $this;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        if (empty($this->values)) {
            throw new BuildException('Values required');
        }

        $quotedKeys = array_map(/** @return string */ function ($v) {
            return $this->spec->quoteIdentifier($v);
        }, $this->keys);

        $quotedColumns = array_map(/** @return string */ function ($v) {
            return $this->spec->quoteIdentifier($v);
        }, $this->columns);

        $quotedValues = array_map(/** @return string[] */ function ($items) {
            return array_map(/** @return string */ function ($v) {
                return $v->buildExpr();
            }, $items);
        }, $this->values);

        if ($this->type === self::TYPE_IGNORE) {
            return $this->spec->buildInsertIgnoreCommand($this->table->buildPart(), $quotedKeys, $quotedColumns, $quotedValues);
        } else { // self::TYPE_REPLACE
            return $this->spec->buildInsertReplaceCommand($this->table->buildPart(), $quotedKeys, $quotedColumns, $quotedValues);
        }
    }

    /**
     * @param string[] $quotedKeys
     * @param string[] $quotedColumns
     * @return string
     */
    public static function buildKeysSql($quotedKeys, $quotedColumns)
    {
        return '(' . join(', ', array_merge($quotedKeys, $quotedColumns)) . ')';
    }

    /**
     * @param array<array<string>> $quotedValues
     * @return string
     */
    public static function buildValuesSql($quotedValues)
    {
        return 'VALUES ' . join(', ', array_map(/** @return string */ function ($items) {
            return '(' . join(', ', $items) . ')';
        }, $quotedValues));
    }
}
