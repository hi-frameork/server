<?php declare(strict_types=1);

namespace Hi\Server;

use InvalidArgumentException;
use RuntimeException;

abstract class AbstractServer
{
    /**
     * 默认结构： 
     *  [
     *      'host'        => null,
     *      'port'        => null,
     *      'swoole'      => [],
     *      'workerman'   => [],
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
     * @var Manager
     */
    protected $manager;

    /**
     * Server Construct.
     */
    public function __construct(array $config = [])
    {
        $this->processConfig($config);
        $this->manager = $this->createManager();
    }

    /**
     * 检查 server 服务配置
     */
    protected function processConfig(array $config)
    {
        if (isset($config['host'])) {
            $this->withHost($config['host']);
        }

        if (isset($config['port'])) {
            $this->withPort($config['port']);
        }

        // 服务进程名称
        $this->config['name']        = $config['name'] ?? 'hi-server';
        // 服务运行时所在目录
        $this->config['runtime_dir'] = $config['runtime_dir'] ?? '';
        // 对于不同 server 运行时，在配置中进行独立便于维护
        $this->config['swoole']      = $config['swoole'] ?? [];
        $this->config['workerman']   = $config['workerman'] ?? [];
    }

    /**
     * 返回服务进程管理实例
     */
    protected function createManager(): Manager
    {
        return new Manager(
            $this->config['name'],
            $this->config['runtime_dir']
        );
    }

    /**
     * 指定服务运行所在端口
     */
    public function withPort(int $port)
    {
        $this->checkPort($port);
        $this->config['port'] = $port;
        return $this;
    }

    /**
     * 指定服务运行所在 host
     */
    public function withHost(string $host)
    {
        $this->config['host'] = $host;
        return $this;
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
    protected function checkPort(int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException('端口无效，取值范围应 1 ~ 65535 之间');
        }
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
                '无法启动服务，必须在 eventHandle 中设置不同 runtime 对应服务事件'
            );
        }

        foreach ($this->eventHandle as $method) {
            if (! method_exists($this, $method)) {
                throw new RuntimeException("无法启动服务，{$method} 方法未找到");
            }
        }
    }

    /**
     * 返回服务当前配置
     * 此方法应返回完成服务所使用配置
     */
    public function config(): array
    {
        return array_merge($this->config, [
            'name' => $this->manager->name(),
            'runtime_dir' => $this->manager->runtimeDirectory(),
        ]);
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
     * 服务启动，所有子类均应在各自的方法体内执行服务实例启动
     *
     * @return void
     */
    abstract public function start();

    /**
     * 平滑停止服务
     */
    public function stop()
    {
        $this->manager->stop();
    }

    /**
     * 强制重启服务
     */
    public function restart()
    {
        $this->manager->shutdown();
        $this->start();
    }

    /**
     * 平滑重启服务
     */
    public function reload()
    {
        if ($this->manager->isRunning()) {
            $this->manager->reload();
        } else {
            $this->start();
        }
    }

    /**
     * 强行停止服务
     */
    public function shutdown()
    {
        $this->manager->shutdown();
    }
}
