<?php

namespace Ssel\Tests;

use Ssel\App;
use Ssel\Tests\TestEvent\TestStartEvent;
use Ssel\Tests\TestEvent\TestTimerEvent;
use Ssel\Event\EventInterface;

class AppTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var App
     */
    private $app;

    public function setUp()
    {
        $this->app = new App();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRun()
    {
        $time = strtotime("now");
        $this->app->settings([
            'execLimit' => 3,
            'retryTime' => 0
        ]);
        $this->app->run();
        $time = strtotime("now") - $time;

        $this->assertGreaterThanOrEqual(3, $time);
    }

    public function testEvents()
    {
        $this->app->events([
            'start' => new TestStartEvent('start'),
            'timer' => new TestTimerEvent('timer', 5),
        ]);

        $events = $this->app->events();
        $this->assertSame(count($events), 2);
        $this->assertContainsOnly(EventInterface::class, $events);

        $this->app->events('timer2', new TestTimerEvent('timer', 5));
        $this->assertInstanceOf(TestTimerEvent::class, $this->app->events('timer2'));

        $this->app->removeEvent('timer');
        try {
            $this->app->events('timer');
            $this->fail('Test failed.');
        } catch (\Ssel\Exception\EventNotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testaAddTimerEvent()
    {
        $callback = function () {
            return 'test';
        };
        $this->app->addTimerEvent('timer', $callback, 10);
        $event = $this->app->events('timer');

        $this->assertInstanceOf(\Ssel\Event\CallableTimerEvent::class, $event);
        $this->assertSame($event->name(), 'timer');
        $this->assertSame($event->interval(), 10);
        $this->assertTrue($event->ready());
        $this->assertSame($event->data(), 'test');
    }

    public function testaAddStartEvent()
    {
        $callback = function () {
            return 'test';
        };
        $this->app->addStartEvent('start', $callback);
        $event = $this->app->events('start');

        $this->assertInstanceOf(\Ssel\Event\CallableStartEvent::class, $event);
        $this->assertSame($event->name(), 'start');
        $this->assertTrue($event->ready());
        $this->assertSame($event->data(), 'test');
    }

    public function testSetHeader()
    {
        $this->app->setHeader('User-Agent', 'test');
        $header = $this->app->response()->getHeaderLine('User-Agent');
        $this->assertSame($header, 'test');
    }

    public function testSettings()
    {
        $this->app->settings(['execLimit' => 1000]);
        $this->assertSame($this->app->settings()['execLimit'], 1000);

        $this->app->settings('execLimit', 2000);
        $this->assertSame($this->app->settings('execLimit'), 2000);
    }

    public function testkeepMessage()
    {
        $this->assertInternalType('string', $this->app->keepMessage());

        $message = 'test';
        $this->app->keepMessage($message);
        $this->assertSame($this->app->keepMessage(), $message);
    }

    public function testLoop()
    {
        $this->assertInstanceOf(\React\EventLoop\LoopInterface::class, $this->app->loop());

        $loop = \React\EventLoop\Factory::create();
        $this->app->loop($loop);
        $this->assertSame($loop, $this->app->loop());
    }

    public function testResponce()
    {
        $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $this->app->response());
    }
}