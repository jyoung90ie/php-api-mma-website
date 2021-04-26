<?php

namespace helpers;

use PDO;
use PDOException;

class Database
{
    private $host = 'localhost';
    private $user = 'root';
    private $pass = 'root';
    private $db = 'promma';
    private $port = 8889;

    private $connection = null;

    public function __construct()
    {
        $options = [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        try {
            $this->connection = new PDO("mysql:host=$this->host;port=$this->port;charset=utf8mb4;dbname=$this->db",
                $this->user, $this->pass, $options);
        } catch (PDOException $exception) {
            echo $exception->getMessage();
        }
    }

    public function getConnection(): ?PDO
    {
        return $this->connection;
    }
}

?>