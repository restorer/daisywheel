<?php

namespace daisywheel\db\builder;

use daisywheel\core\InvalidArgumentsException;

class ColumnPart extends PartWithAlias
{
    const TYPE_PRIMARYKEY = 'PRIMARYKEY';
    const TYPE_BIGPRIMARYKEY = 'BIGPRIMARYKEY';
    const TYPE_TYNYINT = 'TINYINT';
    const TYPE_SMALLINT = 'SMALLINT';
    const TYPE_INT = 'INT';
    const TYPE_BIGINT = 'BIGINT';
    const TYPE_DECIMAL = 'DECIMAL';
    const TYPE_FLOAT = 'FLOAT';
    const TYPE_DOUBLE = 'DOUBLE';
    const TYPE_DATE = 'DATE';
    const TYPE_TIME = 'TIME';
    const TYPE_DATETIME = 'DATETIME';
    const TYPE_CHAR = 'CHAR'; // 255
    const TYPE_VARCHAR = 'VARCHAR'; // 255
    const TYPE_TEXT = 'TEXT'; // 2^16
    const TYPE_MEDIUMTEXT = 'MEDIUMTEXT'; // 2^24
    const TYPE_LONGTEXT = 'LONGTEXT'; // 2^32
    const TYPE_BLOB = 'BLOB'; // 2^16
    const TYPE_MEDIUMBLOB = 'MEDIUMBLOB'; // 2^24
    const TYPE_LONGBLOB = 'LONGBLOB'; // 2^32

    protected static $supportedTypes = array(
        self::TYPE_PRIMARYKEY => true,
        self::TYPE_BIGPRIMARYKEY => true,
        self::TYPE_TYNYINT => true,
        self::TYPE_SMALLINT => true,
        self::TYPE_INT => true,
        self::TYPE_BIGINT => true,
        self::TYPE_DECIMAL => true,
        self::TYPE_FLOAT => true,
        self::TYPE_DOUBLE => true,
        self::TYPE_DATE => true,
        self::TYPE_TIME => true,
        self::TYPE_DATETIME => true,
        self::TYPE_CHAR => true,
        self::TYPE_VARCHAR => true,
        self::TYPE_TEXT => true,
        self::TYPE_MEDIUMTEXT => true,
        self::TYPE_LONGTEXT => true,
        self::TYPE_BLOB => true,
        self::TYPE_MEDIUMBLOB => true,
        self::TYPE_LONGBLOB => true,
    );

    protected $table = null;
    protected $columnName = '';
    protected $columnType = null;
    protected $columnOptions = array();
    protected $notNull = false;
    protected $default = null;

    protected function __construct($arguments)
    {
        if (count($arguments) === 2) {
            $this->table = Table::create($arguments[0]);
            $this->columnName = $arguments[1];
        } elseif (count($arguments) === 1) {
            $this->columnName = $arguments[0];
        } else {
            throw new InvalidArgumentsException();
        }
    }

    public function notNull($notNull=true)
    {
        $this->notNull = $notNull;
        return $this;
    }

    protected function magicDefault($value)
    {
        $this->default = ValuePart::create(array($value));
        return $this;
    }

    protected function getTable()
    {
        return $this->table;
    }

    protected function getColumnName()
    {
        return $this->columnName;
    }

    protected function getColumnType()
    {
        return $this->columnType;
    }

    protected function getColumnOptions()
    {
        return $this->columnOptions;
    }

    protected function getNotNull()
    {
        return $this->notNull;
    }

    protected function getDefault()
    {
        return $this->default;
    }

    public function __call($name, $arguments)
    {
        $columnType = mb_strtoupper($name);

        if (isset(self::$supportedTypes[$columnType])) {
            $this->columnType = $columnType;
            $this->columnOptions = $arguments;
            return $this;
        }

        return parent::__call($name, $arguments);
    }

    public static function create($arguments)
    {
        if (count($arguments) === 1 && ($arguments[0] instanceof ColumnPart)) {
            return $arguments[0];
        } else {
            return new self($arguments);
        }
    }
}
