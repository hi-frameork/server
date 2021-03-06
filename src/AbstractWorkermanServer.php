<?php declare(strict_types=1);

namespace Hi\Server\Runtime;

use InvalidArgumentException;
use Workerman\Worker;

abstract class AbstractWorkermanServer extends AbstractServer implements ServerInterface
{
    /**
     * @var Worker
     */
    protected $server;

    /**
     * 启动 Workerman 服务
     */
    public function start()
    {
        $this->setCommandToArgv('start');
        $this->server = $this->createServer();
        $this->registerEventHandle();
        $this->processSetting();
        Worker::runAll();
    }

    /**
     * 注册服务事件回调
     */
    protected function registerEventHandle()
    {
        $this->checkEventHandle();

        foreach (get_class_methods($this) as $value) {
            if (in_array($value, $this->eventHandle)) {
                $this->server->{$value} = [$this, $value];
            }
        }
    }

    /**
     * 处理服务设置
     */
    protected function processSetting()
    {
        $config = $this->config()['workerman'] ?? [];

        if (! $config) {
            return;
        }

        $this->server::$pidFile   = $this->manager->pidFile();
        $this->server::$logFile   = $this->manager->logFile();
        $this->server::$daemonize = $config['daemonize'] ?? false;

        if (isset($config['count'])) {
            $this->server->count = $config['count'];
        }
        if (isset($config['name'])) {
            $this->server->name = $config['name'];
        }
        if (isset($config['user'])) {
            $this->server->user = $config['user'];
        }
    }

    /**
     * 在当前环境 argv 参数设置指令
     */
    protected function setCommandToArgv(string $command)
    {
        $availableCommands = [
            'start',
            'stop',
            'restart',
            'reload',
        ];

        if (! in_array($command, $availableCommands)) {
            throw new InvalidArgumentException("命令：{$command} 不被支持");
        }

        global $argv;
        $argv[] = $command;
    }

    /**
     * 创建服务实例
     */
    abstract protected function createServer();
}
