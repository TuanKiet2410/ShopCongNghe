<?php
class Invoice {
    private $conn;
    private $table = "invoices";

    public $id;
    public $user_id;
    public $voucher_id;
    public $payment_method;
    public $status;
    public $total_money;
    public $created_at;

    public function __construct($db) { $this->conn = $db; }

    public function getAll() {
        // Có thể JOIN với bảng users để lấy tên người mua
        $query = "SELECT i.*, u.username FROM " . $this->table . " i LEFT JOIN users u ON i.user_id = u.id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " SET user_id=:user_id, voucher_id=:voucher_id, payment_method=:payment_method, status=:status, total_money=:total_money";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':voucher_id', $this->voucher_id);
        $stmt->bindParam(':payment_method', $this->payment_method);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':total_money', $this->total_money);

        if($stmt->execute()) return true;
        return false;
    }
    
    // Hàm cập nhật trạng thái đơn hàng (Duyệt đơn, Hủy đơn)
    public function updateStatus() {
        $query = "UPDATE " . $this->table . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) return true;
        return false;
    }
    public function getOne(){
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt;
    }
}
?>