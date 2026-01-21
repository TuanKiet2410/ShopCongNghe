<?php
class Invoice {
    private $conn;
    private $table = "invoices";

    // 1. CÁC THUỘC TÍNH (Sửa lại cho khớp với cột trong Database)
    public $id;
    public $id_user;        // Đổi từ user_id -> id_user
    public $id_voucher;     // Đổi từ voucher_id -> id_voucher
    public $payment_type;   // Đổi từ payment_method -> payment_type
    public $status;
    public $total_money;
    public $created_at;

    // Biến tạm để chứa danh sách sản phẩm từ Cart gửi sang
    public $cart_items = []; 

    public function __construct($db) { 
        $this->conn = $db; 
    }

    // ---------------------------------------------------------
    // 2. GET ALL: Lấy danh sách đơn hàng (Kèm tên người mua)
    // ---------------------------------------------------------
    public function getAll() {
        // Sửa câu lệnh JOIN: i.id_user = u.id
        $query = "SELECT i.*, u.username, u.fullname 
                  FROM " . $this->table . " i 
                  LEFT JOIN users u ON i.id_user = u.id 
                  ORDER BY i.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // ---------------------------------------------------------
    // 3. CREATE: Tạo hóa đơn + Chi tiết hóa đơn (Transaction)
    // ---------------------------------------------------------
    public function create() {
        try {
            if (empty($this->cart_items)) {
                throw new Exception("Giỏ hàng rỗng, không thể tạo đơn hàng.");
            }

            // A. Bắt đầu Transaction (Đảm bảo tạo Hóa đơn xong mới tạo Chi tiết)
            $this->conn->beginTransaction();

            // --- BƯỚC 1: TẠO HÓA ĐƠN (INVOICES) ---
            // Sửa tên cột cho khớp DB: id_user, id_voucher, payment_type
            $query = "INSERT INTO " . $this->table . " 
                      SET 
                        id_user = :id_user, 
                        id_voucher = :id_voucher, 
                        payment_type = :payment_type, 
                        status = :status, 
                        total_money = :total_money";
            
            $stmt = $this->conn->prepare($query);

            // Xử lý null cho voucher nếu không có
            if(empty($this->id_voucher)) $this->id_voucher = null;

            $stmt->bindValue(':id_user', $this->id_user);
            $stmt->bindValue(':id_voucher', $this->id_voucher);
            $stmt->bindValue(':payment_type', $this->payment_type);
            $stmt->bindValue(':status', $this->status);
            $stmt->bindValue(':total_money', $this->total_money);

            if (!$stmt->execute()) {
                throw new Exception("Lỗi tạo Invoice: " . implode(" ", $stmt->errorInfo()));
            }

            // Lấy ID hóa đơn vừa tạo
            $invoice_id = $this->conn->lastInsertId();
            $this->id = $invoice_id;

            // --- BƯỚC 2: TẠO CHI TIẾT HÓA ĐƠN (INVOICE_DETAILS) ---
            // Sửa tên cột: id_invoice, id_product
            // Lưu ý: Bảng invoice_details lúc nãy tạo KHÔNG CÓ cột unit_price, nên tôi bỏ ra.
            $queryDetail = "INSERT INTO invoice_details (id_invoice, id_product, quantity) 
                            VALUES (:id_invoice, :id_product, :quantity)";
            
            $stmtDetail = $this->conn->prepare($queryDetail);

            foreach ($this->cart_items as $item) {
                // Lấy ID sản phẩm (Check kỹ các trường hợp key)
                $p_id = $item['id'] ?? $item['product_id'] ?? null;
                $qty = $item['quantity'] ?? 1;

                if (empty($p_id)) {
                    throw new Exception("Dữ liệu sản phẩm lỗi: ID không tồn tại.");
                }

                $stmtDetail->bindValue(':id_invoice', $invoice_id);
                $stmtDetail->bindValue(':id_product', $p_id);
                $stmtDetail->bindValue(':quantity', $qty);
                
                if (!$stmtDetail->execute()) {
                    throw new Exception("Lỗi Insert Detail (Sản phẩm ID: $p_id): " . implode(" ", $stmtDetail->errorInfo()));
                }
            }

            // B. Commit Transaction (Lưu tất cả thay đổi)
            $this->conn->commit();
            return true;

        } catch (Throwable $e) { 
            // Nếu có lỗi, hoàn tác tất cả (Rollback)
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            
            // Ghi log lỗi để debug
            error_log("Invoice Error: " . $e->getMessage());
            // file_put_contents('debug_invoice_error.txt', $e->getMessage()); // Bật dòng này nếu muốn xem file log
            
            return false;
        }
    }
    
    // 4. CẬP NHẬT TRẠNG THÁI (Duyệt/Hủy)
    public function updateStatus() {
        $query = "UPDATE " . $this->table . " SET status=:status WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) return true;
        return false;
    }

    // 5. LẤY 1 HÓA ĐƠN (Kèm chi tiết sản phẩm - Nâng cao)
    public function getOne(){
        // Lấy thông tin hóa đơn
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $invoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($invoice) {
            // Lấy thêm danh sách sản phẩm của hóa đơn này
            $queryItems = "SELECT d.quantity, p.name, p.price, p.image 
                           FROM invoice_details d
                           JOIN products p ON d.id_product = p.id
                           WHERE d.id_invoice = ?";
            $stmtItems = $this->conn->prepare($queryItems);
            $stmtItems->bindParam(1, $this->id);
            $stmtItems->execute();
            
            $invoice['items'] = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
            return $invoice; // Trả về mảng dữ liệu đầy đủ
        }
        
        return null;
    }

    // 6. XÓA HÓA ĐƠN
    public function delete(){
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        if($stmt->execute()) return true;
        return false;
    }
}
?>