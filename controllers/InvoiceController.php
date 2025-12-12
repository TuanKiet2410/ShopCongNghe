<?php
require_once __DIR__ . '/../models/Invoice.php';

class InvoiceController {
    private $model;

    public function __construct($db) { $this->model = new Invoice($db); 
 
    
    }

    public function processRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'];
        header('Content-Type: application/json');

        switch ($method) {
            case 'GET':
                // Nếu có ID thì xem chi tiết, không thì xem hết
                $id ? $this->getOne($id) : $this->getAll(); 
                break;
            case 'POST':
                $this->create();
                break;
            case 'PUT':
                // Dùng để cập nhật trạng thái đơn hàng
                $this->updateStatus($id);
                break;
        }
    }

    private function getAll() {
        $stmt = $this->model->getAll();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));
        $this->model->user_id = $data->user_id;
        $this->model->voucher_id = $data->voucher_id ?? null; // Có thể null
        $this->model->payment_method = $data->payment_method; // COD, Banking
        $this->model->status = 'pending'; // Mặc định là chờ xử lý
        $this->model->total_money = $data->total_money;

        if($this->model->create()) {
            http_response_code(201); 
            echo json_encode(["message" => "Order Created Successfully"]);
        } else {
            http_response_code(503); echo json_encode(["message" => "Failed"]);
        }
    }

    private function updateStatus($id) {
        $data = json_decode(file_get_contents("php://input"));
        $this->model->id = $id;
        $this->model->status = $data->status; // Ví dụ: 'paid', 'shipping', 'cancelled'

        if($this->model->updateStatus()) echo json_encode(["message" => "Status Updated"]);
        else echo json_encode(["message" => "Failed"]);
    }
    private function getOne($id){
        $this->model->id = $id;
        $stmt = $this->model->getOne();
        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($row);
        } else {
            http_response_code(404); 
            echo json_encode(["message" => "Not Found"]);
        }
    }


}
?>