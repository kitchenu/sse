<?php

namespace Ssel\Event;

abstract class TimerEvent implements EventInterface
{
    protected $name;

    protected $interval;

    public function __construct($name, $interval)
    {
        $this->name = $name;
        $this->interval = $interval;
    }

    public function ready()
    {
        return true;
    }

    public function name()
    {
        return $this->name;
    }

    public function interval()
    {
        return $this->interval;
    }

    public function type()
    {
        return self::TYPE_TIMER;
    }
}
