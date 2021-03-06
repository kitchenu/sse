<?php

namespace Ssel\Event;

interface EventInterface
{
    const TYPE_START = 'start';
    const TYPE_END = 'end';
    const TYPE_TIMER = 'timer';
    const TYPE_SUB = 'sub';

    public function ready();

    public function name();
 
    public function data();

    public function type();
}