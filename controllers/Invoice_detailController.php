<?php
require_once __DIR__ . './../models/invoice_detail.php';

class InvoiceDetailController {
    private $model;

    public function __construct($db) {
        $this->model = new InvoiceDetail($db);
    }

    public function processRequest($id_invoice) {
        // Chỉ xử lý method GET để xem chi tiết
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if ($id_invoice) {
                $this->getByInvoice($id_invoice);
            } else {
                // Nếu không có ID hóa đơn, có thể trả về lỗi hoặc lấy tất cả (tùy logic)
                $this->getAll();
            }
        } else {
            http_response_code(405);
        }
    }

    // Lấy chi tiết dựa trên ID Hóa Đơn
    private function getByInvoice($id_invoice) {
        // --- BƯỚC QUAN TRỌNG NHẤT ---
        // Trong Model bạn khai báo public $invoice_id, nên ở đây phải gán đúng vào nó.
        // KHÔNG ĐƯỢC GÁN: $this->model->id = $id_invoice (Sai)
        $this->model->id_invoice = $id_invoice; 

        // Gọi hàm
        $stmt = $this->model->getByInvoiceId();
        
        // Lấy dữ liệu dạng mảng
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Trả về JSON
        echo json_encode($result);
    }

    private function getAll() {
        $stmt = $this->model->getAll();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?>  