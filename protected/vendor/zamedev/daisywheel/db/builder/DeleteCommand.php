<?php

namespace daisywheel\db\builder;

class DeleteCommand extends Command
{
    protected $from = null;
    protected $where = null;

    public function from()
    {
        $this->from = Table::create(func_get_args());
        return $this;
    }

    public function where($expression)
    {
        $this->where = $expression;
        return $this;
    }

    protected function getFrom()
    {
        return $this->from;
    }

    protected function getWhere()
    {
        return $this->where;
    }
}
