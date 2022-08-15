<?php

namespace Hi\Server;

use Swoole\Server as BaseSwooleServer;

/**
 * Swoole 运行容器基类
 */
abstract class SwooleServer extends Server
{
    /**
     * @var BaseSwooleServer
     */
    protected $server;

    /**
     * @inheritdoc
     */
    public function start(): void
    {
        if ($this->isRunning()) {
            throw new ServerException(
                "操作失败，{$this->config->get('host')}:{$this->config->get('port')} 已被占用"
            );
        }

        $this->server = $this->createServer();

        $this->bindEventHanle();
        $this->server->set($this->setting());
        $this->server->start();
    }

    /**
     * 绑定 Swoole 事件 handle
     */
    protected function bindEventHanle(): void
    {
        $this->collectionEventHandle();

        foreach ($this->eventHandle as $name => $value) {
            /** @var array{0: string, 1: string} $value */
            /** @var callable $handle */
            $handle = [$this, $value[1]];
            $this->server->on($name, $handle);
        }
    }

    /**
     * 处理服务设置
     *
     * @return array<string, string|int>
     */
    public function setting(): array
    {
        return array_merge(
            [
                'pid_file' => $this->config->get('pid_file'),
                'log_file' => $this->config->get('log_file'),
                'open_cpu_affinity' => true,
            ],
            $this->config->get('swoole')
        );
    }

    /**
     * 由子类实现，返回对应 Swoole Server 实例
     * 
     * @return BaseSwooleServer
     */
    abstract protected function createServer();
}
