<?php
class Voucher {
    private $conn;
    private $table = "vouchers";

    public $id;
    public $image;
    public $discount_value;
    public $description;
    public $start_date;
    public $end_date;
    public $quantity;
    public $code;

    public function __construct($db) { $this->conn = $db; }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " SET code=:code, discount=:discount_value, description=:description, start_date=:start_date, end_date=:end_date, quantity=:quantity, image=:image";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':discount_value', $this->discount_value);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':code', $this->code);

        if($stmt->execute()) return true;
        return false;
    }
    // (Các hàm getById, update, delete làm tương tự như Customer)
    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->discount_value = $row['discount_value'];
            $this->description = $row['description'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->quantity = $row['quantity'];
            $this->image = $row['image'];
            $this->code = $row['code'];
            return true;
        }
        return false;
    }
    public function update() {
        $query = "UPDATE " . $this->table . " SET code=:code, discount=:discount_value, description=:description, start_date=:start_date, end_date=:end_date, quantity=:quantity, image=:image WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':discount_value', $this->discount_value);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':start_date', $this->start_date);
        $stmt->bindParam(':end_date', $this->end_date);
        $stmt->bindParam(':quantity', $this->quantity);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':code', $this->code);

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