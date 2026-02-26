<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class Database {
    private $host = "localhost";
    private $user = "root";
    private $password = "admin";
    private $dbname = "resume_analyzer";
    public $conn;

    public function connect() {
        try {
            $this->conn = new mysqli(
                $this->host,
                $this->user,
                $this->password,
                $this->dbname
            );
            $this->conn->set_charset("utf8mb4");
            return $this->conn;
        } catch (Exception $e) {
            die("Database Connection Failed");
        }
    }
}