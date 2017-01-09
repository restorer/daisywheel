<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class ExpressionPart extends PartWithAlias
{
    const OPERATOR_EQ = 'EQ';
    const OPERATOR_NEQ = 'NEQ';
    const OPERATOR_GT = 'GT';
    const OPERATOR_GTE = 'GTE';
    const OPERATOR_LT = 'LT';
    const OPERATOR_LTE = 'LTE';
    const OPERATOR_IN = 'IN';
    const OPERATOR_NOTIN = 'NOTIN';
    const OPERATOR_IS = 'IS';
    const OPERATOR_ISNOT = 'ISNOT';
    const OPERATOR_ADD = 'ADD';
    const OPERATOR_SUB = 'SUB';
    const OPERATOR_MUL = 'MUL';
    const OPERATOR_DIV = 'DIV';
    const OPERATOR_NEG = 'NEG';
    const OPERATOR_NOT = 'NOT';
    const OPERATOR_BETWEEN = 'BETWEEN';

    const RELATION_AND = 'AND';
    const RELATION_OR = 'OR';

    protected static $operatorOperandsCountMap = [
        self::OPERATOR_EQ => 2,
        self::OPERATOR_NEQ => 2,
        self::OPERATOR_GT => 2,
        self::OPERATOR_GTE => 2,
        self::OPERATOR_LT => 2,
        self::OPERATOR_LTE => 2,
        self::OPERATOR_IN => 2,
        self::OPERATOR_NOTIN => 2,
        self::OPERATOR_IS => 2,
        self::OPERATOR_ISNOT => 2,
        self::OPERATOR_ADD => 2,
        self::OPERATOR_SUB => 2,
        self::OPERATOR_MUL => 2,
        self::OPERATOR_DIV => 2,
        self::OPERATOR_NEG => 1,
        self::OPERATOR_NOT => 1,
        self::OPERATOR_BETWEEN => 3,
    ];

    protected static $operatorSqlOperatorMap = [
        self::OPERATOR_EQ => '=',
        self::OPERATOR_NEQ => '<>',
        self::OPERATOR_GT => '>',
        self::OPERATOR_GTE => '>=',
        self::OPERATOR_LT => '<',
        self::OPERATOR_LTE => '<=',
        self::OPERATOR_IN => 'IN',
        self::OPERATOR_NOTIN => 'NOT IN',
        self::OPERATOR_IS => 'IS',
        self::OPERATOR_ISNOT => 'IS NOT',
        self::OPERATOR_ADD => '+',
        self::OPERATOR_SUB => '-',
        self::OPERATOR_MUL => '*',
        self::OPERATOR_DIV => '/',
        self::OPERATOR_NEG => '-',
        self::OPERATOR_NOT => 'NOT',
        self::OPERATOR_BETWEEN => 'BETWEEN',
    ];

    protected $operator = null;
    protected $operands = [];
    protected $relations = [];

    public function __construct($operator, $operands)
    {
        $this->operator = mb_strtoupper($operator);

        if (!isset(self::$operatorOperandsCountMap[$this->operator])
            || self::$operatorOperandsCountMap[$this->operator] != count($operands)
        ) {
            throw new InvalidArgumentsException();
        }

        $this->operands = array_map(function($v) {
            return ValuePart::create([$v]);
        }, $operands);
    }

    protected function magicAnd($expression)
    {
        $this->relations[] = [
            'type' => self::RELATION_AND,
            'expression' => $expression,
        ];

        return $this;
    }

    protected function magicOr($expression)
    {
        $this->relations[] = [
            'type' => self::RELATION_OR,
            'expression' => $expression,
        ];

        return $this;
    }

    protected function getOperator()
    {
        return $this->operator;
    }

    protected function getSqlOperator()
    {
        return self::$operatorSqlOperatorMap[$this->operator];
    }

    protected function getOperands()
    {
        return $this->operands;
    }

    protected function getRelations()
    {
        return $this->relations;
    }
}
