<?php
class InvoiceDetail{
    private $conn;
    public $id;
    public $invoice_id;
    public $product_id;
    public $quantity;
    public $unit_price;
    public function __construct($db){
        $this->conn = $db;
    }
public function getByInvoiceId(){
    // Thêm JOIN để lấy tên và ảnh từ bảng products
    $query = "SELECT d.*, p.name as product_name, p.image as product_image 
              FROM invoice_details d
              LEFT JOIN products p ON d.product_id = p.id
              WHERE d.invoice_id = ?";
              
    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(1, $this->invoice_id);
    $stmt->execute();
    return $stmt;
}
    public function getAll(){
        $query = "SELECT * FROM invoice_details";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

}


?>