<?php declare(strict_types=1);

namespace Hi\Server;

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
     * {@inheritDoc}
     */
    public function version(): string
    {
        return static::VERSION;
    }

    /**
     * 返回PHP可执行文件路径
     *
     * @return string|false
     */
    public function findPhpExecutable()
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
