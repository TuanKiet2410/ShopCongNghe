<?php
class User {
    private $conn;
    private $table = "users"; // SỬA: Tên bảng trong SQL là số nhiều

    // Các thuộc tính khớp với DB
    public $id;
    public $username;
    public $password;
    public $role;
    public $permission;
    public $is_locked; // THÊM: Cột trạng thái khóa
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. GET ALL: Lấy danh sách user
    public function getAll() {
        // Không select password để bảo mật
        $query = "SELECT id, username, role, permission, is_locked, created_at FROM " . $this->table;
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. GET BY ID: Lấy 1 user
    public function getById() {
        $query = "SELECT id, username, role, permission, is_locked, created_at FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->username = $row['username'];
            $this->role = $row['role'];
            $this->permission = $row['permission'];
            $this->is_locked = $row['is_locked']; // Gán giá trị khóa
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // 3. CREATE (Đăng ký/Thêm mới)
    public function create() {
        // Thêm is_locked vào câu lệnh INSERT
        $query = "INSERT INTO " . $this->table . " 
                  SET username=:username, password=:password, role=:role, 
                      permission=:permission, is_locked=:is_locked";
        
        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->permission = htmlspecialchars(strip_tags($this->permission)); // Xử lý permission
        
        // Mặc định là 0 (không khóa) nếu không có dữ liệu
        if(empty($this->is_locked)) {
            $this->is_locked = 0;
        }

        // Mã hóa password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':permission', $this->permission);
        $stmt->bindParam(':is_locked', $this->is_locked);

        if($stmt->execute()) {
            return true;
        }
        // In lỗi nếu có
        printf("Error: %s.\n", $stmt->errorInfo()[2]);
        return false;
    }

    // 4. UPDATE: Cập nhật thông tin (Không update password ở đây để an toàn)
    public function update() {
        // Bổ sung update permission và is_locked
        $query = "UPDATE " . $this->table . " 
                  SET username=:username, role=:role, permission=:permission, is_locked=:is_locked 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Clean data
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->permission = htmlspecialchars(strip_tags($this->permission));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->is_locked = htmlspecialchars(strip_tags($this->is_locked));

        // Bind data
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':permission', $this->permission);
        $stmt->bindParam(':is_locked', $this->is_locked);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) return true;
        
        printf("Error: %s.\n", $stmt->errorInfo()[2]);
        return false;
    }

    // 5. DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) return true;
        
        printf("Error: %s.\n", $stmt->errorInfo()[2]);
        return false;
    }

    // 6. LOGIN (Kiểm tra đăng nhập - Hàm bổ sung cần thiết)
    public function login() {
        // Tìm user theo username
        $query = "SELECT id, username, password, role, is_locked FROM " . $this->table . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // 1. Kiểm tra tài khoản có bị khóa không
            if ($row['is_locked'] == 1) {
                return "LOCKED";
            }

            // 2. Kiểm tra mật khẩu (So sánh pass nhập vào với pass mã hóa trong DB)
            if(password_verify($this->password, $row['password'])) {
                // Đăng nhập thành công, gán lại các giá trị để sử dụng
                $this->id = $row['id'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }
}
?>