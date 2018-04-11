<?php

namespace Ssel\Event;

class CallableStartEvent extends TimerEvent
{
    use CallableTrait;

    public function __construct($name, callable $callback)
    {
        parent::__construct($name);
        $this->callback = $callback;
    }
}