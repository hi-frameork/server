<?php declare(strict_types=1);

namespace Hi\Server;

interface ServerInterface
{
    /**
     * 版本号
     */
    const VERSION = '0.0.1';

    /**
     * 返回 Server 组件版本号
     */
    public function version(): string;

    /**
     * 在指定端口与地址启动监听
     */
    public function start(int $port = 9527, string $host = '127.0.0.1');

    /**
     * 强制重启服务
     */
    public function restart();

    /**
     * 平滑重启服务
     */
    public function reload();

    /**
     * 平滑停止服务
     */
    public function stop();

    /**
     * 强行停止服务
     */
    public function shutdown();
}
