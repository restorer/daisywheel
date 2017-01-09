<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\ast\Expr;

class BasicExpr implements Expr
{
    const TYPE_UNARY = 'UNARY';
    const TYPE_BINARY = 'BINARY';
    const TYPE_EQ = 'EQ';
    const TYPE_LIST = 'LIST';
    const TYPE_RIGHT_HAND = 'RIGHT_HAND';
    const TYPE_BETWEEN = 'BETWEEN';

    /** @var string */
    protected $type;

    /** @var string */
    protected $operator;

    /** @var Expr[] */
    protected $operands;

    /** @var string|null */
    protected $extraSql;

    /**
     * @param $type string
     * @param $operator string
     * @param $operands Expr[]
     * @param $extraSql string|null
     */
    protected function __construct($type, $operator, $operands, $extraSql = null)
    {
        $this->type = $type;
        $this->operator = $operator;
        $this->operands = $operands;
        $this->extraSql = $extraSql;
    }

    /**
     * @implements Expr
     */
    public function build()
    {
        if ($this->type === self::TYPE_UNARY) {
            return "({$this->operator} {$this->operands[0]->build()})";
        }

        if ($this->type === self::TYPE_RIGHT_HAND) {
            return "({$this->operands[0]->build()} {$this->operator})";
        }

        if ($this->type === self::TYPE_BETWEEN) {
            return "({$this->operands[0]->build()} {$this->operator} {$this->operands[1]->build()} AND {$this->operands[2]->build()})";
        }

        if ($this->type === self::TYPE_LIST) {
            if (!is_array($this->operands[1])) {
                return "({$this->operands[0]->build()} {$this->operator} {$this->operands[1]->build()})";
            }

            if (empty($this->operands[1])) {
                return "({$this->extraSql})";
            }

            return "({$this->operands[0]->build()} {$this->operator} (" . join(', ', array_map(function ($v) {
                return $v->build();
            }, $this->operands[1])) . '))';
        }

        if ($this->type === self::TYPE_EQ
            && ($this->operands[1] instanceof ValueExpr)
            && $this->operands[1]->isNull()
        ) {
            return "({$this->operands[0]->build()} {$this->extraSql})";
        }

        return '(' . join(" {$this->operator} ", array_map(function ($v) {
            return $v->build();
        }, $this->operands)) . ')';
    }

    /**
     * @param $operator string
     * @param $operands Expr[]
     * @return BasicExpr
     */
    public static function createUnary($operator, $operands)
    {
        if (count($operands) !== 1) {
            throw new BuildException("Exactly one operand required");
        }

        return new self(self::TYPE_UNARY, $operator, $operands);
    }

    /**
     * @param $operator string
     * @param $operands Expr[]
     * @return BasicExpr
     */
    public static function createBinary($operator, $operands)
    {
        if (count($operands) !== 2) {
            throw new BuildException('Exactly two operands required');
        }

        return new self(self::TYPE_BINARY, $operator, $operands);
    }

    /**
     * @param $operator string
     * @param $operands Expr[]
     * @return BasicExpr
     */
    public static function createMulti($operator, $operands)
    {
        if (count($operands) < 2) {
            throw new BuildException('At least two operands required');
        }

        return new self(self::TYPE_BINARY, $operator, $operands);
    }

    /**
     * @param $operator string
     * @param $operands Expr[]
     * @param $nullValueSql string
     * @return BasicExpr
     */
    public static function createEq($operator, $operands, $nullValueSql)
    {
        if (count($operands) !== 2) {
            throw new BuildException('Exactly two operands required');
        }

        return new self(self::TYPE_EQ, $operator, $operands, $nullValueSql);
    }

    /**
     * @param $operator string
     * @param $operands Expr[]
     * @param $emptyListValueSql string
     * @return BasicExpr
     */
    public static function createList($operator, $operands, $emptyListValueSql)
    {
        if (count($operands) !== 2) {
            throw new BuildException('Exactly two operands required');
        }

        if (!is_array($operands[1])
            && !($operands[1] instanceof PlaceholderExpr)
            && !($operands[1] instanceof SelectCommand)
        ) {
            throw new BuildException('Right operand must be an array, PlaceholderExpr or SelectCommand');
        }

        return new self(self::TYPE_LIST, $operator, $operands, $emptyListValueSql);
    }

    /**
     * @param $operator string
     * @param $operands Expr[]
     * @return BasicExpr
     */
    public static function createRightHand($operator, $operands)
    {
        if (count($operands) !== 1) {
            throw new BuildException("Exactly one operand required");
        }

        return new self(self::TYPE_RIGHT_HAND, $operator, $operands);
    }

    /**
     * @param $operator string
     * @param $operands Expr[]
     * @return BasicExpr
     */
    public static function createBetween($operator, $operands)
    {
        if (count($operands) !== 3) {
            throw new BuildException('Exactly three operands required');
        }

        return new self(self::TYPE_BETWEEN, $operator, $operands);
    }
}
