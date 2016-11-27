<?php

namespace Kitchenu\Sse\Event;


abstract class AbstractEvent implements EventInterface
{
    protected $name;

    protected $data;

    public function __construct($name = null)
    {
        if ($name) {
            $this->name = $name;
        }
    }

    /**
     * @param int
     * @return string
     */
    public function encode($id)
    {
        return <<<EOT
id: $id
event: {$this->name}
data: {$this->data}
\n
EOT;
    }
}