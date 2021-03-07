<?php declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructBuiltInServer extends AbstructServer
{
    public function restart(bool $force = false)
    {
    }

    public function stop()
    {
    }

    protected function runHttpServer()
    {
        // 生成入口文件完整路径
        $entryFilePath = rtrim($_SERVER['PWD'], DIRECTORY_SEPARATOR) 
            . DIRECTORY_SEPARATOR 
            . ltrim($_SERVER['SCRIPT_FILENAME'], DIRECTORY_SEPARATOR)
        ;

        /**
         * 拼接 PHP 内建 server 启动完整指令
         *
         * 命令输出示例(shell)：
         *  /usr/bin/php -S 127.0.0.1:8000 entry.php
         */
        $command = sprintf(
            '%s -S %s:%s %s',
            $this->phpExecutable(),
            $this->host,
            $this->port,
            $entryFilePath
        );

        passthru($command);
    }

    /**
     * 返回PHP可执行文件路径
     *
     * @return string|false
     */
    protected function phpExecutable()
    {
        if ($php = getenv('PHP_BINARY')) {
            if (!is_executable($php)) {
                $command = '\\' === \DIRECTORY_SEPARATOR ? 'where' : 'command -v';
                if ($php = strtok(exec($command.' '.escapeshellarg($php)), PHP_EOL)) {
                    if (!is_executable($php)) {
                        return false;
                    }
                } else {
                    return false;
                }
            }

            return $php;
        }

        if ($php = getenv('PHP_PATH')) {
            if (!@is_executable($php)) {
                return false;
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

        return false;
    }
}
