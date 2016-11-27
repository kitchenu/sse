<?php

namespace Kitchenu\Sse\Event;

interface EventInterface
{
    public function check();

    public function encode($id);
}