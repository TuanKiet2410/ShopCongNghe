<?php
class AuthMiddleware {

    public function isAuthenticated() {
        $headers = apache_request_headers();
        $clientToken = null;

        // 1. Lấy token khách gửi lên (từ Header)
        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches);
            if (isset($matches[1])) {
                $clientToken = $matches[1];
            }
        }

        // 2. Kiểm tra có Session không?
        // (Nếu session hết hạn hoặc chưa login, biến này sẽ không tồn tại)
        if (!isset($_SESSION['app_token'])) {
             $this->returnError("Phiên đăng nhập đã hết hạn hoặc chưa đăng nhập.");
        }

        // 3. --- SO SÁNH (QUAN TRỌNG) ---
        // Token khách gửi == Token mình lưu trong Session?
        if ($clientToken !== $_SESSION['app_token']) {
             $this->returnError("Token không hợp lệ (Fake Token).");
        }
        // -------------------------------

        // Khớp 100% -> Cho qua
        return true;
    }

    // hàm check token của admin 
    public function isAuthenticatedAdmin() {
        $headers = apache_request_headers();
        $clientToken = null;

        // 1. Lý token khách gửi lên (từ Header)
        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches);
            if (isset($matches[1])) {
                $clientToken = $matches[1];
            }
        }

        // 2. Kiem tra co Session khong?
        // (Nhung session het han hoac chua login, bien nay se khong ton tai)
        if (!isset($_SESSION['app_token'])) {
             $this->returnError("Phien dang nhap da het han hoac chua dang nhap.");
        }

        // 3. --- SO SANH (QUAN TRONG) ---
        // Token khach gui == Token minh luu trong Session?
        if ($clientToken !== $_SESSION['app_token'] ) {
             $this->returnError("Token sai.");
        }

        if($_SESSION['user_id'] != 11){
            $this->returnError("Token khong phải của admin");
        }
        // -------------------------------

        // Khop 100% -> Cho qua
        return true;
    }

    private function returnError($msg) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => $msg]);
        exit();
    }
}
?>