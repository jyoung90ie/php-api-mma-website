<?php

namespace helpers;

use PDO;
use PDOException;

if (!constant('DB_HOST')) {
    die ('DB credentials missing');
}

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $db = DB_NAME;
    private $port = DB_PORT;

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