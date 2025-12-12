<?php
class Customer {
    private $conn;
    private $table = "customers";

    public $id;
    public $user_id;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $image;

    public function __construct($db) { $this->conn = $db; }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->user_id = $row['user_id'];
            $this->image = $row['image'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " SET name=:name, email=:email, phone=:phone, address=:address, user_id=:user_id, image=:image";
        $stmt = $this->conn->prepare($query);

        // Bind params (Nhớ clean data nếu cần)
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':image', $this->image);

        if($stmt->execute()) return true;
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " SET name=:name, email=:email, phone=:phone, address=:address, image=:image WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) return true;
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) return true;
        return false;
    }
}
?>