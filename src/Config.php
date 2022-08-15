<?php

namespace Hi\Server;

use function sys_get_temp_dir;
use function md5;
use function array_key_exists;

class Config
{
    /**
     * @var array{
            name: string,
            host: string,
            port: int,
            pid_file?: string,
            log_file?: string,
            workerman: array{},
            swoole: array{},
        }
     */
    protected $config = [
        'name' => 'hi-server',
        'host' => '0.0.0.0',
        'port' => 9527,
        'workerman' => [],
        'swoole' => [],
    ];

    /**
     * Construct
     *
     * @param array{
            name?: string,
            host?: string,
            port?: int,
            pid_file?: string,
            log_file?: string,
            workerman?: array{},
            swoole?: array{},
        } $config
     */
    public function __construct(array $config = [])
    {
        if (isset($config['name'])) {
            $this->config['name'] = $this->processName($config['name']);
        }

        if (isset($config['host'])) {
            $this->config['host'] = $this->processHost($config['host']);
        }

        if (isset($config['port'])) {
            $this->config['port'] = $this->processPort($config['port']);
        }

        if (isset($config['swoole'])) {
            $this->config['swoole'] = $config['swoole'];
        }

        if (isset($config['workerman'])) {
            $this->config['workerman'] = $config['workerman'];
        }

        // 如果未传入 pid_file 文件路径，取默认值
        $this->config['pid_file'] = $config['pid_file'] ?? $this->defaultPidFile();

        // 如果未传入 log_file 文件路径，取默认值
        $this->config['log_file'] = $config['log_file'] ?? $this->defaultLogFile();
    }

    /**
     * Name 预处理(验证)
     * @todo 对 name 进行合法性校验
     */
    protected function processName(string $value): string
    {
        return $value;
    }

    /**
     * Host 地址预处理(验证)
     * @todo 对 host 进行正确性校验
     */
    protected function processHost(string $value): string
    {
        return $value;
    }

    /**
     * 端口号预处理(验证)
     *
     * @throws ServerException 
     */
    protected function processPort(int $value): int
    {
        if ($value < 1 || $value > 65535) {
            throw new ServerException('端口无效，取值范围应 1 ~ 65535 之间');
        }

        return $value;
    }

    /**
     * 处理服务运行时目录
     */
    public function defaultDirectory(): string
    {
        return sys_get_temp_dir() . '/' . md5(__DIR__) . '/';
    }

    /**
     * 预处理进程 pid 文件路径
     *
     * @throws ServerException 
     */
    protected function defaultPidFile(): string
    {
        return $this->defaultDirectory() . $this->get('name') . '.pid';
    }

    /**
     * 预处理日志文件路径
     *
     * @throws ServerException 
     */
    protected function defaultLogFile(): string
    {
        return $this->defaultDirectory() . $this->get('name') . '.log';
    }

    /**
     * 返回指定 key 对应 value，如果 key 为空返回所有
     *
     * @return mixed
     * @throws ServerException 
     */
    public function get(string $key = null)
    {
        if (! $key) {
            return $this->config;
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        throw new ServerException("未找到 key[${key}] 对应 value");
    }

    /**
     * 返回服务端口
     *
     * @throws ServerException 
     */
    public function getPort(): int
    {
        return $this->get('port');
    }

    /**
     * 返回服务 Host
     *
     * @throws ServerException 
     */
    public function getHost(): string
    {
        return $this->get('host');
    }

    /**
     * 返回进程 PID 文件路径
     *
     * @throws ServerException 
     */
    public function getPidFile(): string
    {
        return $this->get('pid_file');
    }

    /**
     * 返回日志文件路径
     *
     * @throws ServerException 
     */
    public function getLogFile(): string
    {
        return $this->get('log_file');
    }
}
