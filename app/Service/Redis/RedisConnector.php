<?php
declare(strict_types=1);

namespace app\Service\Redis;

use RuntimeException;
use Smf\ConnectionPool\Connectors\PhpRedisConnector;

class RedisConnector extends PhpRedisConnector
{

    /**
     * Connect to the specified Server and returns the connection resource
     * @param array $config
     * @return mixed
     */
    public function connect(array $config)
    {
        $connection = new RedisExtend();
        $ret = $connection->connect($config['host'], $config['port'], $config['timeout'] ?? 10);
        if ($ret === false) {
            throw new RuntimeException(sprintf('Failed to connect Redis server: %s', $connection->getLastError()));
        }
        if (isset($config['password'])) {
            $config['password'] = (string) $config['password'];
            if ($config['password'] !== '') {
                $connection->auth($config['password']);
            }
        }
        if (isset($config['database'])) {
            $connection->select($config['database']);
        }
        foreach ($config['options'] ?? [] as $key => $value) {
            $connection->setOption($key, $value);
        }
        $connection->initScript();
        return $connection;
    }
}
