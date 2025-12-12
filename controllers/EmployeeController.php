<?php
require_once './models/Employee.php';

class EmployeeController{
    private $model;
    public function __construct($db) { $this->model = new Employee($db); }
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
    private function getAll() {
        $stmt = $this->model->getAll();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    private function getOne($id) {
        $this->model->id = $id;
        if($this->model->getById()) {
            echo json_encode($this->model);
        } else {
            http_response_code(404); echo json_encode(["message" => "Not Found"]);
        }
    }
    private function create() {
        $data = json_decode(file_get_contents("php://input"));
        $this->model->name = $data->name;
        $this->model->phone = $data->phone;
        $this->model->address = $data->address ?? '';
        $this->model->image = $data->image ?? null;

        if($this->model->create()) {
            http_response_code(201); echo json_encode(["message" => "Created"]);
        } else {
            http_response_code(503); echo json_encode(["message" => "Failed"]);
        }
    }
    private function update($id) {
        $data = json_decode(file_get_contents("php://input"));
        $this->model->id = $id;
        $this->model->name = $data->name;
        $this->model->phone = $data->phone;
        $this->model->address = $data->address ?? '';
        $this->model->image = $data->image ?? null;

        if($this->model->update()) {
            echo json_encode(["message" => "Updated"]);
        } else {
            http_response_code(503); echo json_encode(["message" => "Failed"]);
        }
    }
    private function delete($id) {
        $this->model->id = $id;
        if($this->model->delete()) {
            echo json_encode(["message" => "Deleted"]);
        } else {
            http_response_code(503); echo json_encode(["message" => "Failed"]);
        }
    }

}


?>