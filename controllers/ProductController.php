<?php
// Nhúng Model Product
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../controllers/AuthMiddleware.php';
class ProductController
{
    private $productModel;

    // Nhận kết nối DB từ Router truyền sang
    public function __construct($db)
    {
        $this->productModel = new Product($db);
    }

    // Hàm điều hướng chính
    public function processRequest($id)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getProduct($id); // Lấy 1 sản phẩm
                } else {
                    if (isset($_GET['page'])) {
                        $this->XulyPhanTrang();
                        return;
                    }
                    // Lấy tất cả
                    
                }
                break;

            case 'POST':
                $this->createProduct();
                break;

            case 'PUT': // Lưu ý: Một số host không nhận PUT, có thể dùng POST
                $this->updateProduct($id);
                break;

            case 'DELETE':
                $this->deleteProduct($id);
                break;

            default:
                http_response_code(405); // Method Not Allowed
                echo json_encode(["message" => "Phương thức không được hỗ trợ"]);
                break;
        }
    }

    // --- CÁC HÀM XỬ LÝ LOGIC CHI TIẾT ---

    // 1. Lấy danh sách sản phẩm
    private function getAllProducts()
    {
        $stmt = $this->productModel->getAll();
        $num = $stmt->rowCount();

        if ($num > 0) {
            $products_arr = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $product_item = [
                    'id' => $id,
                    'name' => $name,
                    'image' => $image,
                    'price' => (float)$price, // Ép kiểu số thực
                    'stock' => (int)$stock,   // --- THÊM: Stock ---
                    'category' => $category,
                    'brand' => $brand,
                    // 'created_at' => $created_at // Bỏ nếu bảng products không có cột này, hoặc thêm vào SQL nếu có
                ];
                array_push($products_arr, $product_item);
            }
            http_response_code(200);
            echo json_encode($products_arr);
        } else {
            http_response_code(200);
            echo json_encode([]);
        }
    }

    // 2. Lấy 1 sản phẩm theo ID
    private function getProduct($id)
    {
        $this->productModel->id = $id;

        if ($this->productModel->getById()) {
            $product_item = [
                'id' => $this->productModel->id,
                'name' => $this->productModel->name,
                'image' => $this->productModel->image,
                'price' => (float)$this->productModel->price,
                'stock' => (int)$this->productModel->stock, // --- THÊM: Stock ---
                'description' => $this->productModel->description,
                'category' => $this->productModel->category,
                'color' => $this->productModel->color,
                'brand' => $this->productModel->brand
            ];
            http_response_code(200);
            echo json_encode($product_item);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Không tìm thấy sản phẩm"]);
        }
    }

    // 3. Tạo sản phẩm mới
    private function createProduct()
    {
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->name) && !empty($data->price)) {
            // Gán dữ liệu
            $this->productModel->name = $data->name;
            $this->productModel->price = $data->price;

            // Các trường bổ sung
            $this->productModel->image = $data->image ?? null;
            $this->productModel->description = $data->description ?? '';
            $this->productModel->category = $data->category ?? '';
            $this->productModel->color = $data->color ?? '';
            $this->productModel->brand = $data->brand ?? '';

            // --- THÊM: Stock (Mặc định là 0 nếu không nhập) ---
            $this->productModel->stock = $data->stock ?? 0;

            if ($this->productModel->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Tạo sản phẩm thành công"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Lỗi hệ thống"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu tên hoặc giá sản phẩm"]);
        }
    }

    // 4. Cập nhật sản phẩm
    private function updateProduct($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu ID sản phẩm"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));

        $this->productModel->id = $id;

        // Gán dữ liệu mới
        $this->productModel->name = $data->name;
        $this->productModel->price = $data->price;
        $this->productModel->image = $data->image ?? null;
        $this->productModel->description = $data->description ?? '';
        $this->productModel->category = $data->category ?? '';
        $this->productModel->color = $data->color ?? '';
        $this->productModel->brand = $data->brand ?? '';

        // --- THÊM: Update Stock ---
        $this->productModel->stock = $data->stock ?? 0;

        if ($this->productModel->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Cập nhật thành công"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Cập nhật thất bại"]);
        }
    }

    // 5. Xóa sản phẩm
    private function deleteProduct($id)
    {
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "Thiếu ID sản phẩm"]);
            return;
        }

        $this->productModel->id = $id;

        if ($this->productModel->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Xóa thành công"]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Xóa thất bại"]);
        }
    }



    //xử lý phân trang
private function XulyPhanTrang()
{

    // --- BƯỚC BẢO VỆ ---
    $auth = new AuthMiddleware();
    $auth->isAuthenticated(); // Nếu không có token, nó sẽ tự ngắt và báo lỗi

//--------------------------


    // 1. Xác định trang hiện tại
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page < 1) $page = 1;

    // 2. Cấu hình số lượng sản phẩm trên 1 trang
    $limit = 6; 

    // 3. Tính vị trí bắt đầu lấy trong Database (OFFSET)
    $offset = ($page - 1) * $limit;

    // 4. Kiểm tra xem có đang tìm kiếm không?
    $searchKeyword = isset($_GET['search']) ? $_GET['search'] : null;

    if ($searchKeyword) {
        // --- TRƯỜNG HỢP CÓ TÌM KIẾM ---
        
        // Gọi hàm search NHƯNG phải truyền thêm offset và limit để phân trang kết quả tìm kiếm
        $stmt = $this->productModel->search($searchKeyword, $offset, $limit);
        
        // Đếm tổng số sản phẩm TÌM THẤY (để chia trang chính xác)
        $totalItems = $this->productModel->countSearchProducts($searchKeyword);
    } else {
        // --- TRƯỜNG HỢP KHÔNG TÌM KIẾM (Lấy tất cả) ---
        
        $stmt = $this->productModel->getProducts($offset, $limit);
        
        // Đếm tổng tất cả sản phẩm
        $totalItems = $this->productModel->countAllProducts();
    }

    // 5. Xử lý dữ liệu trả về
    $num = $stmt->rowCount();
    $products_arr = [];

    if ($num > 0) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $product_item = array(
                "id" => $id,
                "name" => $name,
                "price" => $price,
                "image" => $image,
                "description" => $description,
                "category" => $category,
                "color" => $color,
                "brand" => $brand,
                "stock" => $stock 
            );
            array_push($products_arr, $product_item);
        }
    }

    // 6. Tính tổng số trang dựa trên $totalItems (đã xử lý logic ở trên)
    // Nếu không có sản phẩm nào thì totalPages = 0
    $totalPages = ($totalItems > 0) ? ceil($totalItems / $limit) : 0;

    // --- PHẦN 3: TRẢ VỀ KẾT QUẢ (API Response) ---
    echo json_encode([
        "status" => "success",
        "pagination" => [
            "current_page" => $page,
            "limit" => $limit,
            "total_pages" => $totalPages,
            "total_items" => $totalItems
        ],
        "data" => $products_arr
    ]);
}

    
}      

