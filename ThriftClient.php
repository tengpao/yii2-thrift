<?php

namespace tengpao\thrift;

use Thrift\ClassLoader\ThriftClassLoader;

class ThriftClient
{
    // 生成的gen-php的路径
    public $genDir = __DIR__ . '/gen-php';

    // 请求方式, 可选项: `http`,`socket`
    public $requestType = 'socket';

    // 请求方式为`http`有效
    public $httpName = 'THttpClient';
    public $uri = '';

    // 请求方式为`socket`有效
    public $socketName = 'TSocket';
    
    public $transportName = 'TBufferedTransport';
    public $protocolName = 'TBinaryProtocol';

    public $host = 'localhost';
    public $port = 9090;
    public $rBufSize = 1024;
    public $wBufSize = 1024;

    /**
     * @var array service配置
     * 
     * 每个service独立的配置项,一个service名对应一组配置
     * 以上配置都可以作为service可选独立配置(genDir除外),没有默认使用全局配置
     * 
     * 必选配置: `clientName` 包含全部命名空间的client类名
     * 新增可选配置: `defineName`为除clientName所在命名空间外所需注册的命名空间,可以为数组
     * 
     * 例:
     * ['idService' => ['defineName' => 'shared', 'clientName' => '\tutorial\CalculatorClient']]
     */
    public $serviceConfig = [];

    protected $socket;
    protected $transport;
    protected $protocol;

    public function init()
    {
    }

    public function __get($name)
    {
        if (!isset($this->serviceConfig[$name]) || !is_array($this->serviceConfig[$name]) || !isset($this->serviceConfig[$name]['clientName'])) {
            throw new \Exception('参数配置错误');
        }

        $config = $this->serviceConfig[$name];

        $loader = new ThriftClassLoader();

        // 当前service的cient类名（包含全部命名空间）
        $clientName = $config['clientName'];

        $defineName = explode('\\', ltrim($clientName, '\\'))[0];
        $loader->registerDefinition($defineName, $this->genDir);

        if (!empty($config['defineName'])) {
            if (is_string($config['defineName'])) {
                $loader->registerDefinition($config['defineName'], $this->genDir);
            } elseif (is_array($config['defineName'])) {
                foreach ($config['defineName'] as $item) {
                    $loader->registerDefinition($item, $this->genDir);
                }
            }
        }
        $loader->register();

        // 如果有单独配置则使用单独配置，否则使用全局配置
        $host = isset($config['host']) ? $config['host'] : $this->host;
        $port = isset($config['port']) ? $config['port'] : $this->port;
        $uri = isset($config['uri']) ? $config['uri'] : $this->uri;
        $rBufSize = isset($config['rBufSize']) ? $config['rBufSize'] : $this->rBufSize;
        $wBufSize = isset($config['wBufSize']) ? $config['wBufSize'] : $this->wBufSize;

        $transportName = '\\Thrift\\Transport\\' . (isset($config['transportName']) ? $config['transportName'] : $this->transportName);
        $protocolName = '\\Thrift\\Protocol\\' . (isset($config['protocolName']) ? $config['protocolName'] : $this->protocolName);

        if ((isset($config['requestType']) ? $config['requestType'] : $this->requestType) == 'http') {
            $httpName = '\\Thrift\\Transport\\' . (isset($config['httpName']) ? $config['httpName'] : $this->httpName);
            $this->socket = new $httpName($host, $port, $uri);
        } else {
            $socketName = '\\Thrift\\Transport\\' . (isset($config['socketName']) ? $config['socketName'] : $this->socketName);
            $this->socket = new $socketName($host, $port);
        }

        $this->transport = new $transportName($this->socket, $rBufSize, $wBufSize);
        $this->protocol = new $protocolName($this->transport);
        $this->transport->open();

        $this->$name = new $clientName($this->protocol);
        return $this->$name;

    }


    /**
     * 对象销毁关闭连接
     */
    public function __destruct()
    {
        if ($this->transport) {
            $this->transport->close();
        }
    }


}
