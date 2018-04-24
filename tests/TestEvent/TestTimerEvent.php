<?php

namespace Ssel\Tests\TestEvent;

use Ssel\Event\TimerEvent;

class TestTimerEvent extends TimerEvent
{ 
    public function data()
    {
        return 'timer event test';
    }
} 