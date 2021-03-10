<?php declare(strict_types=1);

namespace Hi\Server;

use InvalidArgumentException;
use RuntimeException;
use Swoole\Process;
use Swoole\Server;

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
     * @var array
     */
    protected $eventHandle = [];

    /**
     * 启动 swoole 服务
     */
    public function start(int $port = 9527, string $host = '127.0.0.1'): void
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

    public function restart(bool $force = false)
    {
        if ($force) {
            $this->stop(true);
            $this->start();
        } else {
            Process::kill($this->getPid(), SIGUSR1);
        }
    }

    public function stop(bool $force = false)
    {
        $pid = $this->getPid();

        $pidtree = $this->servicePidTree($pid);
        if (! $pidtree) {
            return true;
        }

        if ($force) {
            exec('kill -9 ' . implode(' ', array_keys($pidtree)));
        } else {
            Process::kill($pid, SIGTERM);
        }

        // 等待进程完全退出
        $this->waitForStop();

        return true;
    }

    /**
     * 等待检查服务状态
     * 超过 30 秒没有结束是为服务停止失败
     */
    public function waitForStop()
    {
        $count = 0;
        while (true) {
            if (sleep(1) || $count++ > 30) {
                throw new RuntimeException('操作失败，未检测到服务成功停止');
            }
            if ($this->isRunning() === false) {
                break;
            }
        }
    }

    /**
     * 如果服务启动与结束过快子进程未完全启动
     * 此时停止服务将会导致子进程孤立
     * 所以此处等待时间延长，以尽可能确保进程都启动了
     */
    public function waitForStart()
    {
        $count = 0;
        while (true) {
            if (sleep(1) || $count++ > 30) {
                throw new RuntimeException('操作失败，未检测到服务成功启动');
            }
            if ($this->isRunning() === true) {
                break;
            }
        }
    }

    public function getPid(): int
    {
        $pidFile = $this->getPidFile();
        if (! is_file($pidFile)) {
            return 0;
        }

        $pid = @file_get_contents($pidFile);
        if (! $pid) {
            return 0;
        }

        return (int) $pid;
    }

    public function getPidFile(): string
    {
        $setting = $this->processSetting();
        return $setting['pid_file'] ?? '';
    }

    public function isRunning(): bool
    {
        if ($this->servicePidTree($this->getPid())) {
            return true;
        } else {
            return false;
        }
    }

    protected function registerEventHandle()
    {
        if (empty($this->eventHandle)) {
            throw new InvalidArgumentException('无法启动服务，必须在 eventHandle 中设置事件');
        }

        foreach (get_class_methods($this) as $value) {
            if (in_array($value, $this->eventHandle)) {
                $this->server->on(lcfirst(substr($value, 2)), [$this, $value]);
            }
        }
    }

    protected function processSetting(): array
    {
        return array_merge($this->defaultSetting(), $this->config['swoole'] ?? []);
    }

    protected function defaultSetting()
    {
        return [
            'log_file'          => $this->defaultLogPath(),
            'pid_file'          => $this->defaultPidFilePath(),
            'task_tmpdir'       => sys_get_temp_dir(),
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

