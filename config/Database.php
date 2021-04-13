<?php

class Database
{
    private string $host = 'localhost';
    private string $user = 'root';
    private string $pass = 'root';
    private string $db = 'promma';
    private int $port = 8889;

    public mysqli $conn;

    public function getConnection(): mysqli
    {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        } catch (Exception $exception) {
            echo 'DB connection failed' . $exception->getMessage();
        }

        return $this->conn;
    }
}

?>