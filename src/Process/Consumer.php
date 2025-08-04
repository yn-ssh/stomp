<?php
/**
 * @Author SSH
 * @Email 694711507@qq.com
 * @Date 2025/8/5 00:15
 * @Description
 */

namespace ssh\Stomp\Process;

use support\Container;
use Workerman\Stomp\Client as StompClient;
use ssh\Stomp\Client;

/**
 * Class StompConsumer
 * @package process
 */
class Consumer
{
    /**
     * @var string
     */
    protected $_consumerDir = '';

    /**
     * StompConsumer constructor.
     * @param string $consumer_dir
     */
    public function __construct($consumer_dir = '')
    {
        $this->_consumerDir = $consumer_dir;
    }

    /**
     * onWorkerStart.
     */
    public function onWorkerStart()
    {
        $dir_iterator = new \RecursiveDirectoryIterator($this->_consumerDir);
        $iterator = new \RecursiveIteratorIterator($dir_iterator);
        foreach ($iterator as $file) {
            if (is_dir($file)) {
                continue;
            }
            $fileinfo = new \SplFileInfo($file);
            $ext = $fileinfo->getExtension();
            if ($ext === 'php') {
                $class = str_replace('/', "\\", substr(substr($file, strlen(base_path())), 0, -4));
                if (!is_a($class, 'Webman\Stomp\Consumer', true)) {
                    continue;
                }
                $consumer = Container::get($class);
                $connection_name = $consumer->connection ?? 'default';
                $queue = $consumer->queue;
                $ack   = $consumer->ack ?? 'auto';
                $config=$consumer->config??'';
                $connection = Client::connection($connection_name,$config);
                $cb = function ($client, $package, $ack) use ($consumer) {
                    \call_user_func([$consumer, 'consume'], $package['body'], $ack, $client);
                };
                $connection->subscribe($queue, $cb, ['ack' => $ack]);
                /*if ($connection->getState() == StompClient::STATE_ESTABLISHED) {
                    $connection->subscribe($queue, $cb, ['ack' => $ack]);
                } else {
                    $connection->onConnect = function (Client $connection) use ($queue, $ack, $cb) {
                        $connection->subscribe($queue, $cb, ['ack' => $ack]);
                    };
                }*/
            }
        }

    }
}
