<?php declare(strict_types=1);

namespace Hi\Server;

use Hi\Server\Runtime\ServerInterface;
use RuntimeException;

use function posix_kill;

/**
 * Server 进程管理
 */
class Manager
{
    /**
     * @var string
     */
    protected $runtimeDirectory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ServerInterface
     */
    protected $server;

    /**
     * Manager Construct
     */
    public function __construct(string $name, string $runDir)
    {
        $this->name = $name;
        $this->runtimeDirectory = $this->processRuntimeDirectory($runDir);
    }

    /**
     * 返回进程 id
     */
    public function pid(): int
    {
        $pidFile = $this->pidFile();
        if (! is_file($pidFile)) {
            return 0;
        }

        $pid = @file_get_contents($pidFile);
        if (! $pid) {
            return 0;
        }

        return (int) $pid;
    }

    /**
     * 返回进程名称
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * 返回进程 ID 所在文件路径
     */
    public function pidFile(): string
    {
        return $this->runtimeDirectory . $this->name() . '.pid';
    }

    /**
     * 返回进程日志文件路径
     */
    public function logFile(): string
    {
        return $this->runtimeDirectory . $this->name() . '.log';
    }

    /**
     * 平滑重启服务
     */
    public function reload()
    {
        if ($this->isRunning()) {
            posix_kill($this->pid(), SIGUSR1);
        }
    }

    /**
     * 平滑停止服务
     */
    public function stop()
    {
        if ($this->isRunning()) {
            posix_kill($this->pid(), SIGTERM);
            $this->waitForStop();
        }
    }

    /**
     * 强制停止服务(强制杀掉服务相关所有进程)
     */
    public function shutdown()
    {
        $pids = $this->pids();
        if (! $pids) {
            return;
        }

        exec('kill -9 ' . implode(' ', array_keys($pids)));
        $this->waitForStop();
    }

    /**
     * 返回当前进程 ID 及所有子进程 ID
     */
    public function pids(): array
    {
        if ($this->pid() == 0) {
            return [];
        }

        $tree = [];
        $this->findChildPids($this->pid(), $tree);

        return array_keys($tree);
    }

    /**
     * 以递归方式查找指定 pid 下进程 pid 树
     */
    protected function findChildPids($pid, &$pids = [])
    {
        exec("ps -A -o pid,ppid | grep {$pid}", $output);

        foreach ($output as $line) {
            $data      = explode(' ', trim($line, ' '));
            $childPid  = array_shift($data);
            $parentPid = array_pop($data);

            if ($childPid != $pid) {
                $this->findChildPids($childPid, $pids);
            }

            $pids[$childPid] = $parentPid;
        }
    }

    /**
     * 返回进程是否运行中状态
     */
    public function isRunning(): bool
    {
        if ($this->pids()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 阻塞等待进程启动
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

    /**
     * 阻塞等待进程停止
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
     * 返回进程运行目录
     */
    public function runtimeDirectory(): string
    {
        return $this->runtimeDirectory;
    }

    /**
     * 处理服务运行时文件存放目录
     */
    protected function processRuntimeDirectory(string $path = null): string
    {
        if ($path && is_dir($path)) {
            return $path;
        }

        $tempDir = sys_get_temp_dir() . '/' . md5(__DIR__) . '/';
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        if (! is_dir($tempDir)) {
            throw new RuntimeException(
                '运行目录[' . $path . ']初始化失败，请检查相关目录权限'
            );
        }

        return $tempDir;
    }
}
