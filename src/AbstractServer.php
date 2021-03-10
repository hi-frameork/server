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
use function exec;


abstract class AbstractServer
{
    /**
     * 默认结构： 
     *  [
     *      'host'      => null,
     *      'port'      => null,
     *      'name'      => null,
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
     * 返回服务当前配置
     */
    public function config(): array
    {
        return $this->config;
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

        // 对于不同 server 运行时，在配置中进行独立便于维护
        // 如果为传入对应配置，则使用 server 默认配置
        $this->config['swoole']    = $config['swoole'] ?? [];
        $this->config['workerman'] = $config['workerman'] ?? [];
    }

    /**
     * 以递归方式查找指定 pid 下进程 pid 树
     */
    protected function findPidTreeByMasterPid($pid, &$pids = [])
    {
        exec("ps -A -o pid,ppid | grep {$pid}", $output);

        foreach ($output as $line) {
            $data      = explode(' ', trim($line, ' '));
            $childPid  = array_shift($data);
            $parentPid = array_pop($data);

            if ($childPid != $pid) {
                $this->findPidTreeByMasterPid($childPid, $pids);
            }

            $pids[$childPid] = $parentPid;
        }
    }

    /**
     * 返回服务进程 ID 树
     */
    public function servicePidTree($masterPid)
    {
        if ($masterPid == 0) {
            return [];
        }

        $pidTree = [];
        $this->findPidTreeByMasterPid($masterPid, $pidTree);

        return $pidTree;
    }

    /**
     * 服务启动，所有子类均应在各自的方法体内执行服务实例启动
     *
     * @return void
     */
    abstract public function start(int $port = 9527, string $host = '127.0.0.1');
}
