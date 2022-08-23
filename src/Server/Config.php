<?php

namespace Hi\Server;

use function sys_get_temp_dir;
use function md5;
use function array_key_exists;

class Config
{
    /**
     * @var array<string ,mixed>
     */
    protected $config = [];

    /**
     * Construct
     *
     * @param array<string, mixed> $config
     */
    /**
     * Construct
     *
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config['name'] = $this->processName($config['name'] ?? 'hi-server');
        $this->config['host'] = $this->processHost($config['host'] ?? '0.0.0.0');
        $this->config['port'] = $this->processPort($config['port'] ?? 9527);
        $this->config['pid_file'] = $this->processPidFile($config['pid_file'] ?? null);
        $this->config['log_file'] = $this->processLogFile($config['log_file'] ?? null);
        $this->config['swoole'] = $config['swoole'] ?? [];
        $this->config['workerman'] = $config['workerman'] ?? [];
    }

    /**
     * Name 预处理(验证)
     * @todo 对 name 进行合法性校验
     */
    public function processName(string $value): string
    {
        return $value;
    }

    /**
     * Host 地址预处理(验证)
     * @todo 对 host 进行正确性校验
     */
    public function processHost(string $value): string
    {
        return $value;
    }

    /**
     * 端口号预处理(验证)
     *
     * @throws ServerException 
     */
    public function processPort(int $value): int
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
    protected function processPidFile(?string $value): string
    {
        return $value 
            ?? $this->defaultDirectory() . $this->get('name') . '.pid';
    }

    /**
     * 预处理日志文件路径
     *
     * @throws ServerException 
     */
    protected function processLogFile(?string $value): string
    {
        return $value 
            ?? $this->defaultDirectory() . $this->get('name') . '.log';
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
     * 添加或替换指定 key 对应 value
     *
     * @param mixed $value
     */
    public function set(string $key, $value): void
    {
        $this->config[$key] = $value;
    }
}
