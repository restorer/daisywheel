<?php

namespace daisywheel\querybuilder\ast;

use daisywheel\querybuilder\ast\commands\alter\AddColumnCommand;
use daisywheel\querybuilder\ast\commands\alter\AddForeignKeyCommand;
use daisywheel\querybuilder\ast\commands\alter\AddIndexCommand;
use daisywheel\querybuilder\ast\commands\alter\AlterColumnCommand;
use daisywheel\querybuilder\ast\commands\alter\DropColumnCommand;
use daisywheel\querybuilder\ast\commands\alter\DropForeignKeyCommand;
use daisywheel\querybuilder\ast\commands\alter\DropIndexCommand;
use daisywheel\querybuilder\ast\commands\alter\RenameColumnCommand;
use daisywheel\querybuilder\ast\commands\alter\RenameForeignKeyCommand;
use daisywheel\querybuilder\ast\commands\alter\RenameIndexCommand;
use daisywheel\querybuilder\ast\commands\alter\RenameToCommand;
use daisywheel\querybuilder\ast\parts\DataTypePart;
use daisywheel\querybuilder\ast\parts\ForeignKeyConstraintPart;
use daisywheel\querybuilder\ast\parts\TablePart;
use daisywheel\querybuilder\BuildHelper;
use daisywheel\querybuilder\BuildSpec;

class AlterTableSelector
{
    /** @var BuildSpec */
    protected $spec;

    /** @var TablePart */
    protected $table;

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
     * @param string $newName
     *
     * @return RenameToCommand
     */
    public function renameTo($newName)
    {
        return new RenameToCommand($this->spec, $this->table, $newName);
    }

    /**
     * @param DataTypePart $column
     *
     * @return AddColumnCommand
     */
    public function addColumn($column)
    {
        return new AddColumnCommand($this->spec, $this->table, $column);
    }

    /**
     * @param string $name
     * @param string|string[] $columns
     *
     * @return AddIndexCommand
     * @throws \daisywheel\querybuilder\BuildException
     * @psalm-suppress TypeCoercion
     */
    public function addIndex($name, $columns)
    {
        /** @noinspection PhpParamsInspection */
        return new AddIndexCommand($this->spec, $this->table, $name, BuildHelper::arg($columns), false);
    }

    /**
     * @param string $name
     * @param string|string[] $columns
     *
     * @return AddIndexCommand
     * @throws \daisywheel\querybuilder\BuildException
     * @psalm-suppress TypeCoercion
     */
    public function addUniqueIndex($name, $columns)
    {
        /** @noinspection PhpParamsInspection */
        return new AddIndexCommand($this->spec, $this->table, $name, BuildHelper::arg($columns), true);
    }

    /**
     * @param string $name
     * @param string|string[] $columns
     * @param string|TablePart $refTable
     * @param string|string[] $refColumns
     *
     * @return ForeignKeyConstraintPart
     * @throws \daisywheel\querybuilder\BuildException
     * @psalm-suppress TypeCoercion
     */
    public function addForeignKey($name, $columns, $refTable, $refColumns)
    {
        /** @noinspection PhpParamsInspection */
        return AddForeignKeyCommand::createPart(
            $this->spec,
            $this->table,
            $name,
            BuildHelper::arg($columns),
            TablePart::create($this->spec, $refTable),
            BuildHelper::arg($refColumns)
        );
    }

    /**
     * @param DataTypePart $column
     *
     * @return AlterColumnCommand
     */
    public function alterColumn($column)
    {
        return new AlterColumnCommand($this->spec, $this->table, $column);
    }

    /**
     * @param string $name
     *
     * @return DropColumnCommand
     */
    public function dropColumn($name)
    {
        return new DropColumnCommand($this->spec, $this->table, $name);
    }

    /**
     * @param string $name
     *
     * @return DropIndexCommand
     */
    public function dropIndex($name)
    {
        return new DropIndexCommand($this->spec, $this->table, $name);
    }

    /**
     * @param string $name
     *
     * @return DropIndexCommand
     */
    public function dropUniqueIndex($name)
    {
        return new DropIndexCommand($this->spec, $this->table, $name);
    }

    /**
     * @param string $name
     *
     * @return DropForeignKeyCommand
     */
    public function dropForeignKey($name)
    {
        return new DropForeignKeyCommand($this->spec, $this->table, $name);
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @return RenameColumnCommand
     */
    public function renameColumn($oldName, $newName)
    {
        return new RenameColumnCommand($this->spec, $this->table, $oldName, $newName);
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @return RenameIndexCommand
     */
    public function renameIndex($oldName, $newName)
    {
        return new RenameIndexCommand($this->spec, $this->table, $oldName, $newName);
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @return RenameIndexCommand
     */
    public function renameUniqueIndex($oldName, $newName)
    {
        return new RenameIndexCommand($this->spec, $this->table, $oldName, $newName);
    }

    /**
     * @param string $oldName
     * @param string $newName
     *
     * @return RenameForeignKeyCommand
     */
    public function renameForeignKeyIndex($oldName, $newName)
    {
        return new RenameForeignKeyCommand($this->spec, $this->table, $oldName, $newName);
    }
}
