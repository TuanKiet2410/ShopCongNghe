<?php
require_once __DIR__ . './../models/User.php';

class UserController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function processRequest($id) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Đặt header JSON cho toàn bộ phản hồi
        header('Content-Type: application/json');

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getOne($id);
                } else {
                    $this->getAll();
                }
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
                echo json_encode(["message" => "Method not allowed"]);
                break;
        }
    }

    // --- Logic chi tiết ---

    // 1. Lấy danh sách
    private function getAll() {
        $stmt = $this->userModel->getAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    }

    // 2. Lấy chi tiết 1 user
    private function getOne($id) {
        $this->userModel->id = $id;
        if($this->userModel->getById()) {
            echo json_encode([
                'id' => $this->userModel->id,
                'username' => $this->userModel->username,
                'role' => $this->userModel->role,
                'permission' => $this->userModel->permission, // --- THÊM ---
                'is_locked' => (int)$this->userModel->is_locked, // --- THÊM ---
                'created_at' => $this->userModel->created_at
            ]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
        }
    }

    // 3. Tạo User mới
    private function create() {
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->username) && !empty($data->password)) {
            $this->userModel->username = $data->username;
            $this->userModel->password = $data->password;
            
            // Xử lý các trường có giá trị mặc định
            $this->userModel->role = $data->role ?? 'user'; 
            $this->userModel->permission = $data->permission ?? 'view';
            $this->userModel->is_locked = $data->is_locked ?? 0; // --- THÊM: Mặc định không khóa ---

            if($this->userModel->create()) {
                http_response_code(201);
                echo json_encode(["message" => "User created successfully"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to create user"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Data is incomplete (Missing username or password)"]);
        }
    }

    // 4. Cập nhật User
    private function update($id) {
        $data = json_decode(file_get_contents("php://input"));
        
        // Kiểm tra ID
        if(!$id) {
            http_response_code(400);
            echo json_encode(["message" => "User ID is required"]);
            return;
        }

        $this->userModel->id = $id;

        // --- QUAN TRỌNG: Cần gán đủ dữ liệu vì Model update() update hết các trường ---
        // Nếu client không gửi trường nào đó, ta nên giữ nguyên giá trị cũ hoặc gán rỗng.
        // Ở đây giả sử client gửi đủ, hoặc dùng toán tử ?? để tránh lỗi null.
        
        $this->userModel->username = $data->username; 
        $this->userModel->role = $data->role;
        $this->userModel->permission = $data->permission ?? 'view'; // --- THÊM ---
        $this->userModel->is_locked = $data->is_locked ?? 0;       // --- THÊM ---

        if($this->userModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "User updated successfully"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "User not updated"]);
        }
    }

    // 5. Xóa User
    private function delete($id) {
        $this->userModel->id = $id;
        if($this->userModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "User deleted"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "User not deleted"]);
        }
    }
}
?>