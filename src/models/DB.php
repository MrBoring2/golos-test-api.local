<?php

namespace Models;

use PDO;

class DB
{
    private $host = 'MySQL-8.4';
    private $user = 'root';
    private $pass = '';
    private $dbname = 'golos-test-db';

    public function connect()
    {
        $conn_str = "mysql:host=$this->host;dbname=$this->dbname";
        $conn = new PDO($conn_str, $this->user, $this->pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $conn;
    }
}