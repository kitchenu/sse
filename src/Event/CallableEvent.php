<?php

namespace Kitchenu\Sse\Event;

class CallableEvent extends AbstractEvent
{
    protected $name;

    protected $data;

    protected $callback;

    public function __construct($name, callable $callback)
    {
        parent::__construct($name);
        $this->callback = $callback;
    }

    /**
     * Check for continue to send event.
     *
     * @return bool
     */
    public function check()
    {
        $this->data = $this->callback->__invoke();

        return $this->data != null ? true : false;
    }
}