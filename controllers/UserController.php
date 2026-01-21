<?php
require_once __DIR__ . './../models/User.php';
require_once __DIR__ . '/../controllers/AuthMiddleware.php';
class UserController {
    private $userModel;
    private $authMiddleware;
    public function __construct($db) {
        $this->userModel = new User($db);
        $this->authMiddleware = new AuthMiddleware($db);
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
                if(isset($_GET['permission'])) { // --- THÊM ---
                    $this->updatePermission($id);
                    break;
                }
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
    // lớp bảo vệ check admin

    $stmt = $this->userModel->getAll();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 🔥 CHUYỂN permission TEXT → ARRAY
    foreach ($users as &$user) {
        if (!empty($user['permission'])) {
            $user['permission'] = array_values(
                array_filter(
                    array_map('trim', explode(',', $user['permission']))
                )
            );
        } else {
            $user['permission'] = [];
        }
    }

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
        $this->userModel->fullname = $data->fullname;
        $this->userModel->email = $data->email;
        $this->userModel->address = $data->address;
        $this->userModel->phone = $data->phone; 
        $this->userModel->role = $data->role;
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

    //update permisson
  // update permisson
    private function updatePermission($id) {
        // 1. Lấy dữ liệu
        $input = json_decode(file_get_contents("php://input"), true);
        
        // 2. Kiểm tra dữ liệu đầu vào
        if (!isset($input['permission'])) {
            http_response_code(400);
            echo json_encode(["message" => "Missing permission data"]);
            return;
        }

        // 3. Xử lý mảng thành chuỗi (ví dụ: ['A', 'B'] -> "A,B")
        $permissions = $input['permission'];
        
        // Kiểm tra nếu permissions là mảng thì mới implode, nếu là string thì giữ nguyên (đề phòng)
        if (is_array($permissions)) {
            $permissionString = implode(',', $permissions);
        } else {
            $permissionString = $permissions;
        }

        // 4. Gán vào Model
        $this->userModel->id = $id;
        $this->userModel->permission = $permissionString;

        // 5. Gọi hàm update và trả về JSON (TUYỆT ĐỐI KHÔNG var_dump/echo gì khác)
        if($this->userModel->updatePermission()) {
            http_response_code(200);
            echo json_encode(["message" => "User permission updated"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "User permission not updated"]);
        }
    }
}
?>