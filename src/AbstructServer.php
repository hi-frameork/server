<?php declare(strict_types=1);

namespace Hi\Server;

use InvalidArgumentException;

abstract class AbstructServer
{
    /**
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * @var int
     */
    protected $port = 9527;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $name = 'hi-server';

    /**
     * @var callable
     */
    protected $handleRequest;

    /**
     * Server Construct.
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->processConfig($config);
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
     * 处理服务启动端口
     *
     * @return void
     */
    protected function processPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('端口无效，取值范围应 1 ~ 65535 之间');
        }

        $this->port = $port;
    }

    /**
     * 处理服务启动 host
     *
     * @return void
     */
    protected function processHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * 检查 server 服务配置
     */
    protected function processConfig(array $config): array
    {
        // 从配置中提取公共参数，用于后续 server 的快速创建
        $this->host = $config['host'] ?? $this->host;
        $this->port = $config['port'] ?? $this->port;

        // 服务名称（用户设置 http 服务进程名）
        $this->name = $config['name'] ?? $this->name;

        // 对于不同 server 运行时，在配置中进行独立便于维护
        // 如果为传入对应配置，则使用 server 默认配置
        $config['swoole']    = $config['swoole'] ?? [];
        $config['workerman'] = $config['workerman'] ?? [];

        return $config;
    }

    /**
     * 服务启动，所有子类均应在各自的方法体内执行服务实例启动
     *
     * @return void
     */
    abstract public function start(int $port = 9527, string $host = '127.0.0.1');
}
