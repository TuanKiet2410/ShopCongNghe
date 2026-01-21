<?php
class InvoiceDetail {
    private $conn;
    private $table = "invoice_details";

    // 1. CẬP NHẬT THUỘC TÍNH (Khớp với cột trong Database)
    public $id;
    public $id_invoice; // Đổi từ invoice_id -> id_invoice
    public $id_product; // Đổi từ product_id -> id_product
    public $quantity;
    
    // Lưu ý: Bảng mới của bạn không có unit_price, nên bỏ dòng này đi
    // public $unit_price; 

    public function __construct($db){
        $this->conn = $db;
    }

    // Lấy danh sách chi tiết theo ID hóa đơn
    public function getByInvoiceId(){
        // 2. SỬA CÂU SQL (dùng id_invoice và id_product)
        // Lấy thêm p.price vì trong bảng chi tiết không còn lưu giá nữa
        $query = "SELECT d.*, p.name as product_name, p.image as product_image, p.price as product_price
                  FROM " . $this->table . " d
                  LEFT JOIN products p ON d.id_product = p.id
                  WHERE d.id_invoice = ?";
              
        $stmt = $this->conn->prepare($query);
        
        // Bind tham số (dùng thuộc tính mới id_invoice)
        $stmt->bindParam(1, $this->id_invoice);
        
        $stmt->execute();
        return $stmt;
    }

    public function getAll(){
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>