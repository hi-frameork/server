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
        if ($this->isRunning() === false) {
            return true;
        }

        if ($force) {
            exec("ps -ef | grep {$this->name} | grep -vE 'grep|watcher' | cut -c 9-15 | xargs kill -s 9");
        } else {
            Process::kill($this->getPid(), SIGTERM);
        }

        while (true) {
            if ($this->isRunning() === false) {
                unlink($this->getPidFile());
                if (is_file($this->getPidFile())) {
                    throw new RuntimeException('进程 pid 文件删除失败');
                }
                break;
            }
        }

        return true;
    }

    public function getPid()
    {
        $pidFile = $this->getPidFile();
        if (! is_file($pidFile)) {
            return false;
        }

        $pid = @file_get_contents($this->getPidFile());
        if (! $pid) {
            return false;
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
        $pid = $this->getPid();
        if (! $pid) {
            return false;
        }

        return Process::kill($pid, SIG_DFL);
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
        $setting = array_merge($this->defaultSetting(), $this->config['swoole'] ?? []);
        return $setting;
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

