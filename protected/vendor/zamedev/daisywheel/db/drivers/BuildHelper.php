<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\ColumnPart;
use daisywheel\db\builder\CreateIndexCommand;
use daisywheel\db\builder\CreateTableCommand;
use daisywheel\db\builder\DeleteCommand;
use daisywheel\db\builder\ExpressionPart;
use daisywheel\db\builder\FunctionPart;
use daisywheel\db\builder\InsertCommand;
use daisywheel\db\builder\PartWithAlias;
use daisywheel\db\builder\SelectCommand;
use daisywheel\db\builder\UpdateCommand;
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
        } elseif ($command instanceof DeleteCommand) {
            return self::buildDeleteCommand($driver, $command);
        } elseif ($command instanceof UpdateCommand) {
            return self::buildUpdateCommand($driver, $command);
        } elseif ($command instanceof CreateTableCommand) {
            return self::buildCreateTableCommand($driver, $command);
        } elseif ($command instanceof CreateIndexCommand) {
            return self::buildCreateIndexCommand($driver, $command);
        } else {
            throw new InvalidArgumentsException();
        }
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

    protected static function buildCreateIndexCommand($driver, $command)
    {
        return self::buildCreateIndexPart($driver, $command->indexName, $command->tableName, $command->columnNames);
    }

    protected static function buildCreateTableCommand($driver, $command)
    {
        $list = self::buildCreateTableColumns($driver, $command, array());
        $list = self::buildCreateTableConstraints($driver, $command, $list);

        $sql = 'CREATE '
            . $driver->getCreateTableStartPart($command)
            . ' (' . join(', ', $list) . ')';

        $list = self::buildCreateTableIndices($driver, $command, array($sql));
        return (count($list) == 1 ? $list[0] : $list);
    }

    protected static function buildCreateTableIndices($driver, $command, $list)
    {
        foreach ($command->indexList as $item) {
            $list[] = self::buildCreateIndexPart($item['name'], $command->tableName, $item['columns']);
        }

        return $list;
    }

    protected static function buildCreateIndexPart($driver, $indexName, $tableName, $columnNames)
    {
        return 'CREATE INDEX '
            . $driver->quoteConstraint($tableName, $indexName)
            . ' ON '
            . $driver->quoteTable($tableName)
            . ' ('
            . join(', ', array_map(function($v) use ($driver) {
                return $driver->quoteIdentifier($v);
            }, $columnNames))
            . ')';
    }

    protected static function buildCreateTableColumns($driver, $command, $list)
    {
        $map = $driver->getColumnTypeMap();

        foreach ($command->columns as $columnPart) {
            $mapped = $map[$columnPart->columnType];
            $item = $driver->quoteIdentifier($columnPart->columnName) . ' ' . $mapped['type'];

            if (isset($mapped['supportOptions'])) {
                if (count($columnPart->columnOptions) < $mapped['supportOptions'][0]) {
                    throw new InvalidArgumentsException(
                        "Options count is less than required ({$mapped['supportOptions'][0]}) for column \"{$columnPart->columnName}\""
                    );
                }

                if (count($columnPart->columnOptions) > $mapped['supportOptions'][1]) {
                    throw new InvalidArgumentsException(
                        "Options count is more than required ({$mapped['supportOptions'][1]}) for column \"{$columnPart->columnName}\""
                    );
                }

                $item .= '(' . join(', ', array_map(function($v) use ($driver) {
                    return $driver->quote($v);
                }, $columnPart->columnOptions)) . ')';
            }

            if ((!isset($mapped['supportNotNull']) || $mapped['supportNotNull']) && $columnPart->notNull) {
                $item .= ' NOT NULL';
            }

            if ((!isset($mapped['supportDefault']) || $mapped['supportDefault']) && $columnPart->default) {
               $item .= ' DEFAULT ' . self::buildValuePart($driver, $columnPart->default);
            }

            $list[] = $item;
        }

        return $list;
    }

    protected static function buildCreateTableConstraints($driver, $command, $list)
    {
        $optionMap = $driver->getReferenceOptionMap();

        foreach ($command->uniqueList as $item) {
            $list[] = 'CONSTRAINT '
                . $driver->quoteConstraint($command->tableName, $item['name'])
                . ' UNIQUE ('
                . join(', ', array_map(function($v) use ($driver) {
                    return $driver->quoteIdentifier($v);
                }, $item['columns']))
                . ')';
        }

        foreach ($command->foreignKeyList as $reference) {
            $list[] = 'CONSTRAINT '
                . $driver->quoteConstraint($command->tableName, $reference->constraintName)
                . ' FOREIGN KEY ('
                . join(', ', array_map(function($v) use ($driver) {
                    return $driver->quoteIdentifier($v);
                }, $reference->columns))
                . ') REFERENCES '
                . $driver->quoteTable($reference->refTableName)
                . ' ('
                . join(', ', array_map(function($v) use ($driver) {
                    return $driver->quoteIdentifier($v);
                }, $reference->refColumns))
                . ') ON UPDATE '
                . $optionMap[$reference->onUpdate]
                . ' ON DELETE '
                . $optionMap[$reference->onDelete];
        }

        return $list;
    }

    protected static function buildUpdateCommand($driver, $command)
    {
        if ($command->table === null) {
            throw new BuildException('Update command must have "table" clause');
        }

        if (!count($command->setList)) {
            throw new BuildException('Update command must have "set" clause');
        }

        return 'UPDATE '
            . self::buildTable($driver, $command->table)
            . ' SET ' . self::buildSetList($driver, $command->setList)
            . ($command->where ? ' WHERE ' . self::buildExpressionPart($driver, $command->where) : '')
        ;
    }

    protected static function buildSetList($driver, $list)
    {
        return join(', ', array_map(function($v) use ($driver) {
            return self::buildColumnPart($driver, $v['column'])
                . ' = '
                . self::buildPart($driver, $v['value'])
            ;
        }, $list));
    }

    protected static function buildDeleteCommand($driver, $command)
    {
        if ($command->from === null) {
            throw new BuildException('Delete command must have "from" clause');
        }

        return 'DELETE FROM '
            . self::buildTable($driver, $command->from)
            . ($command->where ? ' WHERE ' . self::buildExpressionPart($driver, $command->where) : '')
        ;
    }

    protected static function buildInsertCommand($driver, $command)
    {
        if ($command->into === null) {
            throw new BuildException('Insert command must have "into" clause');
        }

        $result = 'INSERT INTO '
            . self::buildTable($driver, $command->into)
            . ' (' . self::buildPartList($driver, $command->columns) . ')'
        ;

        if (count($command->values)) {
            $result .= ' VALUES ' . self::buildInsertValues($driver, $command->values);
        }

        if ($command->select) {
            $result .= ' ' . self::buildSelectCommand($driver, $command->select);
        }

        return $result;
    }

    protected static function buildInsertValues($driver, $values)
    {
        return join(', ', array_map(function($v) use ($driver) {
            return '(' . self::buildPartList($driver, $v, ', ', '', '') . ')';
        }, $values));
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
            return self::buildColumnPart($driver, $v['column']) . ' ' . (($reverse ? !$v['asc'] : $v['asc']) ? 'ASC' : 'DESC');
        }, $command->orderByList)) . ' ';
    }

    protected static function buildGroupByList($driver, $list, $prepend)
    {
        if (!count($list)) {
            return '';
        }

        return $prepend . join(', ', array_map(function($v) use ($driver) {
            return self::buildColumnPart($driver, $v);
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
        } elseif ($part instanceof ColumnPart) {
            $result = self::buildColumnPart($driver, $part);
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

    protected static function buildColumnPart($driver, $part)
    {
        if ($part->columnName === '*') {
            $result = '*';
        } else {
            $result = $driver->quoteIdentifier($part->columnName);
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
