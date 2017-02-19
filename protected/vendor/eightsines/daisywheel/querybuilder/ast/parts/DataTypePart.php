<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\Part;
use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildSpec;

/**
 * @method DataTypePart default(mixed $defaultValue)
 */
class DataTypePart implements Part
{
    const TYPE_PRIMARY_KEY = 'PRIMARY_KEY';
    const TYPE_BIG_PRIMARY_KEY = 'BIG_PRIMARY_KEY';
    const TYPE_TINY_INT = 'TINY_INT';
    const TYPE_SMALL_INT = 'SMALL_INT';
    const TYPE_INT = 'INT';
    const TYPE_BIG_INT = 'BIG_INT';
    const TYPE_DECIMAL = 'DECIMAL';
    const TYPE_FLOAT = 'FLOAT';
    const TYPE_DOUBLE = 'DOUBLE';
    const TYPE_DATE = 'DATE';
    const TYPE_TIME = 'TIME';
    const TYPE_DATE_TIME = 'DATE_TIME';
    const TYPE_CHAR = 'CHAR';
    const TYPE_VAR_CHAR = 'VAR_CHAR';
    const TYPE_TEXT = 'TEXT';
    const TYPE_MEDIUM_TEXT = 'MEDIUM_TEXT';
    const TYPE_LONG_TEXT = 'LONG_TEXT';
    const TYPE_BLOB = 'BLOB';
    const TYPE_MEDIUM_BLOB = 'MEDIUM_BLOB';
    const TYPE_LONG_BLOB = 'LONG_BLOB';

    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var string */
    protected $type;

    /** @var int[] */
    protected $options = [];

    /** @var boolean */
    protected $isNotNull = false;

    /** @var mixed|null */
    protected $defaultValue;

    /**
     * @param BuildSpec $spec
     * @param string $name
     * @param string $type
     * @param mixed[] $options
     */
    public function __construct($spec, $name, $type, array $options = [])
    {
        $this->spec = $spec;
        $this->name = $name;
        $this->type = $type;

        $this->options = array_map(
            /** @return int */
            function ($v) {
                return (int)$v;
            },
            $options
        );
    }

    /**
     * @param boolean $isNotNull
     *
     * @return self
     */
    public function notNull($isNotNull = true)
    {
        $this->isNotNull = $isNotNull;
        return $this;
    }

    /**
     * @param mixed $defaultValue
     *
     * @return self
     */
    public function default_($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return $this->spec->buildDataTypePart(
            $this->spec->quoteIdentifier($this->name),
            $this->type,
            $this->options,
            $this->isNotNull,
            ($this->defaultValue === null ? null : $this->spec->quote($this->defaultValue))
        );
    }

    /**
     * @param string $name
     * @param mixed $arguments
     *
     * @throws BuildException
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, "{$name}_")) {
            return call_user_func_array([$this, "{$name}_"], $arguments);
        }

        throw new BuildException('Calling unknown method ' . get_class($this) . "::{$name}");
    }

    /**
     * @param string $quotedName
     * @param string $type
     * @param int[] $options
     * @param boolean $isNotNull
     * @param string|null $quotedDefaultValue
     *
     * @return string
     */
    public static function basicBuild($quotedName, $type, $options, $isNotNull, $quotedDefaultValue)
    {
        return "{$quotedName} {$type}"
            . (empty($options) ? '' : ('(' . implode(', ', $options) . ')'))
            . ($isNotNull ? ' NOT NULL' : '')
            . ($quotedDefaultValue === null ? '' : " DEFAULT {$quotedDefaultValue}");
    }
}
