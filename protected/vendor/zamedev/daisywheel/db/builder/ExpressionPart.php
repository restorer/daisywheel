<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class ExpressionPart extends PartWithAlias
{
    const RELATION_AND = 'AND';
    const RELATION_OR = 'OR';

    protected $operator = null;
    protected $operands = array();
    protected $relations = array();

    protected function __construct($arguments)
    {
        if (count($arguments) === 4) {
            $this->operator = mb_strtoupper($arguments[1]);

            $this->operands = array(
                ValuePart::create(array($arguments[0])),
                ValuePart::create(array($arguments[2])),
                ValuePart::create(array($arguments[3])),
            );
        } elseif (count($arguments) === 3) {
            $this->operator = mb_strtoupper($arguments[1]);

            $this->operands = array(
                ValuePart::create(array($arguments[0])),
                ValuePart::create(array($arguments[2])),
            );
        } elseif (count($arguments) === 2) {
            $this->operator = mb_strtoupper($arguments[0]);

            $this->operands = array(
                ValuePart::create(array($arguments[1])),
            );
        } else {
            throw new InvalidArgumentsException();
        }

        if (!is_string($this->operator)) {
            throw new InvalidArgumentsException();
        }
    }

    protected function magicAnd()
    {
        $this->relations[] = array(
            'type' => self::RELATION_AND,
            'expression' => self::create(func_get_args()),
        );

        return $this;
    }

    protected function magicOr()
    {
        $this->relations[] = array(
            'type' => self::RELATION_OR,
            'expression' => self::create(func_get_args()),
        );

        return $this;
    }

    protected function getOperator()
    {
        return $this->operator;
    }

    protected function getOperands()
    {
        return $this->operands;
    }

    protected function getRelations()
    {
        return $this->relations;
    }

    public function __call($name, $arguments)
    {
        $method = "magic{$name}";

        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }

        throw new UnknownMethodException('Calling unknown method ' . get_class($this) . "::{$method}");
    }

    public static function create($arguments)
    {
        if (count($arguments) === 1 && ($arguments[0] instanceof ExpressionPart)) {
            return $arguments[0];
        } else {
            return new self($arguments);
        }
    }
}
