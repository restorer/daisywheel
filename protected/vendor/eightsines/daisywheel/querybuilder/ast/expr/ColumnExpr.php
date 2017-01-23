<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\parts\DataTypePart;
use daisywheel\querybuilder\ast\parts\TablePart;

class ColumnExpr implements Expr
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var TablePart|null */
    protected $table;

    /**
     * @param BuildSpec $spec
     * @param string $name
     * @param TablePart|null $table
     */
    public function __construct($spec, $name, $table = null)
    {
        $this->spec = $spec;
        $this->name = $name;
        $this->table = $table;
    }

    /**
     * @return DataTypePart
     */
    public function primaryKey()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_PRIMARY_KEY);
    }

    /**
     * @return DataTypePart
     */
    public function bigPrimaryKey()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_BIG_PRIMARY_KEY);
    }

    /**
     * @return DataTypePart
     */
    public function tinyInt()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_TINY_INT);
    }

    /**
     * @return DataTypePart
     */
    public function smallInt()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_SMALL_INT);
    }

    /**
     * @return DataTypePart
     */
    public function int()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_INT);
    }

    /**
     * @return DataTypePart
     */
    public function bigInt()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_BIG_INT);
    }

    /**
     * @param int $length
     * @param int $decimals
     * @return DataTypePart
     */
    public function decimal($length, $decimals)
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_DECIMAL, [$length, $decimals]);
    }

    /**
     * @return DataTypePart
     */
    public function float()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_FLOAT);
    }

    /**
     * @return DataTypePart
     */
    public function double()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_DOUBLE);
    }

    /**
     * @return DataTypePart
     */
    public function date()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_DATE);
    }

    /**
     * @return DataTypePart
     */
    public function time()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_TIME);
    }

    /**
     * @return DataTypePart
     */
    public function dateTime()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_DATE_TIME);
    }

    /**
     * @param int $length
     * @return DataTypePart
     * {@internal Up to 255}
     */
    public function char($length)
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_CHAR, [$length]);
    }

    /**
     * @param int $length
     * @return DataTypePart
     * {@internal Up to 255}
     */
    public function varChar($length)
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_VAR_CHAR, [$length]);
    }

    /**
     * @return DataTypePart
     * {@internal Up to 2^16}
     */
    public function text()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_TEXT);
    }

    /**
     * @return DataTypePart
     * {@internal Up to 2^24}
     */
    public function mediumText()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_MEDIUM_TEXT);
    }

    /**
     * @return DataTypePart
     * {@internal Up to 2^32}
     */
    public function longText()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_LONG_TEXT);
    }

    /**
     * @return DataTypePart
     * {@internal Up to 2^16}
     */
    public function blob()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_BLOB);
    }

    /**
     * @return DataTypePart
     * {@internal Up to 2^24}
     */
    public function mediumBlob()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_MEDIUM_BLOB);
    }

    /**
     * @return DataTypePart
     * {@internal Up to 2^32}
     */
    public function longBlob()
    {
        return new DataTypePart($this->spec, $this->name, DataTypePart::TYPE_LONG_BLOB);
    }

    /**
     * @see Expr::buildExpr()
     */
    public function buildExpr()
    {
        return ($this->table === null ? '' : ($this->table->buildPart() . '.'))
            . ($this->name === '*' ? '*' : $this->spec->quoteIdentifier($this->name));
    }
}
