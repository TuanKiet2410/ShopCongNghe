<?php
class User {
    private $conn;
    private $table = "users"; // Tên bảng số nhiều

    // 1. CÁC THUỘC TÍNH (Khớp hoàn toàn với Database mới)
    public $id;
    public $username;
    public $password;
    public $fullname;   // Mới
    public $email;      // Mới
    public $phone;      // Mới
    public $address;    // Mới
    public $image;      // Mới (Lưu Base64)
    public $role;
    public $permission;
    public $is_locked;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ---------------------------------------------------------
    // 2. GET ALL: Lấy danh sách user (kèm thông tin cá nhân)
    // ---------------------------------------------------------
    public function getAll() {
        // Chọn đầy đủ cột (trừ password)
        $query = "SELECT id, username, fullname, email, phone, address, image, role, permission, is_locked, created_at 
                  FROM " . $this->table . " ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // ---------------------------------------------------------
    // 3. GET BY ID: Lấy chi tiết 1 user
    // ---------------------------------------------------------
    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->username = $row['username'];
            // Không gán password ngược lại model để bảo mật
            $this->fullname = $row['fullname'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->image = $row['image'];
            $this->role = $row['role'];
            $this->permission = $row['permission'];
            $this->is_locked = $row['is_locked'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    // ---------------------------------------------------------
    // 4. CREATE (Đăng ký thành viên)
    // ---------------------------------------------------------
    public function create() {
        // Thêm các trường mới vào câu lệnh INSERT
        $query = "INSERT INTO " . $this->table . " 
                  SET 
                    username=:username, 
                    password=:password, 
                    fullname=:fullname, 
                    email=:email, 
                    phone=:phone, 
                    address=:address, 
                    image=:image, 
                    role=:role, 
                    permission=:permission, 
                    is_locked=:is_locked";
        
        $stmt = $this->conn->prepare($query);

        // 1. Làm sạch dữ liệu (Sanitize)
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->permission = htmlspecialchars(strip_tags($this->permission));
        
        // LƯU Ý: KHÔNG dùng strip_tags cho image (Base64)
        
        // Xử lý mặc định
        if(empty($this->is_locked)) {
            $this->is_locked = 0;
        }
        if(empty($this->role)) {
            $this->role = 'user'; // Mặc định là user thường
        }

        // 2. Mã hóa mật khẩu
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        // 3. Gán dữ liệu (Bind)
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':image', $this->image); // Bind ảnh trực tiếp
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':permission', $this->permission);
        $stmt->bindParam(':is_locked', $this->is_locked);

        if($stmt->execute()) {
            return true;
        }
        // Ghi log lỗi thay vì in ra màn hình
        error_log("User Create Error: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // ---------------------------------------------------------
    // 5. UPDATE: Cập nhật thông tin (Bao gồm cả thông tin cá nhân)
    // ---------------------------------------------------------
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET 
                    fullname=:fullname, 
                    email=:email, 
                    phone=:phone, 
                    address=:address, 
                    image=:image, 
                    role=:role, 
                    is_locked=:is_locked 
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitize
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        // Không clean image
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->is_locked = htmlspecialchars(strip_tags($this->is_locked));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':is_locked', $this->is_locked);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) return true;
        
        error_log("User Update Error: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // ---------------------------------------------------------
    // 6. DELETE
    // ---------------------------------------------------------
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) return true;
        return false;
    }

    // ---------------------------------------------------------
    // 7. LOGIN (Đăng nhập)
    // ---------------------------------------------------------
    public function login() {
        // Lấy thêm fullname và image để hiển thị sau khi đăng nhập
        $query = "SELECT id, username, password, role, is_locked, fullname, image 
                  FROM " . $this->table . " 
                  WHERE username = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
         
            $this->is_locked = $row['is_locked'];
            $this->role = $row['role'];

            // 1. Kiểm tra khóa
            if ($row['is_locked'] == 1) {
                return "LOCKED";
            }
       
            // 2. Kiểm tra mật khẩu
            if(password_verify($this->password, $row['password'])) {
                // Đăng nhập thành công -> Gán dữ liệu để trả về cho Frontend
                $this->id = $row['id'];
                $this->fullname = $row['fullname']; // Trả về tên thật
                $this->image = $row['image'];       // Trả về avatar
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    // ---------------------------------------------------------
    // CÁC HÀM PHỤ TRỢ (Giữ nguyên logic cũ)
    // ---------------------------------------------------------
    public function updatePermission() {
        $query = "UPDATE " . $this->table . " SET permission=:permission WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        
        $this->permission = htmlspecialchars(strip_tags($this->permission));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(':permission', $this->permission);
        $stmt->bindParam(':id', $this->id);
        
        if($stmt->execute()) return true;
        return false;
    }

    public function checkCustomer($id){
        // Kiểm tra xem ID có phải là user thường không
        $query = "SELECT id FROM " . $this->table . " WHERE id = ? AND role = 'user' LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        if($stmt->rowCount() > 0) return true;
        return false;
    }

    public function checkEmployer($id){ // Có thể đổi tên thành checkAdmin cho đúng ngữ cảnh
        // Kiểm tra xem ID có phải là admin không
        $query = "SELECT id FROM " . $this->table . " WHERE id = ? AND role = 'admin' LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        if($stmt->rowCount() > 0) return true;
        return false;
    }
}
?>