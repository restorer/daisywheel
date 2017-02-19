<?php

namespace daisywheel\querybuilder\ast\parts;

use daisywheel\querybuilder\ast\commands\SelectCommand;
use daisywheel\querybuilder\ast\Expr;
use daisywheel\querybuilder\ast\Part;

class JoinPart implements Part
{
    const TYPE_LEFT = 'LEFT';
    const TYPE_INNER = 'INNER';
    const TYPE_RIGHT = 'RIGHT';

    /** @var SelectCommand|null */
    protected $owner;

    /** @var TableAliasPart */
    protected $tableAlias;

    /** @var string */
    protected $type;

    /** @var Expr */
    protected $onCondition;

    /**
     * @param SelectCommand $owner
     * @param TableAliasPart $tableAlias
     * @param string $type
     */
    public function __construct($owner, $tableAlias, $type)
    {
        $this->owner = $owner;
        $this->tableAlias = $tableAlias;
        $this->type = $type;
    }

    /**
     * @param Expr $expr
     *
     * @return SelectCommand
     * @psalm-suppress InvalidReturnType
     */
    public function on($expr)
    {
        $this->onCondition = $expr;

        $result = $this->owner;
        $this->owner = null;
        return $result;
    }

    /**
     * @see Part::buildPart()
     */
    public function buildPart()
    {
        return "{$this->type} JOIN {$this->tableAlias->buildPart()} ON {$this->onCondition->buildExpr()}";
    }
}
