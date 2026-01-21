<?php
class Customer {
    private $conn;
    private $table = "users"; // DÙNG CHUNG BẢNG USERS

    // Các thuộc tính khớp với bảng users (giống Employee)
    public $id;
    public $fullname;   // Đổi từ $name sang $fullname cho khớp DB
    public $phone;
    public $address;
    public $image;
    public $username;   // Khách hàng cần username để đăng nhập
    public $password;   // Khách hàng cần password để đăng nhập
    public $role = 'customer'; // Mặc định role là customer
    public $email;
    // public $is_locked; // Khách hàng có thể không cần thao tác field này ngay

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. GET ALL: Chỉ lấy user có role là 'customer'
    public function getAll() {
        // Lọc chính xác những người là khách hàng
        $query = "SELECT id, fullname, email, phone, address, image, role, username 
                  FROM " . $this->table . " 
                  WHERE role = :role 
                  ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind role cứng vào để đảm bảo không lọt admin ra ngoài
        $role_filter = 'customer'; 
        $stmt->bindParam(':role', $role_filter);
        
        $stmt->execute();
        return $stmt;
    }

    // 2. GET BY ID: Lấy chi tiết, đảm bảo đó là customer
    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? AND role = 'customer' LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->fullname = $row['fullname'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->address = $row['address'];
            $this->image = $row['image'];
            $this->username = $row['username'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    // 3. CREATE: Tạo khách hàng (Đăng ký thành viên)
    public function create() {
        // Cấu trúc query giống hệt Employee, chỉ khác giá trị truyền vào
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
        $this->image = htmlspecialchars(strip_tags($this->image));

        // Mặc định role là customer
        $this->role = 'customer'; 

        // Mã hóa mật khẩu (Quan trọng: Customer cũng cần login)
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);

        // Bind data
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':email', $this->email);

        if($stmt->execute()) {
            return true;
        }
        
        // Log lỗi
        error_log("Customer Create Error: " . implode(" ", $stmt->errorInfo()));
        return false;
    }

    // 4. UPDATE: Cập nhật thông tin khách hàng
    public function update() {
        // Không cho phép update password, username, role ở hàm này (thường làm hàm riêng đổi pass)
        $query = "UPDATE " . $this->table . " 
                  SET fullname=:fullname, phone=:phone, address=:address, 
                      image=:image, email=:email
                  WHERE id=:id AND role='customer'";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->fullname = htmlspecialchars(strip_tags($this->fullname));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(':fullname', $this->fullname);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) return true;
        return false;
    }

    // 5. DELETE
    public function delete() {
        // Chỉ xóa nếu là customer
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND role='customer'";
        
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) return true;
        return false;
    }
}
?>