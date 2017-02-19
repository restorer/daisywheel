<?php

namespace daisywheel\querybuilder\ast\commands\alter;

use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\BuildSpec;

class RenameToCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string */
    protected $newName;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $newName
     */
    public function __construct($spec, $table, $newName)
    {
        $this->spec = $spec;
        $this->table = $table;
        $this->newName = $newName;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return $this->spec->buildAlterTableRenameToCommand($this->table, $this->newName);
    }

    /**
     * @param TablePart $table
     * @param string $quotedNewName
     *
     * @return string[]
     */
    public static function basicBuild($table, $quotedNewName)
    {
        return ["ALTER TABLE {$table->buildPart()} RENAME TO {$quotedNewName}"];
    }
}
