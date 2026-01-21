<?php
require_once __DIR__ . '/../models/Customer.php';
require_once __DIR__ . '/AuthMiddleware.php';

class CustomerController {
    private $model;
    private $authMiddleware;

    public function __construct($db) { 
        $this->model = new Customer($db); 
        $this->authMiddleware = new AuthMiddleware($db);
    }

    public function processRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // --- 1. CẤU HÌNH CORS VÀ HEADER ---
        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

        // Xử lý Preflight request cho trình duyệt
        if ($method == 'OPTIONS') {
            http_response_code(200);
            exit();
        }

        // --- 2. ĐIỀU HƯỚNG METHOD ---
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

    // --- 3. CÁC HÀM XỬ LÝ ---

    private function getAll() {
        // $this->authMiddleware->isAuthenticated(); // Bật nếu cần kiểm tra đăng nhập
        
        $stmt = $this->model->getAll();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($customers);
    }

    private function getOne($id) {
        $this->model->id = $id;
        if($this->model->getById()) {
            // Bảo mật: Xóa password khỏi kết quả trả về
            unset($this->model->password); 
            echo json_encode($this->model);
        } else {
            http_response_code(404); 
            echo json_encode(["message" => "Khách hàng không tồn tại"]);
        }
    }

    private function create() {
        // $this->authMiddleware->isAuthenticatedAdmin(); // Bật nếu chỉ Admin được tạo Customer

        $data = json_decode(file_get_contents("php://input"));
        
        // Validate dữ liệu cơ bản
        if(empty($data->name) || empty($data->phone)) {
            http_response_code(400);
            echo json_encode(["message" => "Tên và Số điện thoại là bắt buộc"]);
            return;
        }

        // --- MAP DỮ LIỆU (Input JSON -> Model Properties) ---
        // Lưu ý: Frontend gửi 'name', DB lưu 'fullname'
        $this->model->fullname = $data->name; 
        $this->model->phone = $data->phone;
        $this->model->email = $data->email ?? '';
        $this->model->address = $data->address ?? '';
        $this->model->image = $data->image ?? null;

        // --- LOGIC TỰ ĐỘNG CHO KHÁCH HÀNG ---
        // 1. Username: Lấy số điện thoại làm username đăng nhập
        $this->model->username = $data->username ?? $data->phone;

        // 2. Password: Nếu không gửi kèm, mặc định là 123456
        $this->model->password = $data->password ?? '123456';

        // 3. Role: Luôn luôn là 'customer'
        $this->model->role = 'customer';

        if($this->model->create()) {
            http_response_code(201); 
            echo json_encode([
                "message" => "Đăng ký khách hàng thành công",
                "username" => $this->model->username
            ]);
        } else {
            http_response_code(503); 
            echo json_encode([
                "message" => "Thêm thất bại. SĐT hoặc Email có thể đã tồn tại.",
                "error_code" => "DUPLICATE_ENTRY"
            ]);
        }
    }

    private function update($id) {
        // $this->authMiddleware->isAuthenticated();

        $data = json_decode(file_get_contents("php://input"));
        
        $this->model->id = $id;
        
        // Map dữ liệu cập nhật
        $this->model->fullname = $data->name; // Frontend gửi 'name'
        $this->model->phone = $data->phone;
        $this->model->address = $data->address ?? '';
        $this->model->image = $data->image ?? null;
        $this->model->email = $data->email ?? '';
        
        // Không cập nhật Role, Username, Password ở đây (để đảm bảo an toàn)

        if($this->model->update()) {
            echo json_encode(["message" => "Cập nhật thông tin thành công"]);
        } else {
            http_response_code(503); 
            echo json_encode(["message" => "Cập nhật thất bại"]);
        }
    }

    private function delete($id) {
        // $this->authMiddleware->isAuthenticatedAdmin(); // Chỉ admin mới được xóa

        $this->model->id = $id;
        if($this->model->delete()) {
            echo json_encode(["message" => "Đã xóa khách hàng"]);
        } else {
            http_response_code(503); 
            echo json_encode(["message" => "Xóa thất bại"]);
        }
    }
}
?>