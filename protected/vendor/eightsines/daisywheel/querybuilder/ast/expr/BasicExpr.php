<?php

namespace daisywheel\querybuilder\ast\expr;

use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\BuildException;

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

    /** @var array<Expr|Expr[]|SelectCommand> */
    protected $operands;

    /** @var string|null */
    protected $extraSql;

    /**
     * @param string $type
     * @param string $operator
     * @param array<Expr|Expr[]|SelectCommand> $operands
     * @param string|null $extraSql
     */
    protected function __construct($type, $operator, $operands, $extraSql = null)
    {
        $this->type = $type;
        $this->operator = $operator;
        $this->operands = $operands;
        $this->extraSql = $extraSql;
    }

    /**
     * @see Expr::buildExpr()
     * @psalm-suppress InvalidArgument
     */
    public function buildExpr()
    {
        switch ($this->type) {
            case self::TYPE_UNARY:
                return "({$this->operator} {$this->operands[0]->buildExpr()})";

            case self::TYPE_RIGHT_HAND:
                return "({$this->operands[0]->buildExpr()} {$this->operator})";

            case self::TYPE_BETWEEN:
                return "({$this->operands[0]->buildExpr()} {$this->operator} {$this->operands[1]->buildExpr()} AND {$this->operands[2]->buildExpr()})";

            case self::TYPE_LIST: {
                if ($this->operands[1] instanceof Expr) {
                    return "({$this->operands[0]->buildExpr()} {$this->operator} {$this->operands[1]->buildExpr()})";
                } elseif (empty($this->operands[1])) {
                    return "({$this->extraSql})";
                }

                return "({$this->operands[0]->buildExpr()} {$this->operator} (" . implode(
                    ', ',
                    array_map(
                        /** @return string */
                        function ($v) {
                            /** @var Expr $v */
                            return $v->buildExpr();
                        },
                        $this->operands[1]
                    )
                ) . '))';
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if ($this->type === self::TYPE_EQ
            && ($this->operands[1] instanceof ValueExpr)
            && $this->operands[1]->isNull()
        ) {
            return "({$this->operands[0]->buildExpr()} {$this->extraSql})";
        }

        return '(' . implode(
            " {$this->operator} ",
            array_map(
                /** @return string */
                function ($v) {
                    /** @var Expr $v */
                    return $v->buildExpr();
                },
                $this->operands
            )
        ) . ')';
    }

    /**
     * @param string $operator
     * @param Expr[] $operands
     *
     * @throws BuildException
     * @return BasicExpr
     */
    public static function createUnary($operator, $operands)
    {
        if (count($operands) !== 1) {
            throw new BuildException('Exactly one operand required');
        }

        return new self(self::TYPE_UNARY, $operator, $operands);
    }

    /**
     * @param string $operator
     * @param Expr[] $operands
     *
     * @throws BuildException
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
     * @param string $operator
     * @param Expr[] $operands
     *
     * @throws BuildException
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
     * @param string $operator
     * @param Expr[] $operands
     * @param string $nullValueSql
     *
     * @throws BuildException
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
     * @param string $operator
     * @param array<Expr|Expr[]|SelectCommand> $operands
     * @param string $emptyListValueSql
     *
     * @throws BuildException
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
     * @param string $operator
     * @param Expr[] $operands
     *
     * @throws BuildException
     * @return BasicExpr
     */
    public static function createRightHand($operator, $operands)
    {
        if (count($operands) !== 1) {
            throw new BuildException('Exactly one operand required');
        }

        return new self(self::TYPE_RIGHT_HAND, $operator, $operands);
    }

    /**
     * @param string $operator
     * @param Expr[] $operands
     *
     * @throws BuildException
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
