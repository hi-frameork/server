<?php
namespace AbstractSwooleServer\Http;

use RuntimeException;
use Stubs\SwooleHttpServer;

class CommonCest
{
    protected $setting = [
        'swoole' => [
            'daemonize' => true,
        ]
    ];

    public function _after()
    {
        $this->createServer($this->setting)->stop();
    }

    protected function startServer($setting)
    {
        $server = new SwooleHttpServer($setting);

        if ($server->isRunning()) {
            throw new RuntimeException('服务启动失败');
        }
        
        $projectDir = strstr(__DIR__, 'tests', true);
        $script     = $projectDir . 'tests/_support/Stubs/script/swoole-http-server.php';
        $data       = str_replace('"', '\"', json_encode($setting));
        $data       = str_replace(',', '\,', $data);
        $cmd        = "php {$script} -arg={$data}";
        // echo $cmd, PHP_EOL;
        exec($cmd, $output, $return);

        if ($return !== 0) {
            echo "\n\nError output: ", PHP_EOL;
            array_map(function ($line) { echo $line . "\n"; }, $output);
            exit($return);
        }

        // 等待 server 启动再进行后续业务
        $server->waitForStart();

        return $server;
    }

    protected function createServer($setting)
    {
        return new SwooleHttpServer($setting);
    }
}
