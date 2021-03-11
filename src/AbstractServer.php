<?php declare(strict_types=1);

namespace Hi\Server;

use InvalidArgumentException;
use RuntimeException;

use function is_dir;
use function is_writable;
use function rtrim;
use function getcwd;
use function strstr;
use function mkdir;
use function md5;
use function sys_get_temp_dir;
use function explode;
use function sleep;
use function is_file;
use function file_get_contents;

abstract class AbstractServer
{
    /**
     * 默认结构： 
     *  [
     *      'host'      => null,
     *      'port'      => null,
     *      'name'      => null,
     *      'pid_file'  => null,
     *      'log_file'  => null,
     *      'swoole'    => [],
     *      'workerman' => [],
     *  ]
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var callable
     */
    protected $handleRequest;

    /**
     * @var array<string>
     */
    protected $eventHandle = [];

    /**
     * Server Construct.
     */
    public function __construct(array $config = [])
    {
        $this->processConfig($config);
    }

    /**
     * 返回当前库版本号
     */
    public function version(): string
    {
        return ServerInterface::VERSION;
    }

    /**
     * 返回服务 host
     */
    public function host(): string
    {
        return $this->config['host'] ?? '127.0.0.1';
    }

    /**
     * 返回服务端口
     *
     * @return int
     */
    public function port()
    {
        return $this->config['port'] ?? 9527;
    }

    /**
     * 返回服务名称（将用于设置进程名称）
     */
    public function name()
    {
        return $this->config['name'] ?? 'hi-server';
    }

    /**
     * 返回当前服务进程 ID
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
     * 返回服务进程 ID 所在的文件路径
     */
    public function pidFile(): string
    {
        return $this->config['pid_file'] ?? $this->defaultPidFilePath();
    }

    /**
     * 返回服务运行日志目录
     */
    public function logFile(): string
    {
        return $this->config['log_file'] ?? $this->defaultLogPath();
    }

    /**
     * 返回服务当前配置
     */
    public function config(): array
    {
        return $this->config;
    }
    
    /**
     * 注册请求处理回调
     *
     * @return static
     */
    public function withRequestHanle(callable $callback)
    {
        $this->handleRequest = $callback;
        return $this;
    }

    /**
     * 处理服务启动端口
     *
     * @return void
     */
    protected function processPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('端口无效，取值范围应 1 ~ 65535 之间');
        }

        // 如果 $this->config 已设置 port，优先使用
        $this->config['port'] = $this->config['port'] ?? $port;
    }

    /**
     * 处理服务启动 host
     *
     * @return void
     */
    protected function processHost(string $host)
    {
        // 如果 $this->config 已设置 host，优先使用
        $this->config['host'] = $this->config['host'] ?? $host;
    }

    /**
     * 检查 server 服务配置
     */
    protected function processConfig(array $config)
    {
        // 从配置中提取公共参数，用于后续 server 的快速创建
        $this->config['host'] = $config['host'] ?? null;
        $this->config['port'] = $config['port'] ?? null;

        // 服务名称（用户设置 http 服务进程名）
        $this->config['name'] = $config['name'] ?? null;

        // 服务 pid 所在文件
        $this->config['pid_file'] = $config['pid_file'] ?? null;
        // 服务日志文件
        $this->config['log_file'] = $config['log_file'] ?? null;

        // 对于不同 server 运行时，在配置中进行独立便于维护
        // 如果为传入对应配置，则使用 server 默认配置
        $this->config['swoole']    = $config['swoole'] ?? [];
        $this->config['workerman'] = $config['workerman'] ?? [];
    }

    /**
     * 检查服务要绑定服务事件是否存在
     *
     * 例如
     *  swoole http 服务至少需要 onRequest 事件
     *  workerman 至少需要 onReceive 事件
     */
    protected function checkEventHandle()
    {
        if (empty($this->eventHandle)) {
            throw new RuntimeException(
                '无法启动服务，必须在 eventHandle 中设置对应服务事件'
            );
        }

        foreach ($this->eventHandle as $method) {
            if (! method_exists($this, $method)) {
                throw new RuntimeException("无法启动服务，{$method} 方法未找到");
            }
        }
    }

    /**
     * 以递归方式查找指定 pid 下进程 pid 树
     */
    protected function findServicePids($pid, &$pids = [])
    {
        exec("ps -A -o pid,ppid | grep {$pid}", $output);

        foreach ($output as $line) {
            $data      = explode(' ', trim($line, ' '));
            $childPid  = array_shift($data);
            $parentPid = array_pop($data);

            if ($childPid != $pid) {
                $this->findServicePids($childPid, $pids);
            }

            $pids[$childPid] = $parentPid;
        }
    }

    /**
     * 返回当前服务进程 ID 树
     */
    public function servicePids()
    {
        if ($this->pid() == 0) {
            return [];
        }

        $tree = [];
        $this->findServicePids($this->pid(), $tree);

        return $tree;
    }

    /**
     * 返回当前服务运行状态
     * 运行中返回 true，否则返回 false
     */
    public function isRunning()
    {
        if ($this->servicePids()) {
            return true;
        } else {
            return false;
        }
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

    /**
     * 停止服务（强制）
     */
    public function shutdown(): bool
    {
        $pidtree = $this->servicePids();
        if (! $pidtree) {
            return true;
        }

        exec('kill -9 ' . implode(' ', array_keys($pidtree)));
        $this->waitForStop();

        return true;
    }

    /**
     * 返回当前项目工作目录路径
     *
     * 默认情况下会在与项目目录平行的位置作为默认运行目录
     * 如果权限缺失，将以 sys_get_temp_dir 作为默认目录
     *
     * @return string
     */
    public function defaultRunDirectory(): string
    {
        $path = rtrim((getcwd() ? getcwd() : strstr(__DIR__, 'vendor', true)), '/') . '/../';
        if (is_dir($path) && is_writable($path)) {
            return $path;
        }

        $path = sys_get_temp_dir() . '/' . md5(__DIR__) . '/';
        if (! is_dir($path)) {
            mkdir($path, 0755, true);
        }

        if (! is_dir($path)) {
            throw new RuntimeException('获取服务默认运行目录失败，请检查相关目录权限');
        }

        return $path;
    }

    /**
     * 返回默认 pid file 文件路径
     */
    public function defaultPidFilePath(): string
    {
        return $this->defaultRunDirectory() . $this->name() . '.pid';
    }

    /**
     * 返回默认服务运行日志文件路径
     */
    public function defaultLogPath(): string
    {
        return $this->defaultRunDirectory() . $this->name() . '.log';
    }

    /**
     * 服务启动，所有子类均应在各自的方法体内执行服务实例启动
     *
     * @return void
     */
    abstract public function start(int $port = 9527, string $host = '127.0.0.1');
}
