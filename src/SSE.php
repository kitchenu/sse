<?php

namespace Kitchenu\Sse;

use Closure;
use Kitchenu\Sse\Event\EventInterface as Event;
use Kitchenu\Sse\Event\CallableEvent;

class SSE
{
    protected $id;
    /**
     * @var array
     */
    protected $events = [];

    protected $settings = [
        'seelpTime'     => 1,
        'execLimit'     => 600,
        'reconnectTime' => 1,
        'keepAliveTime' => 30,
        'keepMessage'   => 'keep alive',
    ];

    protected $headers = [
        'Content-Type'                     => 'text/event-stream',
        'Cache-Control'                    => 'no-cache',
        'X-Accel-Buffering'                => 'no',
        'Access-Control-Allow-Origin'      => '*',
        'Access-Control-Allow-Credentials' => 'true',
    ];

    /**
     * SSE constructor.
     *
     * @param $settings
     * @param $headers
     */
    public function __construct($settings = [], $headers = [])
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->id = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? (int) $_SERVER['HTTP_LAST_EVENT_ID'] : 0;
    }

    /**
     * Add a event handler
     * @param string $name the event name
     * @param Event|Closure $event the event handler
     * @return void
     */
    public function addEvent($name, $event)
    {
        if ($event instanceof Event) {
            $this->events[$name] = $event;
        } elseif ($event instanceof Closure) {
            $this->events[$name] = new CallableEvent($name, $event);
        }
    }

    /**
     * remove a event handler
     *
     * @param string $name the event name
     * @return void
     */
    public function removeEvent($name)
    {
        unset($this->events[$name]);
    }

    /**
     * remove a event handler
     *
     * @return void
     */
    public function run()
    {
        $this->init();

        $this->sendHeader();

        echo 'retry: ' . ($this->settings['reconnectTime'] * 1000) . "\n";

        $start = $from = time();

        while (1) {
            if (time() - $from > $this->settings['keepAliveTime']) {
                $from = time();
                // No updates needed, send a comment to keep the connection alive.
                echo ": {$this->keepMessage}\n\n";
            }

            foreach ($this->events as $event) {
                $this->sendEvent($event);
            }

            // Break if the time exceed the limit
            if ($this->settings['execLimit'] !== 0 && time() - $start > $this->settings['execLimit'] ) {
                break;
            }

            usleep($this->settings['seelpTime']  * 1000000);
        }
    }

    /**
     * Initial System
     *
     * @return void
     */
    protected function init()
    {
        @set_time_limit(0); // Disable time limit

        // Prevent buffering
        if(function_exists('apache_setenv')){
            @apache_setenv('no-gzip', 1);
        }

        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);

        while (ob_get_level() != 0) {
            ob_end_flush();
        }
        ob_implicit_flush(1);
    }

    /**
     * @return void
     */
    protected function sendHeader()
    {
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value, false);
        }
    }

    /**
     * @param Event $event
     * @return void
     */
    protected function sendEvent(Event $event)
    {
        if ($event->check()) {
            $this->id++;
            echo $event->encode($this->id);

            // Make sure the data has been sent to the client
            @ob_flush();
            @flush();
        }
    }
}
