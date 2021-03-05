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
    protected $port = 9527;

    /**
     * 返回当前库版本号
     */
    public function version(): string
    {
        return static::VERSION;
    }

    /**
     * 处理服务启动端口
     */
    protected function processPort(int $port): void
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('端口取值区间必须在 1 ~ 65535 之间');
        }

        $this->port = $port;
    }

    /**
     * 处理服务启动 host
     */
    protected function processHost(string $host): void
    {
        $this->host = $host;
    }
}
