<?php
include_once './config/Database.php';
include_once './models/User.php';

class AuthController
{
    private $model;

    public function __construct($db)
    {
        $this->model = new User($db);
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!$data || empty($data->username) || empty($data->password)) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Vui lòng nhập username và mật khẩu"
            ]);
            return;
        }

        $this->model->username = $data->username;
        $this->model->password = $data->password;
        $result = $this->model->login();

        if ($result === true) {
            // --- ĐĂNG NHẬP THÀNH CÔNG ---

            // Tạo một token đơn giản (Trong thực tế nên dùng JWT)
            // Token này sẽ được React/Angular lưu vào localStorage
            $token = bin2hex(random_bytes(16));

            // 2. --- LƯU VÀO SESSION (Thay vì DB) ---
            // Server sẽ giữ token này trong RAM/File tạm
            $_SESSION['app_token'] = $token;
            $_SESSION['user_id'] = $this->model->id; // Lưu thêm ID để dùng sau này
            // -------------------------------------

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Đăng nhập thành công.",
                "data" => [
                    "user_id" => $this->model->id,
                    "username" => $this->model->username,
                    "password" => $this->model->password,
                    "role" => $this->model->role, // Quan trọng: Trả về role để Frontend phân quyền
                    "is_locked" => $this->model->is_locked,
                ],
                "token" => $token
            ]);
        } elseif ($result === "LOCKED") {
            echo json_encode(["status" => "error", "message" => "Tài khoản bị khóa"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Sai tài khoản hoặc mật khẩu"]);
        }
    }

    public function checkAdmin() {
        if ($this->model->role == "admin") {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "User is admin"]);
            return true;
        } else {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "User is not admin"]);
            return false;
        }
    }
}
