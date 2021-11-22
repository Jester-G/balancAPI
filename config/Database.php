<?php

class Database
{
    private $dsn = 'mysql:server=localhost;dbname=back_db';
    private $username = 'root';
    private $password = '';
    private $db;

    public function connect()
    {
        $this->db = null;

        try {
            $this->db = new \PDO($this->dsn, $this->username, $this->password);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            echo $e->getMessage();
            exit;
        }

        return $this->db;
    }
}