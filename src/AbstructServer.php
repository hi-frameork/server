<?php declare(strict_types=1);

namespace Hi\Server;

use InvalidArgumentException;

abstract class AbstructServer implements ServerInterface
{
    /**
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * @var int
     */
    protected $port = 8000;

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
        return static::VERSION;
    }

    /**
     * 检查端口，如果无效将会抛出异常
     *
     * @param int $port
     */
    public function filterPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('端口无效，取值范围应在 (1-65535) 区间');
        }
    }

    /**
     * 检查 server 服务配置
     */
    protected function processConfig(array $config): array
    {
        if (isset($config['host'])) {
            $this->host = $config['host'];
        }

        if (isset($config['port'])) {
            $this->port = $config['port'];
        }

        if (isset($config['name'])) {
            $this->name = $config['name'];
        }

        if (! isset($config['swoole'])) {
            $this->config['swoole'] = [];
        }

        if (! isset($config['workerman'])) {
            $this->config['workerman'] = [];
        }

        return $config;
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
}
