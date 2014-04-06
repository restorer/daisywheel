<?php

namespace daisywheel\db\builder;

use daisywheel\core\UnknownMethodException;

class Builder
{
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

    public function f()
    {
        return FieldPart::create(func_get_args());
    }

    public function v()
    {
        return ValuePart::create(func_get_args());
    }

    public function e()
    {
        return ExpressionPart::create(func_get_args());
    }

    public function not()
    {
        return ExpressionPart::create(array('NOT', ExpressionPart::create(func_get_args())));
    }

    public function select()
    {
        return new SelectCommand($this->driver);
    }

    public function insert()
    {
        return new InsertCommand($this->driver);
    }

    public function __call($name, $arguments)
    {
        $funcName = mb_strtoupper($name);

        if (isset(self::$supportedFunctions[$funcName])) {
            return new FunctionPart($funcName, $arguments);
        }

        throw new UnknownMethodException('Calling unknown method ' . get_class($this) . "::{$name}");
    }
}
