<?php

namespace daisywheel\querybuilder\ast\commands;

use daisywheel\querybuilder\BuildException;
use daisywheel\querybuilder\BuildSpec;
use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\Table;

class DropIndexCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var string */
    protected $name;

    /** @var Table|null */
    protected $table = null;

    /**
     * @param $spec BuildSpec
     * @param $name string
     */
    public function __construct($spec, $name)
    {
        $this->spec = $spec;
        $this->name = $name;
    }

    public function on($nameOrTable)
    {
        $this->table = Table::create($this->spec, $nameOrTable);
        return $this;
    }

    /**
     * @implements Expr
     */
    public function build()
    {
        if ($this->table === null) {
            throw new BuildException("Can't build drop index without table (name = \"{$this->name}\")");
        }

        return $this->spec->buildDropIndexCommand(
            $this->spec->quoteConstraint($this->table->getName(), $this->name),
            $this->table->getName(),
            $this->table->getTemporary()
        );
    }

    /**
     * @param $constraintSql string
     * @param $appendSql string
     * @return string
     */
    public static function basicBuild($constraintSql, $appendSql)
    {
        return ["DROP INDEX {$constraintSql}{$appendSql}"];
    }
}
