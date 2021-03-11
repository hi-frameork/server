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
        $server = $this->createServer($setting);

        if ($server->isRunning()) {
            throw new RuntimeException('服务运行中，启动失败');
        }
        
        $projectDir = $server->defaultRunDirectory();
        $script     = $projectDir . '/server/tests/_support/Stubs/script/swoole-http-server.php';
        
        // 拼接要传入的特定参数
        $data = str_replace('"', '\"', json_encode($setting));
        $data = str_replace(',', '\,', $data);

        // 启动服务
        // echo $cmd, PHP_EOL;
        $cmd = "php {$script} -arg={$data}";
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
