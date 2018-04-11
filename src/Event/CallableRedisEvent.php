<?php

namespace Ssel\Event;

class CallableRedisEvent extends RedisEvent
{
    use CallableTrait;

    public function __construct($name, $channel, callable $callback)
    {
        parent::__construct($name, $channel);
        $this->callback = $callback;
    }
}