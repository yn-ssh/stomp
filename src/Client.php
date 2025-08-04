<?php
/**
 * @Author SSH
 * @Email 694711507@qq.com
 * @Date 2025/8/5 00:15
 * @Description
 */
namespace ssh\Stomp;

use Workerman\Stomp\Client as StompClient;

/**
 * Class Stomp
 * @package support
 *
 * Strings methods
 * @method static void send($queue, $body, array $headers = [])
 */
class Client
{

    /**
     * @var Client[]
     */
    protected static $_connections = null;

    /**
     * @var array
     */
    protected $_queue = [];

    /**
     * @var StompClient
     */
    protected $_client;

    /**
     * Client constructor.
     * @param $host
     * @param array $options
     */
    public function __construct($host, $options = [])
    {
        $this->_client = new StompClient($host, $options);
        $this->_client->onConnect = function ($client) {
            foreach ($this->_queue as $item) {
                $client->{$item[0]}(... $item[1]);
            }
            $this->_queue = [];
        };
        $this->_client->connect();
    }

    /**
     * @param $name
     * @param $arguments
     *
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if ($this->_client->getState() != StompClient::STATE_ESTABLISHED) {
            if (in_array($name, [
                'subscribe',
                'subscribeWithAck',
                'unsubscribe',
                'send',
                'ack',
                'nack',
                'disconnect'])) {
                $this->_queue[] = [$name, $arguments];
                return null;
            }
        }
        return $this->_client->{$name}(...$arguments);
    }

    /**
     * @param string $name
     * @return Client
     */
    public static function connection($name = 'default',$config = null) {
        if (!isset(static::$_connections[$name])) {
            if(empty($config)){
                $config = config('stomp', config('plugin.webman.stomp.stomp', []));
            }else{
                $config = config($config, []);
            }

            if (!isset($config[$name])) {
                throw new \RuntimeException("Stomp connection $name not found");
            }
            $host = $config[$name]['host'];
            $options = $config[$name]['options'];
            $client = new static($host, $options);
            static::$_connections[$name] = $client;
        }
        return static::$_connections[$name];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::connection('default')->{$name}(... $arguments);
    }
}
