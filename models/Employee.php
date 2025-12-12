<?php
class Employee{
    private $conn;
    public $id;
    public $user_id;
    public $name;
    public $phone;
    public $address;
    public $image;
    public function __construct($db){
        $this->conn = $db;
    }
    public function getById(){
        $query = "SELECT * FROM employees WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            $this->name = $row['name'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->user_id = $row['user_id'];
            return true;
        }
        return false;
    }
    public function create(){
        $query = "INSERT INTO employees SET name=:name, phone=:phone, address=:address, user_id=:user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':user_id', $this->user_id);
        if($stmt->execute()) return true;
        return false;
    }
    public function update(){
        $query = "UPDATE employees SET name=:name, phone=:phone, address=:address WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) return true;
        return false;
    }
    public function delete(){
        $query = "DELETE FROM employees WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        if($stmt->execute()) return true;
        return false;
    }
    public function getAll(){
        $query = "SELECT * FROM employees";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}


?>