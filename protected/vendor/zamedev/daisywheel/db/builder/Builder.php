<?php

namespace daisywheel\db\builder;

use daisywheel\core\UnknownMethodException;

class Builder
{
    protected static $supportedOperators = array(
        ExpressionPart::OPERATOR_EQ => true,
        ExpressionPart::OPERATOR_NEQ => true,
        ExpressionPart::OPERATOR_GT => true,
        ExpressionPart::OPERATOR_GTE => true,
        ExpressionPart::OPERATOR_LT => true,
        ExpressionPart::OPERATOR_LTE => true,
        ExpressionPart::OPERATOR_IN => true,
        ExpressionPart::OPERATOR_NOTIN => true,
        ExpressionPart::OPERATOR_IS => true,
        ExpressionPart::OPERATOR_ISNOT => true,
        ExpressionPart::OPERATOR_ADD => true,
        ExpressionPart::OPERATOR_SUB => true,
        ExpressionPart::OPERATOR_MUL => true,
        ExpressionPart::OPERATOR_DIV => true,
        ExpressionPart::OPERATOR_NEG => true,
        ExpressionPart::OPERATOR_NOT => true,
        ExpressionPart::OPERATOR_BETWEEN => true,
    );

    protected static $supportedFunctions = array(
        FunctionPart::TYPE_AVG => true,
        FunctionPart::TYPE_COUNT => true,
        FunctionPart::TYPE_MAX => true,
        FunctionPart::TYPE_MIN => true,
        FunctionPart::TYPE_SUM => true,
        FunctionPart::TYPE_COALESCE => true,
        FunctionPart::TYPE_ABS => true,
        FunctionPart::TYPE_ROUND => true,
        FunctionPart::TYPE_CONCAT => true,
        FunctionPart::TYPE_LENGTH => true,
        FunctionPart::TYPE_LOWER => true,
        FunctionPart::TYPE_LTRIM => true,
        FunctionPart::TYPE_RTRIM => true,
        FunctionPart::TYPE_SUBSTR => true,
        FunctionPart::TYPE_TRIM => true,
        FunctionPart::TYPE_UPPER => true,
    );

    protected $driver = null;

    public function __construct($driver)
    {
        $this->driver = $driver;
    }

    public function c()
    {
        return ColumnPart::create(func_get_args());
    }

    public function v()
    {
        return ValuePart::create(func_get_args());
    }

    public function temp($name)
    {
        return Table::createTemporary($name);
    }

    public function select()
    {
        return new SelectCommand($this->driver);
    }

    public function insert()
    {
        return new InsertCommand($this->driver);
    }

    public function delete()
    {
        return new DeleteCommand($this->driver);
    }

    public function update()
    {
        return new UpdateCommand($this->driver);
    }

    public function createTable($name)
    {
        return new CreateTableCommand($this->driver, $name);
    }

    public function createIndex($name)
    {
        return new CreateIndexCommand($this->driver, $name);
    }

    public function dropTable($name)
    {
        return new DropTableCommand($this->driver, $name);
    }

    public function dropIndex($name)
    {
        return new DropIndexCommand($this->driver, $name);
    }

    public function truncateTable($name)
    {
        return new TruncateTableCommand($this->driver, $name);
    }

    public function __call($name, $arguments)
    {
        $upperName = mb_strtoupper($name);

        if (isset(self::$supportedOperators[$upperName])) {
            return new ExpressionPart($upperName, $arguments);
        }

        if (isset(self::$supportedFunctions[$upperName])) {
            return new FunctionPart($upperName, $arguments);
        }

        throw new UnknownMethodException('Calling unknown method ' . get_class($this) . "::{$name}");
    }
}
