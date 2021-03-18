<?php declare(strict_types=1);

namespace Hi\Server\Runtime;

interface ServerInterface
{
    /**
     * 版本号
     */
    const VERSION = '0.0.1';

    /**
     * 指定服务运行所在端口
     */
    public function withPort(int $port);

    /**
     * 指定服务运行所在 host
     */
    public function withHost(string $host);

    /**
     * 在指定端口与地址启动监听
     */
    public function start();

    /**
     * 平滑停止服务
     */
    public function stop();

    /**
     * 强制重启服务
     */
    public function restart();

    /**
     * 平滑重启服务
     */
    public function reload();

    /**
     * 强行停止服务
     */
    public function shutdown();
}
