<?php
require_once './models/Voucher.php';

class VoucherController {
    private $model;
    public function __construct($db) { $this->model = new Voucher($db); }
    public function processRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'];
        header('Content-Type: application/json');
        switch ($method) {
            case 'GET':
                $id ? $this->getOne($id) : $this->getAll();
                break;
            case 'POST':
                $this->create();
                break;
            case 'PUT':
                $this->update($id);
                break;
            case 'DELETE':
                $this->delete($id);
                break;
        }
    }
        private function create() {
        $data = json_decode(file_get_contents("php://input"));
        $this->model->discount_value = $data->discount_value;
        $this->model->description = $data->description;
        $this->model->start_date = $data->start_date; // Định dạng YYYY-MM-DD
        $this->model->end_date = $data->end_date;
        $this->model->quantity = $data->quantity;
        $this->model->image = $data->image ?? '';

        if($this->model->create()) {
             http_response_code(201); echo json_encode(["message" => "Voucher Created"]);
        }
    }
    private function getOne($id) {
        $this->model->id = $id;
        if($this->model->getById()) {
            echo json_encode($this->model);
        } else {
            http_response_code(404); echo json_encode(["message" => "Voucher Not Found"]);
        }
    }
    private function getAll() {
        $stmt = $this->model->getAll();
        $num = $stmt->rowCount();
        if($num > 0) {
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($rows);
        } else {
            http_response_code(404); echo json_encode(["message" => "Voucher Not Found"]);
        }
    }
    private function update($id) {
        $data = json_decode(file_get_contents("php://input"));
        $this->model->id = $id;
        $this->model->discount_value = $data->discount_value;
        $this->model->description = $data->description;
        $this->model->start_date = $data->start_date;
        $this->model->end_date = $data->end_date;
        $this->model->quantity = $data->quantity;
        $this->model->image = $data->image ?? '';

        if($this->model->update()) {
            echo json_encode(["message" => "Voucher Updated"]);
        } else {
            http_response_code(503); echo json_encode(["message" => "Update Failed"]);
        }
    }
    private function delete($id) {
        $this->model->id = $id;
        if($this->model->delete()) {
            echo json_encode(["message" => "Voucher Deleted"]);
        } else {
            http_response_code(503); echo json_encode(["message" => "Delete Failed"]);
        }
    }

}?>