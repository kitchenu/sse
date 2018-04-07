<?php

namespace Kitchenu\Sse;

use Kitchenu\Sse\Event\EventInterface as Event;
use Kitchenu\Sse\Event\CallableEvent;
use GuzzleHttp\Psr7\Response;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory;
use DateTime;

class SSE
{
    protected $settings = [
        'execLimit' => 60,
        'retryTime' => 1,
        'keepAliveInterval' => 30,
        'sendEventName' => true,
    ];

    /**
     * @var int
     */
    protected $eventId;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var string
     */
    protected $keepMessage = 'keep alive';

    /**
     * @var array
     */
    protected $timers = [];

    /**
     * SSE constructor.
     *
     * @param $settings
     * @param $headers
     */
    public function __construct($settings = [], $headers = [])
    {
        $this->settings = array_merge($this->settings, $settings);
        $this->eventId = isset($_SERVER['HTTP_LAST_EVENT_ID']) ? (int) $_SERVER['HTTP_LAST_EVENT_ID'] : 1;
        $this->response = $this->createResponse($headers);
        $this->loop = Factory::create();
    }

    protected function createResponse(array $headers)
    {
        return new Response(200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ] + $headers);
    }

    /**
     * Add a event handler
     * @param string $name the event name
     * @param Event|Closure $event the event handler
     * @return void
     */
    public function addTimerEvent($name, $event, $interval = 5, $periodic = true)
    {
        if ($event instanceof Event) {
            $this->timers[$name] = $this->loop->addPeriodicTimer($interval, function () use ($name, $event) {
                if ($event->ready($this->startedTime)) {
                    $this->sendEvent($event->data(), $event->name());
                }
            });
        } elseif (is_callable($event)) {
            $this->timers[$name] = $this->loop->addPeriodicTimer($interval, function () use ($name, $event) {
                $data = call_user_func($event, $this->startedTime);
                if (!is_null($data)) {
                    $this->sendEvent($name, $data);
                }
            });
        }
    }


    /**
     * @param string $name
     * @param string $data
     * @return string
     */
    protected function sendEvent($name, $data)
    {
        $message = '';

        if ($this->settings['sendEventName']) {
            $message .= "\nevent: $name";
        }

        $message .= "\ndata: $data\nid: {$this->eventId}\n";

        echo $message;

        $this->eventId++;
    }

    /**
     * remove a event handler
     *
     * @param string $name the event name
     * @return void
     */
    public function removeTimerEvent($name, $event, $interval = 5, $periodic = true)
    {
        $this->loop->cancelTimer($this->timers[$name]);
        unset($this->timers[$name]);
    }

    /**
     * remove a event handler
     *
     * @return void
     */
    public function run()
    {
        $this->init();
        $this->loop->run();
    }

    /**
     * Initial System
     *
     * @return void
     */
    protected function init()
    {
        if ($limit = $this->settings['execLimit']) {
            $this->loop->addTimer($limit, function () {
                $this->loop->stop();
            });
        }

        $this->loop->futureTick(function () {
            $this->startedTime = new DateTime();

            $this->sendHeader();

            if ($this->settings['retryTime']) {
                $retryTime = $this->settings['retryTime'] * 1000;
                echo "retry: $retryTime\n";
            }
        });

        if ($interval = $this->settings['keepAliveInterval']) {
            $this->loop->addPeriodicTimer($interval, function () {
                echo "\n: {$this->keepMessage}\n";
            }); 
        }

//        @set_time_limit(0); // Disable time limit
//
//        // Prevent buffering
//        if(function_exists('apache_setenv')){
//            @apache_setenv('no-gzip', 1);
//        }
//
//        @ini_set('zlib.output_compression', 0);
//        @ini_set('implicit_flush', 1);
//
//        while (ob_get_level() != 0) {
//            ob_end_flush();
//        }
//        ob_implicit_flush(1);

    }

    /**
     * @return void
     */
    protected function sendHeader()
    {
        foreach ($this->response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), false);
            }
        }

        header(sprintf(
            'HTTP/%s %s %s',
            $this->response->getProtocolVersion(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase()
        ));
    }

    public function settings($key = null, $value = null)
    {
        if (is_null($key)) {
            return $this->settings;
        }

        if (is_array($key)) {
            $this->settings = array_merge($this->settings, $key);
        } elseif (is_null($value)) {
            return $this->settings[$key];
        } else {
            $this->settings[$key] = $value;
        }
    }

    public function loop($loop = null)
    {
        if (!$loop instanceof LoopInterface) {
            return $this->loop;
        }

        $this->loop = $loop;
    }

    public function keepMessage($message = null)
    {
        if (is_null($message)) {
            return $this->keepMessage;
        }

        $this->keepMessage = (string) $message;
    }
}
