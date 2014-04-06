<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ExpressionPart;
use daisywheel\db\builder\FieldPart;
use daisywheel\db\builder\FunctionPart;
use daisywheel\db\builder\InsertCommand;
use daisywheel\db\builder\PartWithAlias;
use daisywheel\db\builder\SelectCommand;
use daisywheel\db\builder\ValuePart;
use daisywheel\core\InvalidArgumentsException;

class BuildHelper
{
    public static function build($driver, $command)
    {
        if ($command instanceof SelectCommand) {
            return self::buildSelectCommand($driver, $command);
        } elseif ($command instanceof InsertCommand) {
            return self::buildInsertCommand($driver, $command);
        } else {
            throw new InvalidArgumentsException();
        }
    }

    public static function buildInsertCommand($driver, $command)
    {
        $result = 'INSERT INTO '
            . $driver->quoteTable($command->into)
            . self::buildColumnList($driver, $command->columns, ' (' , ')')
        ;

        if (count($command->values)) {
            $result .= ' VALUES ' . self::buildInsertValues($driver, $command->values);
        }

        return $result;
    }

    public static function buildInsertValues($driver, $values)
    {
        return join(', ', array_map(function($v) use ($driver) {
            return '(' . self::buildPartList($driver, $v, ', ', '', '') . ')';
        }, $values));
    }

    public static function buildColumnList($driver, $columns, $prepend, $append)
    {
        return $prepend . join(', ', array_map(function($v) use ($driver) {
            return $driver->quoteIdentifier($v);
        }, $columns)) . $append;
    }

    public static function buildPartList($driver, $list, $join=', ', $empty='', $prepend='')
    {
        if (!count($list)) {
            return $empty;
        }

        return $prepend . join($join, array_map(function($v) use ($driver) {
            return self::buildPart($driver, $v);
        }, $list));
    }

    protected static function buildSelectCommand($driver, $command)
    {
        if ($command->offset !== null && $command->limit === null) {
            throw new BuildException('Offset without limit is not supported');
        }

        $result = $driver->applySelectLimit(
            $command,
            'SELECT' . ($command->distinct ? ' DISTINCT' : ''),
            self::buildPartList($driver, $command->columns, ', ', ' *', ' ')
                . self::buildTableList($driver, $command->fromList, ' FROM ')
                . self::buildJoinList($driver, $command->joinList, ' ')
                . ($command->where ? ' WHERE ' . self::buildExpressionPart($driver, $command->where) : '')
                . self::buildGroupByList($driver, $command->groupByList, ' GROUP BY ')
                . ($command->having ? ' HAVING ' . self::buildExpressionPart($driver, $command->having) : '')
            ,
            self::buildSelectOrder($driver, $command)
        );

        foreach ($command->unionList as $item) {
            $result .= ' UNION ' . ($item['all'] ? 'ALL ' : '') . self::buildSelectCommand($driver, $item['command']);
        }

        return $result;
    }

    protected static function buildSelectOrder($driver, $command, $reverse=false)
    {
        if (!count($command->orderByList)) {
            return '';
        }

        return ' ORDER BY ' . join(', ', array_map(function($v) use ($driver, $reverse) {
            return self::buildFieldPart($driver, $v['field']) . ' ' . (($reverse ? !$v['asc'] : $v['asc']) ? 'ASC' : 'DESC');
        }, $command->orderByList)) . ' ';
    }

    protected static function buildGroupByList($driver, $list, $prepend)
    {
        if (!count($list)) {
            return '';
        }

        return $prepend . join(', ', array_map(function($v) use ($driver) {
            return self::buildFieldPart($driver, $v);
        }, $list));
    }

    protected static function buildJoinList($driver, $list, $prepend)
    {
        if (!count($list)) {
            return '';
        }

        return $prepend . join(' ', array_map(function($v) use ($driver) {
            return $v->type
                . ' JOIN '
                . self::buildTable($driver, $v->table)
                . ' ON '
                . self::buildExpressionPart($driver, $v->onCondition)
            ;
        }, $list));
    }

    protected static function buildTableList($driver, $list, $prepend)
    {
        if (!count($list)) {
            return '';
        }

        return $prepend . join(', ', array_map(function($v) use ($driver) {
            return self::buildTable($driver, $v);
        }, $list));
    }

    protected static function buildTable($driver, $table)
    {
        $result = $driver->quoteTable($table->name);

        if ($table->asName != '') {
            $result .= ' AS ' . $driver->quoteIdentifier($table->asName);
        }

        return $result;
    }

    protected static function buildPart($driver, $part)
    {
        if ($part instanceof ExpressionPart) {
            $result = self::buildExpressionPart($driver, $part);
        } elseif ($part instanceof FieldPart) {
            $result = self::buildFieldPart($driver, $part);
        } elseif ($part instanceof FunctionPart) {
            $result = $driver->buildFunctionPart($part);
        } elseif ($part instanceof SelectCommand) {
            $result = '(' . self::buildSelectCommand($driver, $part) . ')';
        } elseif ($part instanceof ValuePart) {
            $result = self::buildValuePart($driver, $part);
        } else {
            throw new BuildException('Unsupported builder part "' . get_class($part) . '"');
        }

        if (($part instanceof PartWithAlias) && $part->asName != '') {
            $result .= ' AS ' . $driver->quoteIdentifier($part->asName);
        }

        return $result;
    }

    protected static function buildExpressionPart($driver, $part)
    {
        $operator = $part->operator;
        $operands = $part->operands;

        if (count($operands) === 1) {
            $result = "{$operator} " . self::buildPart($driver, $operands[0]);
        } else if (count($operands) === 3) {
            if ($operator !== 'BETWEEN') {
                throw new BuildException("Unsupported ternary operator \"{$operator}\"");
            }

            $result = self::buildPart($driver, $operands[0])
                . ' BETWEEN '
                . self::buildPart($driver, $operands[1])
                . ' AND '
                . self::buildPart($driver, $operands[2])
            ;
        } elseif (($operator === 'IN' || $operator === 'NOT IN') && ($operands[1] instanceof ValuePart)) {
            $value = $operands[1]->value;

            if (!is_array($value)) {
                throw new BuildException('Right operand for '
                    . $operator
                    . ' operator must be an array (but it is "'
                    . gettype($value)
                    . '"'
                );
            }

            if (count($value)) {
                $result = self::buildPart($driver, $operands[0]) . " {$operator} (" . join(', ', array_map(function($v) use ($driver) {
                    return $driver->quote($v);
                }, $value)) . ')';
            } elseif ($operator === 'IN') {
                $result = '1 = 2';
            } else {
                $result = '1 = 1';
            }
        } else {
            $leftOperand = self::buildPart($driver, $operands[0]);
            $rightOperandIsNull = (($operands[1] instanceof ValuePart) && $operands[1]->value === null);

            if ($operator === 'IS' || $operator === '==' && $rightOperandIsNull) {
                $result = "{$leftOperand} IS NULL";
            } elseif ($operator === 'IS NOT' || $operator === '<>' && $rightOperandIsNull) {
                $result = "{$leftOperand} IS NOT NULL";
            } else {
                $result = "{$leftOperand} {$operator} " . self::buildPart($driver, $operands[1]);
            }
        }

        $result = "({$result})";

        if (count($part->relations)) {
            foreach ($part->relations as $relation) {
                $result .= ' ' . $relation['type'] . ' ' . self::buildExpressionPart($driver, $relation['expression']);
            }

            $result = "({$result})";
        }

        return $result;
    }

    protected static function buildFieldPart($driver, $part)
    {
        if ($part->fieldName === '*') {
            $result = '*';
        } else {
            $result = $driver->quoteIdentifier($part->fieldName);
        }

        if ($part->tableName != '') {
            $result = $driver->quoteTable($part->tableName) . '.' . $result;
        }

        return $result;
    }

    protected static function buildValuePart($driver, $part)
    {
        $value = $part->value;

        if ($value === null) {
            return 'NULL';
        } elseif (is_bool($value)) {
            return ($value ? 1 : 0);
        } elseif (is_scalar($value)) {
            return $driver->quote($value);
        } else {
            throw new BuildException('Unsupported value type "' . gettype($value) . '"');
        }
    }
}
