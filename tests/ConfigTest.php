<?php

namespace Hi\Server\Tests;

use Hi\Server\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testGetDefault()
    {
        $config = new Config();

        $this->assertSame('hi-server', $config->get('name'));
        $this->assertSame('0.0.0.0', $config->get('host'));
        $this->assertSame(9527, $config->get('port'));
        $this->assertSame([], $config->get('swoole'));
        $this->assertSame([], $config->get('workerman'));

        $pidFile = $config->defaultDirectory() . 'hi-server.pid';
        $this->assertSame($pidFile, $config->get('pid_file'));

        $logFile = $config->defaultDirectory() . 'hi-server.log';
        $this->assertSame($logFile, $config->get('log_file'));
    }

    public function testConstruct()
    {
        $values = [
            'name'     => 'hi-test',
            'port'     => 9000,
            'host'     => '127.0.0.1',
            'pid_file' => '/tmp/hi-test.pid',
            'log_file' => '/tmp/hi-test.log',
        ];

        $config = new Config($values);

        $this->assertSame($values['name'], $config->get('name'));
        $this->assertSame($values['host'], $config->get('host'));
        $this->assertSame($values['port'], $config->get('port'));
        $this->assertSame($values['pid_file'], $config->get('pid_file'));
        $this->assertSame($values['log_file'], $config->get('log_file'));
        $this->assertSame([], $config->get('swoole'));
        $this->assertSame([], $config->get('workerman'));
    }
}
