<?php

namespace daisywheel\db\drivers;

use daisywheel\db\builder\FunctionPart;

class SqliteDriver extends BaseDriver
{
    public function quoteIdentifier($name)
    {
        return '"' . str_replace('"', '""', preg_replace('/[^A-Za-z_\-."\'` ]/u', '', $name)) . '"';
    }

    public function buildFunctionPart($part)
    {
        if ($part->type === FunctionPart::TYPE_CONCAT) {
            return '(' . BuildHelper::buildPartList($this, $part->arguments, ' || ') . ')';
        }

        return parent::buildFunctionPart($part);
    }

    public function applySelectLimit($command, $start, $parts, $order)
    {
        $result = "{$start}{$parts}{$order}";

        if ($command->offset !== null) {
            $result .= ' LIMIT ' . $this->quote($command->offset) . ', ' . $this->quote($command->limit);
        } elseif ($command->limit !== null) {
            $result .= ' LIMIT ' . $this->quote($command->limit);
        }

        return $result;
    }
}
