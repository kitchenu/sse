<?php

namespace Ssel\Tests\TestEvent;

use Ssel\Event\StartEvent;

class TestStartEvent extends StartEvent
{
    public function data()
    {
        return 'start event test';
    }
} 