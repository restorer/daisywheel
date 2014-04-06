<?php

namespace daisywheel\db\builder;

class DeleteCommand extends Command
{
    protected $from = '';
    protected $where = null;

    public function from()
    {
        $this->from = new Table(func_get_args());
        return $this;
    }

    public function where()
    {
        $this->where = ExpressionPart::create(func_get_args());
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
