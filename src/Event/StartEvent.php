<?php

namespace Ssel\Event;

abstract class StartEvent implements EventInterface
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function ready()
    {
        return true;
    }

    public function name()
    {
        return $this->name;
    }

    public function type()
    {
        return self::TYPE_START;
    }
}