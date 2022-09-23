<?php

declare(strict_types=1);

namespace Hi;

use function array_unshift;
use function exec;

use Hi\Server\Config;
use Hi\Server\ProcessTrait;

use function implode;
use function posix_kill;

abstract class Server
{
    use ProcessTrait;

    /**
     * Server config
     *
     * @var Config
     */
    protected $config;

    /**
     * Construct
     *
     * @param array{} $config
     */
    public function __construct(array $config = [])
    {
        $this->config = new Config($config);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * 设置服务 host
     *
     * @return $this
     */
    public function withHost(string $host)
    {
        $this->config->set('host', $this->config->processHost($host));

        return $this;
    }

    /**
     * 设置服务 port
     *
     * @return $this
     */
    public function withPort(int $port)
    {
        $this->config->set('port', $this->config->processPort($port));

        return $this;
    }

    /**
     * 启动服务
     */
    abstract public function start(): void;

    /**
     * 平滑重启服务
     */
    public function reload(): void
    {
        if ($this->isRunning()) {
            posix_kill($this->getPid(), SIGUSR1);
        }
    }

    /**
     * 平滑停止服务
     */
    public function stop(): void
    {
        if ($this->isRunning()) {
            posix_kill($this->getPid(), SIGTERM);
            $this->waitForStop();
        }
    }

    /**
     * 强制停止服务(强制杀掉服务相关所有进程)
     */
    public function shutdown(): void
    {
        $pids = $this->getChildPids();
        if (!$pids) {
            return;
        }

        // 携带 Master 进程 PID
        array_unshift($pids, $this->getPid());

        // 执行 kill -9 强制结束进程
        exec('kill -9 ' . implode(' ', $pids));

        $this->waitForStop();
    }
}
