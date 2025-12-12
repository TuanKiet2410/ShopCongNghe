<?php
class Database {
    // --- KHAI BÁO CÁC THÔNG SỐ CẤU HÌNH ---

    // 1. Địa chỉ máy chủ chứa Database. 
    // Dùng '
private $host = "127.0.0.1";
private $port = "3308";
private $db_name = "shoptk";
private $username = "root";
private $password = "";
public $conn;
public function connect() {
    $this->conn = null;

    try {
        $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;

        $this->conn = new PDO(
            $dsn,
            $this->username,
            $this->password
        );

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->exec("set names utf8");

    } catch(PDOException $e) {
        echo "Lỗi kết nối DB: " . $e->getMessage();
    }

    return $this->conn;
}    
}


?>