<?php

namespace daisywheel\querybuilder\ast\commands\alter;

use daisywheel\querybuilder\ast\Command;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\BuildSpec;

/*

SQL Server:

EXEC sp_rename 'table_name.old_column_name', 'new_column_name' , 'OBJECT';

*/

class RenameForeignKeyCommand implements Command
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

    /** @var string */
    protected $oldName;

    /** @var string */
    protected $newName;

    /**
     * @param BuildSpec $spec
     * @param TablePart $table
     * @param string $oldName
     * @param string $newName
     */
    public function __construct($spec, $table, $oldName, $newName)
    {
        $this->spec = $spec;
        $this->table = $table;
        $this->oldName = $oldName;
        $this->newName = $newName;
    }

    /**
     * @see Command::build()
     */
    public function build()
    {
        return [];
    }
}
