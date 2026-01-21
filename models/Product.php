<?php
class Product
{
    private PDO $conn;
    private string $table = "products"; // Tên bảng số nhiều

    // ===== Thuộc tính DB (Khớp với bảng products mới) =====
    public ?int $id = null;
    public ?string $name = null;
    public ?string $image = null; // Base64 Longtext
    public ?int $price = null;
    public ?string $description = null;
    public ?string $type = null;        // Thay cho category
    public ?string $color = null;
    public ?int $stock = null;
    public ?int $id_brand = null;       // Khóa ngoại (INT)

    // ===== Thuộc tính hiển thị (Không có trong bảng products) =====
    public ?string $brand_name = null;  // Lấy từ bảng brands qua JOIN

    // ===== Filter Attributes =====
    public ?string $priceRange = null;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // ==================================================
    // 1. GET ALL (Có JOIN để lấy tên Brand)
    // ==================================================
    public function getAll(): PDOStatement
    {
        // Select p.* để lấy hết cột product, b.name để lấy tên hãng
        $sql = "SELECT p.*, b.name as brand_name 
                FROM {$this->table} p
                LEFT JOIN brands b ON p.id_brand = b.id
                ORDER BY p.id DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // ==================================================
    // 2. GET BY ID
    // ==================================================
    public function getById(): bool
    {
        $sql = "SELECT p.*, b.name as brand_name 
                FROM {$this->table} p
                LEFT JOIN brands b ON p.id_brand = b.id
                WHERE p.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Map dữ liệu vào object
            $this->id = $row['id'];
            $this->name = $row['name'];
            $this->image = $row['image'];
            $this->price = $row['price'];
            $this->description = $row['description'];
            $this->type = $row['type'];
            $this->color = $row['color'];
            $this->stock = $row['stock'];
            $this->id_brand = $row['id_brand'];
            $this->brand_name = $row['brand_name']; // Gán tên hãng
            return true;
        }
        return false;
    }

    // ==================================================
    // 3. CREATE (Lưu id_brand và type)
    // ==================================================
    public function create(): bool
    {
        $sql = "INSERT INTO {$this->table}
                (name, image, price, description, type, color, id_brand, stock)
                VALUES (:name, :image, :price, :description, :type, :color, :id_brand, :stock)";

        $stmt = $this->conn->prepare($sql);

        // Lưu ý: image không dùng strip_tags để bảo toàn Base64
        return $stmt->execute([
            ':name' => trim($this->name)??'chưa có tên',
            ':image' => $this->image??'', // Giữ nguyên Base64
            ':price' => (int)$this->price??0,
            ':description' => trim($this->description),
            ':type' => trim($this->type), // Lưu vào cột type
            ':color' => trim($this->color)??'',
            ':id_brand' => (int)$this->id_brand, // Lưu id hãng
            ':stock' => (int)$this->stock??0
        ]);
    }

    // ==================================================
    // 4. UPDATE
    // ==================================================
    public function update(): bool
    {
        $sql = "UPDATE {$this->table}
                SET name=:name, image=:image, price=:price, description=:description,
                    type=:type, color=:color,  stock=:stock
                WHERE id=:id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name' => trim($this->name),
            ':image' => $this->image,
            ':price' => (int)$this->price,
            ':description' => trim($this->description),
            ':type' => trim($this->type),
            ':color' => trim($this->color),
            ':stock' => (int)$this->stock,
            ':id' => (int)$this->id
        ]);
    }

    // ==================================================
    // 5. DELETE
    // ==================================================
    public function delete(): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id=:id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => (int)$this->id]);
    }

    // ==================================================
    // 6. PHÂN TRANG CƠ BẢN
    // ==================================================
    public function getProducts(int $offset, int $limit): PDOStatement
    {
        $sql = "SELECT p.*, b.name as brand_name 
                FROM {$this->table} p
                LEFT JOIN brands b ON p.id_brand = b.id
                ORDER BY p.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function countAllProducts(): int
    {
        return (int)$this->conn
            ->query("SELECT COUNT(*) FROM {$this->table}")
            ->fetchColumn();
    }

    // ==================================================
    // 7. FILTER + SEARCH (Nâng cao)
    // ==================================================
    public function filter($offset, $limit)
    {
        $where = [];
        $params = [];

        // 1. Tìm theo tên
        if ($this->name) {
            $where[] = "p.name LIKE :name";
            $params[':name'] = '%' . $this->name . '%';
        }

        // 2. Tìm theo Loại (Type)
        if ($this->type && $this->type !== 'Tất cả') {
            $where[] = "p.type = :type";
            $params[':type'] = $this->type;
        }

        // 3. Tìm theo Hãng (Brand ID)
        if ($this->id_brand && $this->id_brand != 0) { // Giả sử 0 hoặc null là tất cả
            $where[] = "p.id_brand = :id_brand";
            $params[':id_brand'] = $this->id_brand;
        }

        // 4. Tìm theo Giá
        if ($this->priceRange) {
            switch ($this->priceRange) {
                case 'under-10':
                    $where[] = "p.price < 10000000";
                    break;
                case '10-30':
                    $where[] = "p.price BETWEEN 10000000 AND 30000000";
                    break;
                case 'over-30':
                    $where[] = "p.price > 30000000";
                    break;
            }
        }

        // Xây dựng câu SQL
        // Lưu ý: Phải dùng alias 'p' vì có join
        $sql = "SELECT p.*, b.name as brand_name 
                FROM {$this->table} p
                LEFT JOIN brands b ON p.id_brand = b.id";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        // Bind dữ liệu
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt;
    }

    // Đếm tổng số kết quả sau khi lọc (để phân trang)
    public function countFilterProducts()
    {
        $where = [];
        $params = [];

        if ($this->name) {
            $where[] = "name LIKE :name";
            $params[':name'] = '%' . $this->name . '%';
        }

        if ($this->type && $this->type !== 'Tất cả') {
            $where[] = "type = :type";
            $params[':type'] = $this->type;
        }

        if ($this->id_brand && $this->id_brand != 0) {
            $where[] = "id_brand = :id_brand";
            $params[':id_brand'] = $this->id_brand;
        }

        if ($this->priceRange) {
            switch ($this->priceRange) {
                case 'under-10': $where[] = "price < 10000000"; break;
                case '10-30': $where[] = "price BETWEEN 10000000 AND 30000000"; break;
                case 'over-30': $where[] = "price > 30000000"; break;
            }
        }

        $sql = "SELECT COUNT(*) FROM {$this->table}"; // Alias p không bắt buộc ở đây nếu không join
        // Tuy nhiên nếu filter theo tên brand thì cần join, ở đây filter theo id_brand nên không cần join cho nhẹ
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }

        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
}