<?php declare(strict_types=1);

namespace Hi\Server;

abstract class AbstructBuiltInServer extends AbstructServer
{
    protected function runHttpServer()
    {
        // 生成入口文件完整路径
        $separator     = DIRECTORY_SEPARATOR;
        $entryFilePath = rtrim($_SERVER['PWD'], $separator) . $separator . ltrim($_SERVER['SCRIPT_FILENAME'], $separator);

        // 拼接 PHP 内建 server 启动完整指令
        $command = sprintf(
            '%s -S %s:%s %s',
            $this->phpExecutable(),
            $this->host,
            $this->port,
            $entryFilePath
        );

        passthru($command, $status);
    }

    /**
     * 返回PHP可执行文件路径
     *
     * @return string|false
     */
    public function phpExecutable()
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

    public function restart(): void
    {
    }

    public function stop(bool $force = false): void
    {
    }
}
