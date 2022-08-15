<?php

namespace Hi\Server;

trait ServerTrait
{
    /**
     * 注册服务响应回调事件
     */
    protected function collectionEventHandle(): void
    {
        foreach (get_class_methods($this) as $method) {
            /** @var string $method */
            if (substr($method, 0, 2) === 'on' && is_callable([$this, $method])) {
                /** @var array<string, string> $target */
                $target = [static::class, $method];
                $this->eventHandle[lcfirst(substr($method, 2))] = $target;
            }
        }
    }

    /**
     * 返回进程 id
     */
    public function pid(): int
    {
        $pidFile = $this->config->getPidFile();
        if (! is_file($pidFile)) {
            return 0;
        }

        $pid = @file_get_contents($pidFile);
        if (! $pid) {
            return 0;
        }

        return (int) $pid;
    }

    /**
     * 返回当前服务所有子进程
     *
     * @return int[]
     */
    public function childPids(): array
    {
        if ($this->pid() == 0) {
            return [];
        }

        /** @var array<int, int> */
        $pids = [];
        $this->findChildPids($this->pid(), $pids);

        /** @var int[] $result */
        $result = array_keys($pids);

        return $result;
    }

    /**
     * 以递归方式查找指定 pid 下进程 pid 树
     *
     * @param int $ppid
     * @param array<int, int> $pids
     */
    protected function findChildPids($ppid, &$pids = []): void
    {
        exec("ps -A -o pid,ppid | awk '$2==${ppid} {print $1}'", $output);

        foreach ($output as $pid) {
            /** @var int $pid */
            if ($pid != $ppid) {
                $this->findChildPids($pid, $pids);
            }
            $pids[$pid] = $ppid;
        }
    }

    /**
     * 判断当前进程/服务是否正在运行中
     */
    protected function isRunning(): bool
    {
        // @todo 应该加上对服务端口运行检测
        if ($this->childPids()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 阻塞等待进程启动
     */
    protected function waitForStart(): void
    {
        $count = 0;
        while (true) {
            if (sleep(1) || $count++ > 30) {
                throw new ServerException('操作失败，未检测到服务成功启动');
            }
            if ($this->isRunning() === true) {
                break;
            }
        }
    }

    /**
     * 阻塞等待进程停止
     */
    protected function waitForStop(): void
    {
        $count = 0;
        while (true) {
            if (sleep(1) || $count++ > 30) {
                throw new ServerException('操作失败，未检测到服务成功停止');
            }
            if ($this->isRunning() === false) {
                break;
            }
        }
    }
}
