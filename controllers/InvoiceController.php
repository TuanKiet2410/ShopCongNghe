<?php
require_once __DIR__ . '/../models/Invoice.php';

class InvoiceController {
    private $model;

    public function __construct($db) { 
        $this->model = new Invoice($db); 
    }

    public function processRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'];
        
       

        if ($method == 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        switch ($method) {
            case 'GET':
                $id ? $this->getOne($id) : $this->getAll(); 
                break;
            case 'POST':
                $this->create();
                break;
            case 'PUT':
                $this->updateStatus($id);
                break;
            case 'DELETE':
                $this->delete($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(["message" => "Method Not Allowed"]);
                break;
        }
    }

    private function getAll() {
        $stmt = $this->model->getAll();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function create() {
        $input_data = json_decode(file_get_contents("php://input"));
        
        // Xử lý dữ liệu đầu vào (Support cả cấu trúc cũ và mới)
        $data = $input_data->invoices ?? $input_data;

        // 1. GÁN DỮ LIỆU VÀO MODEL (Lưu ý tên biến mới)
        // Frontend có thể gửi 'user_id' hoặc 'id_user', ta lấy cái nào có dữ liệu
        $this->model->id_user = $data->id_user ?? $data->user_id ?? null;
        
        // Voucher
        $this->model->id_voucher = $data->id_voucher ?? $data->voucher_id ?? null;
        
        // Payment Type (COD, Banking...)
        $this->model->payment_type = $data->payment_type ?? $data->payment_method ?? 'COD';
        
        $this->model->status = 'pending'; 
        $this->model->total_money = $data->total_money ?? 0;

        // 2. XỬ LÝ CART ITEMS
        if (isset($input_data->cart_items)) {
            // Chuyển object thành array
            $this->model->cart_items = json_decode(json_encode($input_data->cart_items), true);
        } else {
            $this->model->cart_items = []; 
        }

        // 3. GỌI HÀM CREATE CỦA MODEL
        if($this->model->create()) {
            http_response_code(201); 
            echo json_encode(["message" => "Tạo đơn hàng thành công!", "id" => $this->model->id]);
        } else {
            http_response_code(503); 
            echo json_encode([
                "message" => "Lỗi hệ thống, không lưu được đơn hàng.",
                "hint" => "Kiểm tra log server hoặc dữ liệu cart_items."
            ]);
        }
    }

    private function updateStatus($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!$id) {
             echo json_encode(["message" => "Thiếu ID đơn hàng"]);
             return;
        }

        $this->model->id = $id;
        $this->model->status = $data->status; // 'success', 'cancel'...

        if($this->model->updateStatus()) {
            echo json_encode(["message" => "Cập nhật trạng thái thành công"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Cập nhật thất bại"]);
        }
    }

    private function getOne($id){
        $this->model->id = $id;
        
        // Model getOne() mới trả về Mảng (Array) hoặc Null, KHÔNG PHẢI Statement
        $result = $this->model->getOne();

        if($result) {
            echo json_encode($result);
        } else {
            http_response_code(404); 
            echo json_encode(["message" => "Không tìm thấy đơn hàng"]);
        }
    }
    
    private function delete($id){
        $this->model->id = $id;
        if($this->model->delete()) {
            echo json_encode(["message" => "Đã xóa đơn hàng"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Xóa thất bại"]);
        }
    }
}
?>