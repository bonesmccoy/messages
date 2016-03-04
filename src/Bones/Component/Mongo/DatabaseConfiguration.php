<?php


namespace Bones\Component\Mongo;


class DatabaseConfiguration
{

    public function __construct($config)
    {
        if (!isset($config['db'])) {
            throw new \InvalidArgumentException("Missing db configuration in config file.");
        }

        $config = $config['db'];
        if (!isset($config['db_name'])) {
            throw new \InvalidArgumentException("Missing db name on configuration");
        }
        $this->databaseName = $config['db_name'] ;
        $this->host = isset($config['host']) ? $config['host'] : 'localhost';
        $this->port = isset($config['port']) ? $config['port'] : '27017';
        $this->username = isset($config['username']) ? $config['username'] : '';
        $this->password = isset($config['password']) ? $config['password'] : '';
        $this->connect = isset($config['connect']) ? $config['connect'] : '';
    }

    public function getConnectionUrl()
    {
        return sprintf("mongodb://%s%s%s%s/%s",
            ($this->username) ? "{$this->username}:" : '',
            ($this->password) ? "{$this->password}:" : "",
            $this->host,
            ":{$this->port}",
            $this->databaseName
        );
    }

    public function getConnectionOptions()
    {
        return array(
            'connect' => $this->connect
        );
    }

    /**
     * @return string
     */
    public function getConnect()
    {
        return $this->connect;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @return string
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

} 
