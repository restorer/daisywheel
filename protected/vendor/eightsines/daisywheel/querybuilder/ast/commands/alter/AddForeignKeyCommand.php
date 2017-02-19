<?php

namespace daisywheel\querybuilder\ast\commands\alter;

use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\ForeignKeyConstraintPart;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\BuildSpec;

class AddForeignKeyCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var ForeignKeyConstraintPart */
    protected $foreignKeyPart;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     */
    public function __construct($spec, $table)
    {
        $this->spec = $spec;
        $this->table = $table;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return $this->spec->buildAlterTableAddForeignKeyCommand($this->table, $this->foreignKeyPart);
    }

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $name
     * @param string[] $columns
     * @param TablePart $refTable
     * @param string[] $refColumns
     *
     * @return ForeignKeyConstraintPart
     * @throws \daisywheel\querybuilder\BuildException
     */
    public static function createPart($spec, $table, $name, $columns, $refTable, $refColumns)
    {
        $command = new self($spec, $table);

        $command->foreignKeyPart = new ForeignKeyConstraintPart(
            $command,
            $spec,
            $table,
            $name,
            $columns,
            $refTable,
            $refColumns
        );

        return $command->foreignKeyPart;
    }

    /**
     * @param TablePart $table
     * @param ForeignKeyConstraintPart $foreignKeyPart
     *
     * @return string[]
     */
    public static function basicBuild($table, $foreignKeyPart)
    {
        return ["ALTER TABLE {$table->buildPart()} ADD {$foreignKeyPart->buildPart()}"];
    }
}
