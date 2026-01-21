<?php
require_once __DIR__ . '/../models/Employee.php';
require_once __DIR__ . '/AuthMiddleware.php';

class EmployeeController {
    private $model;
    private $authMiddleware;

    public function __construct($db) { 
        $this->model = new Employee($db); 
        $this->authMiddleware = new AuthMiddleware($db);
    }

    public function processRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Headers CORS
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

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
                $this->update($id);
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
        // $this->authMiddleware->isAuthenticatedAdmin(); // Bật lại khi cần bảo mật
        
        $stmt = $this->model->getAll();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getOne($id) {
        $this->model->id = $id;
        if($this->model->getById()) {
            echo json_encode($this->model);
        } else {
            http_response_code(404); 
            echo json_encode(["message" => "Not Found"]);
        }
    }

    private function create() {
        // $this->authMiddleware->isAuthenticatedAdmin();

        $data = json_decode(file_get_contents("php://input"));
        
        if(empty($data->name) || empty($data->phone)) {
            echo json_encode(["message" => "Tên và Số điện thoại là bắt buộc"]);
            return;
        }

        // --- MAP DỮ LIỆU SANG BẢNG USERS ---
        $this->model->fullname = $data->name; // Angular gửi name -> lưu vào fullname
        $this->model->phone = $data->phone;
        $this->model->address = $data->address ?? '';
        $this->model->image = $data->image ?? null;
        $this->model->email = $data->email ?? '';
        
        // --- LOGIC TỰ ĐỘNG ---
        // 1. Username: Tự lấy số điện thoại làm username
        $this->model->username = $data->phone; 
        
        // 2. Password: Mặc định là 123456 (Nhân viên có thể đổi sau)
        $this->model->password = '123456';
        
        // 3. Role: Mặc định là 'employee' (hoặc admin tùy bạn chọn)
        $this->model->role = $data->role ?? 'employee';

        if($this->model->create()) {
            http_response_code(201); 
            echo json_encode(["message" => "Thêm nhân viên thành công", "username" => $this->model->username]);
        } else {
            http_response_code(503); 
            echo json_encode([
                "message" => "Thêm thất bại. Có thể SĐT/Username đã tồn tại.",
                "hint" => "Username trong bảng users là UNIQUE."
            ]);
        }
    }

    private function update($id) {
        // $this->authMiddleware->isAuthenticatedAdmin();

        $data = json_decode(file_get_contents("php://input"));
        
        $this->model->id = $id;
        $this->model->fullname = $data->name;
        $this->model->phone = $data->phone;
        $this->model->address = $data->address ?? '';
        $this->model->image = $data->image ?? null;
        $this->model->email = $data->email ?? '';
        // Cho phép cập nhật quyền (role)
        $this->model->role = $data->role ?? 'employee';

        if($this->model->update()) {
            echo json_encode(["message" => "Cập nhật thành công"]);
        } else {
            http_response_code(503); 
            echo json_encode(["message" => "Cập nhật thất bại"]);
        }
    }

    private function delete($id) {
        // $this->authMiddleware->isAuthenticatedAdmin();
        $this->model->id = $id;
        if($this->model->delete()) {
            echo json_encode(["message" => "Đã xóa nhân viên"]);
        } else {
            http_response_code(503); 
            echo json_encode(["message" => "Xóa thất bại"]);
        }
    }
}
?>