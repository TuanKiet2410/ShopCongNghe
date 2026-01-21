<?php
class Employee {
    private $conn;
    private $table = "users"; // TRỎ VÀO BẢNG USERS

    // Các thuộc tính khớp với bảng users
    public $id;
    public $fullname;   // Map với name từ input
    public $phone;
    public $address;
    public $image;
    public $username;   // Bắt buộc của bảng users
    public $password;   // Bắt buộc của bảng users
    public $role;       // Sẽ set là 'employee' hoặc 'admin'
    public $email;
    public $is_locked;

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. GET ALL: Chỉ lấy user có role KHÁC 'user' (tức là lấy admin/employee)
    public function getAll() {
        // Giả sử 'user' là khách hàng, các role khác là nhân viên
        $query = "SELECT * FROM " . $this->table . " WHERE role != 'user' ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. GET BY ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->fullname = $row['fullname'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->image = $row['image'];
            $this->role = $row['role'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            return true;
        }
        return false;
    }

    // 3. CREATE: Thêm nhân viên vào bảng User
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET fullname=:fullname, phone=:phone, address=:address, 
                      image=:image, username=:username, password=:password, 
                      role=:role, email=:email, is_locked=0";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->role = htmlspecialchars(strip_tags($this->role));

        // Mã hóa mật khẩu
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':email', $this->email);

        if($stmt->execute()) return true;
        
        // Log lỗi nếu trùng username/email
        error_log("Employee Create Error: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // 4. UPDATE
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET fullname=:fullname, phone=:phone, address=:address, 
                      image=:image, role=:role, email=:email
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) return true;
        return false;
    }

    // 5. DELETE
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        if($stmt->execute()) return true;
        return false;
    }
}
?>