<?php
class Product {
    private $conn;
    private $table = "products"; // Đã sửa tên bảng cho đúng với SQL

    // Các thuộc tính khớp với database
    public $id;
    public $name;
    public $image;
    public $price;
    public $description;
    public $category;
    public $color;
    public $brand;
    public $stock; // Thêm trường stock (Kho)

    public function __construct($db) {
        $this->conn = $db;
    }

    // 1. GET ALL: Lấy tất cả sản phẩm
    public function getAll() {
        // Sắp xếp theo ID giảm dần (Sản phẩm mới nhất lên đầu)
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 2. GET BY ID: Lấy 1 sản phẩm theo ID
    public function getById() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 0,1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->image = $row['image'];
            $this->price = $row['price'];
            $this->description = $row['description'];
            $this->category = $row['category'];
            $this->color = $row['color'];
            $this->brand = $row['brand'];
            $this->stock = $row['stock']; // Gán giá trị stock
            return true;
        }
        return false;
    }

    // 3. CREATE: Thêm sản phẩm mới
    public function create() {
        // Thêm stock vào câu lệnh INSERT
        $query = "INSERT INTO " . $this->table . " 
                  SET name=:name, image=:image, price=:price, description=:description, 
                      category=:category, color=:color, brand=:brand, stock=:stock";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        $this->stock = htmlspecialchars(strip_tags($this->stock));

        // Gán dữ liệu
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':brand', $this->brand);
        $stmt->bindParam(':stock', $this->stock);

        if($stmt->execute()) {
            return true;
        }
        printf("Lỗi: %s.\n", $stmt->errorInfo()[2]); // Sửa cách hiển thị lỗi PDO
        return false;
    }

    // 4. UPDATE: Cập nhật sản phẩm
    public function update() {
        // Thêm stock vào câu lệnh UPDATE
        $query = "UPDATE " . $this->table . " 
                  SET name=:name, image=:image, price=:price, description=:description, 
                      category=:category, color=:color, brand=:brand, stock=:stock
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        // Làm sạch dữ liệu
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->image = htmlspecialchars(strip_tags($this->image));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->color = htmlspecialchars(strip_tags($this->color));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Gán dữ liệu
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':category', $this->category);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':brand', $this->brand);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        printf("Lỗi: %s.\n", $stmt->errorInfo()[2]);
        return false;
    }

    // 5. DELETE: Xóa sản phẩm
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        printf("Lỗi: %s.\n", $stmt->errorInfo()[2]);
        return false;
    }


    /** lấy pro duc thông qua vị trí bắt đầu và kết thúc */
    public function getProducts($start, $end) {
        $query = "SELECT * FROM " . $this->table . " ORDER BY id DESC LIMIT $start, $end";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    public function countAllProducts() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->rowCount();
    }
    
public function search($keyword, $offset, $limit) {
    // Lưu ý: Thêm LIMIT và OFFSET vào câu query
    $query = "SELECT * FROM products 
              WHERE name LIKE :keyword 
              LIMIT :limit OFFSET :offset"; 

    $stmt = $this->conn->prepare($query);

    // Bind dữ liệu
    $keyword = "%{$keyword}%";
    $stmt->bindParam(':keyword', $keyword);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt;
}

public function countSearchProducts($keyword) {
    $query = "SELECT COUNT(*) as total FROM products WHERE name LIKE :keyword";
    
    $stmt = $this->conn->prepare($query);
    
    $keyword = "%{$keyword}%";
    $stmt->bindParam(':keyword', $keyword);
    
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $row['total'];
}

}

?>