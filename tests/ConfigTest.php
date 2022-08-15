<?php

namespace Hi\Server\Tests;

use Hi\Server\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testDefaultConstruct()
    {
        $config = new Config();
        $this->assertSame(
            [
                'name' => 'hi-server',
                'host' => '0.0.0.0',
                'port' => 9527,
                'workerman' => [],
                'swoole' => [],
                'pid_file' => $config->getPidFile(),
                'log_file' => $config->getLogFile(),
            ],
            $config->get()
        );
    }

    public function testConstruct()
    {
        $values = [
            'name' => 'hi-test',
            'port' => 9000,
            'host' => '127.0.0.1',
            'pid_file' => '/tmp/hi-test.pid',
            'log_file' => '/tmp/hi-test.log',
        ];

        $config = new Config($values);

        $this->assertEquals(
            [
                'name' => 'hi-test',
                'host' => '127.0.0.1',
                'port' => 9000,
                'pid_file' => '/tmp/hi-test.pid',
                'log_file' => '/tmp/hi-test.log',
                'workerman' => [],
                'swoole' => [],
            ],
            $config->get()
        );
    }

    public function testProcessName()
    {
        $config = new Config(['name' => 'test-name']);
        $this->assertSame('test-name', $config->get('name'));
    }

    public function testProcessHost()
    {
        $config = new Config(['host' => '127.0.0.1']);
        $this->assertSame('127.0.0.1', $config->getHost());
    }

    public function testProcessPort()
    {
        $config = new Config(['port' => 3030]);
        $this->assertSame(3030, $config->getPort());
    }

    public function testDefaultPidFile()
    {
        $config = new Config();
        $this->assertSame(
            $config->defaultDirectory() . $config->get('name') . '.pid',
            $config->getPidFile()
        );
    }

    public function testDefaultLogFile()
    {
        $config = new Config();
        $this->assertSame(
            $config->defaultDirectory() . $config->get('name') . '.log',
            $config->getLogFile()
        );
    }
}
