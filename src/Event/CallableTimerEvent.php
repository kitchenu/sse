<?php

namespace Ssel\Event;

class CallableTimerEvent extends TimerEvent
{
    use CallableTrait;

    public function __construct($name, $interval, callable $callback)
    {
        parent::__construct($name, $interval);
        $this->callback = $callback;
    }
}