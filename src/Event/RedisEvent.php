<?php

namespace Ssel\Event;

abstract class RedisEvent implements EventInterface
{
    protected $name;

    protected $channel;

    protected $message;

    public function __construct($name, $channel)
    {
        $this->name = $name;
        $this->channel = $channel;
    }

    public function ready($message)
    {
        $this->message = $message;
        
        return true;
    }

    public function type()
    {
        return self::TYPE_REDIS;
    }

    public function isChannel($channel)
    {
        return $this->channel === $channel;
    }
}