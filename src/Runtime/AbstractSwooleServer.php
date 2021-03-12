<?php declare(strict_types=1);

namespace Hi\Server;

use RuntimeException;
use Swoole\Process;
use Swoole\Server;

use function get_class_methods;
use function in_array;
use function substr;
use function array_merge;

/**
 * Swoole 运行容器基类
 */
abstract class AbstractSwooleServer extends AbstractServer implements ServerInterface
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * 启动 swoole 服务
     */
    public function start(int $port = 9527, string $host = '127.0.0.1')
    {
        $this->processPort($port);
        $this->processHost($host);

        if ($this->isRunning()) {
            throw new RuntimeException("操作失败，服务已经运行在： {$this->host()}:{$this->port()}");
        }

        $this->server = $this->createServer();

        $this->registerEventHandle();
        $this->server->set($this->processSetting());
        $this->server->start();
    }

    /**
     * 平滑重启服务
     */
    public function reload()
    {
        Process::kill($this->pid(), SIGUSR1);
    }

    /**
     * 强制重启服务
     */
    public function restart()
    {
        $this->stop(true);
        $this->start();
    }

    /**
     * 平滑停止服务
     */
    public function stop()
    {
        if (! $this->isRunning()) {
            return true;
        }

        Process::kill($this->pid(), SIGTERM);
        // 等待进程完全退出
        $this->waitForStop();

        return true;
    }

    /**
     * 注册服务响应回调事件
     */
    protected function registerEventHandle()
    {
        $this->checkEventHandle();

        foreach (get_class_methods($this) as $value) {
            if (in_array($value, $this->eventHandle)) {
                $this->server->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }
    }

    /**
     * 处理服务设置
     */
    protected function processSetting(): array
    {
        return array_merge($this->defaultSetting(), $this->config()['swoole'] ?? []);
    }

    /**
     * 返回组建内置的默认服务设置
     */
    protected function defaultSetting(): array
    {
        return [
            'pid_file'          => $this->pidFile(),
            'log_file'          => $this->logFile(),
            'open_cpu_affinity' => true,
        ];
    }

    /**
     * 由子类实现，返回对应 swoole server 实例
     * 
     * 对于不同的实现子类，返回对应 server 实例，例如：
     *  在 http 服务中，返回 \Swoole\Http\Server
     *  在 tcp 服务中，返回 \Swoole\Tcp\Server
     *
     * @return \Swoole\Server
     */
    abstract protected function createServer();
}

