<?php

namespace Hi\Server;

/**
 * PHP 内建 Webserver 抽象类
 * 为 HTTP、TCP、UDP 等服务提供基类方法
 *
 * 请注意：
 *   此模式下运行容器只能用于本地开发时使用
 *   因性能不佳，严禁在生产环境使用！
 */
abstract class BuiltinServer extends Server
{
    /**
     * @inheritdoc
     */
    public function start(): void
    {
        if ('cli' === php_sapi_name()) {
            $this->runHttpServer();
        } else {
            $this->handle();
        }
    }

    /**
     * 在环境内 host 与 port 上启动内建 Webserver
     *
     * 相当于执行以下命令：
     *  php -S 127.0.0.1:9527 entry.php
     *
     *  @return void
     */
    protected function runHttpServer()
    {
        // 拼接入口文件完整路径
        $entryFilePath = rtrim($_SERVER['PWD'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR ;

        // 如果存在 public/index.php 文件，将其作为请求u入口文件
        $indexEntityFile = $entryFilePath . 'public' . DIRECTORY_SEPARATOR . 'index.php';
        if (is_file($indexEntityFile)) {
            $entryFile = $indexEntityFile;
        } else {
            $entryFile = $entryFilePath . ltrim($_SERVER['SCRIPT_FILENAME'], DIRECTORY_SEPARATOR);
        }

        // 拼接 PHP 内建 Webserver 启动指令
        $command = sprintf('%s -S %s:%s %s',
            $this->phpExecutable(),
            $this->config->getHost(),
            $this->config->getPort(),
            $entryFile
        );

        passthru($command);
    }

    /**
     * 返回PHP可执行文件路径
     * 如果无法确定 PHP 可执行文件路径，返回空字符串
     */
    protected function phpExecutable(): string
    {
        if ($php = getenv('PHP_BINARY')) {
            if (!is_executable($php)) {
                $command = '\\' === \DIRECTORY_SEPARATOR ? 'where' : 'command -v';
                if ($php = strtok(exec($command.' '.escapeshellarg($php)), PHP_EOL)) {
                    if (!is_executable($php)) {
                        return '';
                    }
                } else {
                    return '';
                }
            }

            return $php;
        }

        if ($php = getenv('PHP_PATH')) {
            if (!@is_executable($php)) {
                return '';
            }

            return $php;
        }

        if ($php = getenv('PHP_PEAR_PHP_BIN')) {
            if (@is_executable($php)) {
                return $php;
            }
        }

        if (@is_executable($php = PHP_BINDIR.('\\' === \DIRECTORY_SEPARATOR ? '\\php.exe' : '/php'))) {
            return $php;
        }

        return '';
    }

    /**
     * 执行请求处理
     */
    abstract protected function handle(): void;
}
