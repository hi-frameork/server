<?php declare(strict_types=1);

namespace Hi\Server;

interface ServerInterface
{
    /**
     * 版本号
     */
    const VERSION = '0.0.1';

    /**
     * 在指定端口与地址启动监听
     * @return void
     */
    public function start(callable $requestHandle, callable $taskHandle);

    /**
     * 重启服务
     */
    public function restart();

    /**
     * 停止服务
     */
    public function stop(bool $force = false);

    /**
     * 返回 Server 组件版本号
     */
    public function version(): string;
}
