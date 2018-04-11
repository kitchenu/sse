<?php

namespace Ssel\Event;

/**
 *
 * @author precom
 */
trait CallableTrait
{
    protected $callback;

    public function ready($params = [])
    {
        $data = call_user_func_array($this->callback, $params);

        if (is_null($data)) {
            return false;
        }

        $this->data = $data;

        return true;
    }

    public function name()
    {
        return $this->name;
    }

    public function data()
    {
        return $this->data;
    }
}
