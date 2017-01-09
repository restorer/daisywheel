<?php

namespace daisywheel\db;

use daisywheel\core\Component;
use daisywheel\db\builder\Builder;

class Connection extends Component
{
    protected static $driverMap = [
        'mysql' => 'daisywheel\\db\\drivers\\MySqlDriver',
        'sqlite' => 'daisywheel\\db\\drivers\\SqliteDriver',
        'pgsql' => 'daisywheel\\db\\drivers\\PgSqlDriver',
        'sqlsrv' => 'daisywheel\\db\\drivers\\MsSqlDriver',
        'dblib' => 'daisywheel\\db\\drivers\\MsSqlDriver',
    ];

    protected $driver = null;
    protected $prefix = '';

    public function init($config)
    {
        $config->defaults([
            'charset' => 'utf8',
            'driverOptions' => [],
            'prefix' => '',
        ]);

        $this->prefix = $config->get('prefix');
        $driverName = $this->extractDriverName($config->get('dsn'));

        if (!isset(self::$driverMap[$driverName])) {
            throw new InvalidDriverException("Driver \"{$driverName}\" for dsn \"" . $config->get('dsn') . '" is not supported');
        }

        $driverClass = self::$driverMap[$driverName];
        $this->driver = new $driverClass($this);

        $driverOptions = $config->get('driverOptions');
        $driverOptions[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;

        $this->driver->connect(
            $config->get('dsn'),
            $config->get('username'),
            $config->get('password'),
            $driverOptions,
            $config->get('charset')
        );
    }

    public function builder($callback=null)
    {
        $builder = new Builder($this->driver);

        if ($callback) {
            return call_user_func($callback, $builder);
        }

        return $builder;
    }

    public function quote($value)
    {
        return $this->driver->quote($value);
    }

    public function quoteTable($name)
    {
        return $this->driver->quoteTable($name);
    }

    public function quoteColumn($name)
    {
        return $this->driver->quoteIdentifier($name);
    }

    public function queryAllRaw($sql, $params=[])
    {
        return $this->driver->queryAll($this->prepare($sql), $params);
    }

    public function queryRowRaw($sql, $params=[])
    {
        return $this->driver->queryRow($this->prepare($sql), $params);
    }

    public function queryColumnRaw($sql, $params=[])
    {
        return $this->driver->queryColumn($this->prepare($sql), $params);
    }

    public function executeRaw($sql, $params=[])
    {
        return $this->driver->execute($this->prepare($sql), $params);
    }

    public function insertRaw($sql, $params=[])
    {
        return $this->driver->insert($this->prepare($sql), $params);
    }

    protected function getPrefix()
    {
        return $this->prefix;
    }

    protected function prepare($sql)
    {
        $self = $this;

        $sql = preg_replace_callback('/\{\{([^}]+)\}\}/', function($mt) use ($self) {
            return $self->quoteTable($mt[1]);
        }, $sql);

        $sql = preg_replace_callback('/\[\[([^}]+)\]\]/', function($mt) use ($self) {
            return $self->quoteColumn($mt[1]);
        }, $sql);

        return $sql;
    }

    protected function extractDriverName($dsn)
    {
        if (preg_match('/^([a-zA-Z]+):/', $dsn, $mt)) {
            return $mt[1];
        }

        throw new InvalidDriverException("Can't determine driver name for dsn \"{$dsn}\"");
    }
}
